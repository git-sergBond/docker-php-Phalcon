<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

/**
 * Class PhonesAPIController
 * Контроллер для работы с номерами телефонов.
 * Содержит методы для добавления, изменения и удаления номеров телефонов
 * для пользователей, компаний и точек оказания услуг.
 */
class PhonesAPIController extends Controller
{
    /**
     * Добавляет телефон для указанной компании
     * @method POST
     * @params integer companyId, string phone или integer phoneId
     * @return Phalcon\Http\Response с json ответом в формате Status;
     */
    public function addPhoneToCompanyAction()
    {
        if ($this->request->isPost()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $company = Companies::findFirstByCompanyid($this->request->getPost("companyId"));

            if ($company && ($company->getUserId() == $userId || $auth['role'] == ROLE_MODERATOR)) {
                $this->db->begin();
                if ($this->request->getPost("phone")) {

                    //Создаем новый
                    $phone = new Phones();
                    $phone->setPhone($this->request->getPost("phone"));

                    if (!$phone->save()) {
                        $this->rollback();
                        $errors = [];
                        foreach ($phone->getMessages() as $message) {
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
                } else if ($this->request->getPost("phoneId")) {
                    $phone = Phones::findFirstByPhoneid($this->request->getPost("phoneId"));

                    if (!$phone) {
                        $response->setJsonContent(
                            [
                                "status" => STATUS_WRONG,
                                "errors" => ['телефона с таким id не существует']
                            ]
                        );
                        return $response;
                    }


                } else {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['Нужно указать номер телефона или id существующего в параметрах \'phone\', \'phoneId\'']
                        ]
                    );
                    return $response;
                }

                $phoneCompany = PhonesCompanies::findByIds($company->getCompanyId(), $phone->getPhoneId());
                if ($phoneCompany) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_ALREADY_EXISTS,
                            "errors" => ['Телефон уже привязан к компании']
                        ]
                    );
                    return $response;
                }
                $phoneCompany = new PhonesCompanies();

                $phoneCompany->setCompanyId($company->getCompanyId());
                $phoneCompany->setPhoneId($phone->getPhoneId());

