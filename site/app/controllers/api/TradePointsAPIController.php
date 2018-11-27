<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

/**
 * Class TradePointsAPIController
 * Контроллер для работы с точками оказания услуг.
 * Реализует CRUD для точек оказания услуг.
 */
class TradePointsAPIController extends Controller
{
    /**
     * Возвращает точки предоставления услуг для пользователя или для указанной компании пользоваателя.
     *
     * @method GET
     * @param integer $companyId
     * @return string - json array of [status, [TradePoint, phones]], если все успешно,
     * или json array в формате Status в ином случае
     */
    public function getPointsAction($companyId = null)
    {
        if ($this->request->isGet() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            if ($companyId != null) {
                if (!Companies::checkUserHavePermission($userId, $companyId, 'getPoints')) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }

                $subjectId = $companyId;
                $subjectType = 1;
            } else {
                $subjectId = $userId;
                $subjectType = 0;
            }

            $tradePoints = TradePoints::findBySubject($subjectId, $subjectType);


            $pointsWithPhones = [];

            if ($companyId != null) {
                $company = Companies::findFirstByCompanyid($companyId);
                foreach ($tradePoints as $tradePoint) {
                    if ($tradePoint->getWebSite() == null || trim($tradePoint->getWebSite()) == "") {
                        $tradePoint->setWebSite($company->getWebSite());
                    }

                    if ($tradePoint->getEmail() == null || trim($tradePoint->getEmail()) == "") {
                        $tradePoint->setEmail($company->getEmail());
                    }

                    $phones = PhonesPoints::findByPointid($tradePoint->getPointId());
                    if ($phones->count() == 0) {
                        $phones = PhonesCompanies::findByCompanyid($company->getCompanyId());
                    }
                    $phones2 = [];
                    foreach ($phones as $phone) {
                        $phones2[] = ['phoneId' => $phone->getPhoneId(), 'phone' => $phone->phones->getPhone()];
                    }

                    $pointsWithPhones[] = ['tradePoint' => $tradePoint, 'phones' => $phones2];
                }
            } else {
                $user = Users::findFirstByUserid($userId);
                foreach ($tradePoints as $tradePoint) {
                    if (($tradePoint->getEmail() == null || trim($tradePoint->getEmail()) == "")
                        && $user->getEmail() != null) {
                        $tradePoint->setEmail($user->getEmail());
                    }

                    $phones = PhonesPoints::findByPointid($tradePoint->getPointId());
                    if ($phones->count() == 0 && $user->getPhoneId() != null) {
                        $phone = $user->phones;
                        $phones = [];
                        $phones[] = ['phoneId' => $phone->getPhoneId(), 'phone' => $phone->getPhone()];
                    }


                    $pointsWithPhones[] = ['tradePoint' => $tradePoint, 'phones' => $phones];
                }
            }

            $response->setJsonContent([
                'status' => STATUS_OK,
                'points' => $pointsWithPhones,
            ]);
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Возвращает точки предоставления услуг назначенные текущему пользователю
     *
     * @method GET
     * @param  int $userIdManager
     * @return string - json array of [TradePoint, phones]
     */
    public function getPointsForUserManagerAction($userIdManager = null)
    {
        if ($this->request->isGet() && $this->session->get('auth')) {

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            if ($userIdManager == null || $userIdManager == $userId) {
                $tradePoints = TradePoints::findByUsermanager($userId);
            } else {
                $trades = TradePoints::findByUsermanager($userIdManager);
                $tradePoints = [];
                foreach ($trades as $point) {
                    if ($point->companies->getUserId() == $userId) {
                        $tradePoints[] = $point;
                    }
                }
            }

            $pointsWithPhones = [];

            foreach ($tradePoints as $tradePoint) {

                $company = $tradePoint->companies;
                if ($tradePoint->getWebSite() == null || trim($tradePoint->getWebSite()) == "") {
                    $tradePoint->setWebSite($company->getWebSite());
                }

                if ($tradePoint->getEmail() == null || trim($tradePoint->getEmail()) == "") {
                    $tradePoint->setEmail($company->getEmail());
                }

                $phones = PhonesPoints::findByPointid($tradePoint->getPointId());
                if ($phones->count() == 0) {
                    $phones = PhonesCompanies::findByCompanyid($company->getCompanyId());
                }
                $phones2 = [];
                foreach ($phones as $phone) {
                    $phones2[] = ['phoneId' => $phone->getPhoneId(), 'phone' => $phone->phones->getPhone()];
                }

                $pointsWithPhones[] = ['tradePoint' => $tradePoint, 'phones' => $phones2];
            }

            return json_encode($pointsWithPhones);

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Добавляет точку оказания услуг к компании
     *
     * @method POST
     *
     * @params (Обязательные)   string name, double latitude, double longitude,
     * @params (Необязательные) string email, string webSite, string address, string fax,
     * @params (Необязательные) (int userManagerId, int companyId) - парой
     * @return Phalcon\Http\Response с json массивом в формате Status
     */
    public function addTradePointAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();
            return $this->TradePointsAPI->addTradePoint($_POST);
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Вспомогательная функция для добавления точки оказания услуг. Нужна для предоставления
     * функции добавления точек оказания услуг из других контроллеров.
     * Принимает ассоциативный массив params со следующими параметрами:
     * (Обязательные)   string name, double latitude, double longitude,
     * (Необязательные) string email, string webSite, string address, string fax,
     * (Необязательные) (int userManagerId, int companyId) - парой.
     * Если userId равен null, то берет id и сессии
     * Недоступна при непосредственных запросах.
     * @param $params
     *
     * @return string - json array в формате Status. Если успешно, то еще и id созданной точки.
     */
    public function addTradePoint($params)
    {
        $auth = $this->session->get('auth');
        $userId = $auth['id'];
        $response = new Response();

        $point = new TradePoints();

        if ($params["companyId"]) {
            $company = Companies::findFirstByCompanyid($params["companyId"]);

            if (!Companies::checkUserHavePermission($userId, $company->getCompanyId(), 'addPoint')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $point->setSubjectId($params["companyId"]);
            $point->setSubjectType(1);
            if ($params["userManagerId"])
                $point->setUserManager($params["userManagerId"]);
            else {
                $point->setUserManager($userId);
            }
        } else {
            $point->setSubjectId($userId);
            $point->setSubjectType(0);
            $point->setUserManager($userId);
        }

        $point->setName($params["name"]);
        $point->setEmail($params["email"]);
        $point->setWebSite($params["webSite"]);
        $point->setLatitude($params["latitude"]);
        $point->setLongitude($params["longitude"]);
        $point->setAddress($params["address"]);
        $point->setFax($params["fax"]);
        $point->setTime($params["time"]);
        $point->setPositionVariable($params["positionvariable"]);
        $point->setUserManager($params["userId"]);

        if (!$point->save()) {
            $errors = [];
            foreach ($point->getMessages() as $message) {
                $errors[] = $message->getMessage();
            }
            $response->setJsonContent(
                [
                    "status" => STATUS_WRONG,
                    "errors" => $errors
                ]
            );
            return $response;
        }

        $response->setJsonContent(
            [
                "pointId" => $point->getPointId(),
                "status" => STATUS_OK
            ]
        );

        return $response;
    }

    /**
     * Редактирует указанную точку оказания услуг
     *
     * @method PUT
     *
     * @param (Обязательные)   int pointId string name, double latitude, double longitude,
     *        (Необязательные) string email, string webSite, string address, string fax, string time, int userId, int subjectId, int subjectType,
     *        Точно будут изменены - name, latitude, longitude, email, webSite, address, fax, time
     * @return Phalcon\Http\Response с json массивом в формате Status
     */
    public function editTradePointAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $point = TradePoints::findFirstByPointid($this->request->getPut("pointId"));

            if (!$point ||
                !SubjectsWithNotDeleted::checkUserHavePermission($userId, $point->getSubjectId(),
                    $point->getSubjectType(), 'editPoint')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if ($this->request->getPut("subjectId") && $this->request->getPut("subjectType")) {
                //Меняем владельца. Не знаю, зачем это может понадобиться
                $point->setSubjectId($this->request->getPut("subjectId"));
                $point->setSubjectType($this->request->getPut("subjectType"));
            }

            $point->setName($this->request->getPut("name"));
            $point->setEmail($this->request->getPut("email"));
            $point->setWebSite($this->request->getPut("webSite"));
            $point->setLatitude($this->request->getPut("latitude"));
            $point->setLongitude($this->request->getPut("longitude"));
            $point->setAddress($this->request->getPut("address"));
            $point->setPositionVariable($this->request->getPut("positionvariable"));
            $point->setFax($this->request->getPut("fax"));
            $point->setTime($this->request->getPut("time"));

            if ($this->request->getPut("userId") && $point->getSubjectType() != 0)
                $point->setUserManager($this->request->getPut("userId"));

            if (!$point->update()) {

                foreach ($point->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }


    /**
     * Удаляет указанную точку оказания услуг
     *
     * @method DELETE
     *
     * @param (Обязательные) $pointId
     * @return Phalcon\Http\Response с json массивом в формате Status
     */
    public function deleteTradePointAction($pointId)
    {
        if ($this->request->isDelete()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $point = TradePoints::findFirstByPointid($pointId);

            if (!$point) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая точка оказания услуг не существует']
                    ]
                );
                return $response;
            }

            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $point->getSubjectId(), $point->getSubjectType(), 'deletePoint')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$point->delete()) {
                $errors = [];
                foreach ($point->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Возвращает публичную информацию об указанной точке оказания услуг.
     * Публичный доступ.
     *
     * @method GET
     *
     * @param $pointId
     * @return string - json array {status,point,[services],[phones]}
     */
    public function getPointInfoAction($pointId){
        if ($this->request->isGet()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $point = TradePoints::findFirstByPointid($pointId);

            if(!$point){
                $response->setJsonContent([
                    'status' => STATUS_WRONG,
                    'errors' => ['Точка оказания услуг не существует'],
                ]);
                return $response;
            }

            $point2 = $point->clipToPublic();

            $services = TradePoints::getServicesForPoint($point->getPointId());
            $services2 = [];
            foreach($services as $service){
                $services2[] = $service->clipToPublic();
            }

            $phones = PhonesPoints::getPhonesForPoint($point->getPointId());

            $response->setJsonContent([
                'status' => STATUS_OK,
                'point' => $point2,
                'services' => $services2,
                'phones' => $phones,
            ]);
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}