                if (!$phoneCompany->save()) {

                    $this->db->rollback();
                    $errors = [];
                    foreach ($phoneCompany->getMessages() as $message) {
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

                $this->db->commit();

                $response->setJsonContent(
                    [
                        'status' => STATUS_OK
                    ]
                );
                return $response;

            } else {
                $response->setJsonContent(
                    [
                        'status' => STATUS_WRONG,
                        'errors' => ['permission error']
                    ]
                );
                return $response;
            }
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Добавляет телефон для указанной точки оказания услуг
     * @method POST
     * @params integer pointId, string phone или integer phoneId
     * @return Phalcon\Http\Response с json ответом в формате Status;
     */
    public function addPhoneToTradePointAction()
    {
        if ($this->request->isPost()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $point = TradePoints::findFirstByPointid($this->request->getPost("pointId"));

            if (!$point || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $point->getSubjectId(), $point->getSubjectType(), 'editPoint')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $this->db->begin();

            if ($this->request->getPost("phone")) {

                //Создаем новый
                $phone = new Phones();
                $phone->setPhone($this->request->getPost("phone"));

                if (!$phone->save()) {

                    $this->db->rollback();
                    $errors = [];
                    foreach ($phone->getMessages() as $message) {
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

            } else if ($this->request->getPost("phoneId")) {
                $phone = Phones::findFirstByPhoneid($this->request->getPost("phoneId"));

                if (!$phone) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['телефона с таким id не существует']
                        ]
                    );
                    return $response;
                }

            } else {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нужно указать номер телефона или id существующего в параметрах \'phone\', \'phoneId\'']
                    ]
                );
                return $response;
            }

            $phonePoint = PhonesPoints::findByIds($point->getPointId(), $phone->getPhoneId());
            if ($phonePoint) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_ALREADY_EXISTS,
                        "errors" => ['Телефон уже привязан к точке оказания услуг']
                    ]
                );
                return $response;
            }

            $phonePoint = new PhonesPoints();

            $phonePoint->setPointId($point->getPointId());
            $phonePoint->setPhoneId($phone->getPhoneId());

            if (!$phonePoint->save()) {

                $this->db->rollback();
                $errors = [];
                foreach ($phonePoint->getMessages() as $message) {
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

            $this->db->commit();

            $response->setJsonContent(
                [
                    'status' => STATUS_OK
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Убирает телефон из списка телефонов компании
     *
     * @method DELETE
     *
     * @param int $phoneId
     * @param int $companyId
     * @return Phalcon\Http\Response с json массивом в формате Status
     */
    public function deletePhoneFromCompanyAction($phoneId, $companyId)
    {
        if ($this->request->isDelete()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $phonesCompany = PhonesCompanies::findByIds($companyId, $phoneId);

            if (!$phonesCompany) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Телефон не существует']
                    ]
                );
                return $response;
            }

            $company = $phonesCompany->companies;

            if (!$company || !Companies::checkUserHavePermission($userId, $companyId, 'editCompany')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$phonesCompany->delete()) {
                $errors = [];
                foreach ($phonesCompany->getMessages() as $message) {
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

            $phone = Phones::findFirstByPhoneid($phoneId);

            if ($phone->countOfReferences() == 0)
                $phone->delete();

            $response->setJsonContent(
                [
                    "status" => STATUS_OK,
                ]
            );
            return $response;


        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Убирает телефон из списка телефонов точки
     *
     * @method DELETE
     *
     * @param int $phoneId
     * @param int $pointId
     * @return Phalcon\Http\Response с json массивом в формате Status
     */
    public function deletePhoneFromTradePointAction($phoneId, $pointId)
    {
        if ($this->request->isDelete()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $phonesPoint = PhonesPoints::findByIds($pointId, $phoneId);

            if (!$phonesPoint) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Телефон не существует']
                    ]
                );
                return $response;
            }

            $point = $phonesPoint->tradepoints;

            if (!$point || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $point->getSubjectId(), $point->getSubjectType(),
                    'editPoint')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$phonesPoint->delete()) {
                $errors = [];
                foreach ($phonesPoint->getMessages() as $message) {
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

            $phone = Phones::findFirstByPhoneid($phoneId);

            if ($phone->countOfReferences() == 0)
                $phone->delete();

            $response->setJsonContent(
                [
                    "status" => STATUS_OK,
                ]
            );
            return $response;


        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }


    /**
     * Изменяет определенный номер телефона у определенной точки услуг
     * @method PUT
     * @params integer pointId, string phone (новый) и integer phoneId (старый)
     * @return Phalcon\Http\Response с json ответом в формате Status;
     */
    public function editPhoneInTradePointAction()
    {
        if ($this->request->isPut()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();
            $point = TradePoints::findFirstByPointid($this->request->getPut("pointId"));


            if (!$point || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $point->getSubjectId(), $point->getSubjectType(),
                    'editPoint')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }


            $this->db->begin();

            if ($this->request->getPut("phone") && $this->request->getPut("phoneId")) {

                $phone = Phones::findFirstByPhoneid($this->request->getPut("phoneId"));

                if (!$phone) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['Id старого номера не существует']
                        ]
                    );
                    return $response;
                }

                if ($phone->countOfReferences() < 2) {
                    $phone->setPhone($this->request->getPut("phone"));
                } else {

                    $phone = new Phones();
                    $phone->setPhone($this->request->getPut("phone"));

                    $phonePoint = new PhonesPoints();
                }

                $phonesPoint = PhonesPoints::findByIds($this->request->getPut("pointId"),
                    $this->request->getPut("phoneId"));

                if (!$phonesPoint) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['Телефон не связан с точкой оказания услуг']
                        ]
                    );
                    return $response;
                }

                if (!$phonesPoint->delete()) {
                    $errors = [];
                    foreach ($phonesPoint->getMessages() as $message) {
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

                if (!$phone->save()) {

                    $this->db->rollback();
                    $errors = [];
                    foreach ($phone->getMessages() as $message) {
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

                $phonePoint->setPointId($point->getPointId());
                $phonePoint->setPhoneId($phone->getPhoneId());

                if (!$phonePoint->save()) {

                    $this->db->rollback();
                    $errors = [];
                    foreach ($phonePoint->getMessages() as $message) {
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

            } else {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нужно указать новый номер телефона phone и id старого phoneId']
                    ]
                );
                return $response;
            }

            $this->db->commit();

            $response->setJsonContent(
                [
                    'status' => STATUS_OK
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /*public function testAction(){
        $response = new Response();
        $phonesCompany = PhonesCompanies::findFirst(["companyId = :companyId: and phoneId = :phoneId:", "bind" =>
            ["companyId" => $this->request->getPut("companyId"),
                "phoneId" => $this->request->getPut("phoneId")]
        ]);

        //$phonesCompany->setPhoneId($this->request->getPut("phoneId2"));
        $phonesCompany->setCompanyId(21);

        if (!$phonesCompany->save()) {

            $this->db->rollback();
            $errors = [];
            foreach ($phonesCompany->getMessages() as $message) {
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
                'status' => STATUS_OK
            ]
        );
        return $response;
    }*/

    /**
     * Изменяет определенный номер телефона у определенной компании
     * @method PUT
     * @params integer companyId, string phone (новый) и integer phoneId (старый)
     * @return Phalcon\Http\Response с json ответом в формате Status;
     */
    public function editPhoneInCompanyAction()
    {
        if ($this->request->isPut()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $phonesCompany = PhonesCompanies::findByIds($this->request->getPut("companyId"),
                $this->request->getPut("phoneId"));

            if (!$phonesCompany) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Телефон не существует']
                    ]
                );
                return $response;
            }

            if (!Companies::checkUserHavePermission($userId, $this->request->getPut("companyId"), 'editCompany')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $this->db->begin();

            if ($this->request->getPut("phone") && $this->request->getPut("phoneId")) {

                $phone = Phones::findFirstByPhoneid($this->request->getPut("phoneId"));

                if (!$phone) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['Id старого номера не существует']
                        ]
                    );
                    return $response;
                }

                if ($phone->countOfReferences() < 2) {
                    $phone->setPhone($this->request->getPut("phone"));

                } else {
                    //Удаляем предыдущую связь, создаем новый телефон и связываем с ним
                    $phone = new Phones();
                    $phone->setPhone($this->request->getPut("phone"));

                }

                if (!$phonesCompany->delete()) {
                    $errors = [];
                    foreach ($phonesCompany->getMessages() as $message) {
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
                $phonesCompany = new PhonesCompanies();

                if (!$phone->save()) {

                    $this->db->rollback();
                    $errors = [];
                    foreach ($phone->getMessages() as $message) {
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

                $phonesCompany->setCompanyId($this->request->getPut("companyId"));
                $phonesCompany->setPhoneId($phone->getPhoneId());

                if (!$phonesCompany->save()) {

                    $this->db->rollback();
                    $errors = [];
                    foreach ($phonesCompany->getMessages() as $message) {
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

            } else {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нужно указать новый номер телефона phone и id старого phoneId']
                    ]
                );
                return $response;
            }

            $this->db->commit();

            $response->setJsonContent(
                [
                    'status' => STATUS_OK
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Добавляет телефон пользователю.
     * Приватный метод.
     *
     * @method POST
     *
     * @params string phone или integer phoneId
     * @return Phalcon\Http\Response с json ответом в формате Status;
     */
    public function addPhoneToUserAction()
    {
        if ($this->request->isPost()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $user = Users::findFirstByUserid($userId);

            $this->db->begin();
            if ($this->request->getPost("phone")) {
                //Создаем новый
                $phone = new Phones();
                $phone->setPhone($this->request->getPost("phone"));

                if (!$phone->save()) {
                    $this->rollback();
                    $errors = [];
                    foreach ($phone->getMessages() as $message) {
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
            } else if ($this->request->getPost("phoneId")) {
                $phone = Phones::findFirstByPhoneid($this->request->getPost("phoneId"));

                if (!$phone) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['телефона с таким id не существует']
                        ]
                    );
                    return $response;
                }
            } else {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нужно указать номер телефона или id существующего в параметрах \'phone\', \'phoneId\'']
                    ]
                );
                return $response;
            }

            $phoneUser = PhonesUsers::findByIds($user->getUserId(), $phone->getPhoneId());
            if ($phoneUser) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_ALREADY_EXISTS,
                        "errors" => ['Телефон уже привязан к пользователю']
                    ]
                );
                return $response;
            }

            $phoneUser = new PhonesUsers();

            $phoneUser->setUserId($user->getUserId());
            $phoneUser->setPhoneId($phone->getPhoneId());

            if (!$phoneUser->save()) {

                $this->db->rollback();
                $errors = [];
                foreach ($phoneUser->getMessages() as $message) {
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

            $this->db->commit();

            $response->setJsonContent(
                [
                    'status' => STATUS_OK
                ]
            );
            return $response;


        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Убирает телефон из списка телефонов пользователя.
     * Приватный метод.
     *
     * @method DELETE
     *
     * @param int $phoneId
     * @return Phalcon\Http\Response с json массивом в формате Status
     */
    public function deletePhoneFromUserAction($phoneId)
    {
        if ($this->request->isDelete()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $phonesUsers = PhonesUsers::findByIds($userId, $phoneId);

            if (!$phonesUsers) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Телефон не существует']
                    ]
                );
                return $response;
            }

            if (!$phonesUsers->delete()) {
                $errors = [];
                foreach ($phonesUsers->getMessages() as $message) {
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

            $phone = Phones::findFirstByPhoneid($phoneId);

            if ($phone->countOfReferences() == 0)
                $phone->delete();

            $response->setJsonContent(
                [
                    "status" => STATUS_OK,
                ]
            );
            return $response;


        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}
