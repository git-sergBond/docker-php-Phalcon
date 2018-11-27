<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

/**
 * Контроллер для работы с компаниями.
 * Реализует CRUD для компаний, содержит методы для настройки менеджеров.
 */
class CompaniesAPIController extends Controller
{
    /**
     * Возвращает компании текущего пользователя
     *
     * @method GET
     * @return string - json array компаний
     */
    public function getCompaniesAction($withPoints = false)
    {
        if (($this->request->isGet() && $this->session->get('auth'))) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();
            $companies = Companies::findByUserid($userId);

            if($withPoints){
                $companies2 = [];
                foreach($companies as $company) {
                    $points = TradePoints::findBySubject($company->getCompanyId(),1);
                    $companies2[] = ['company' => $company, 'points' => $points];
                }
                $response->setJsonContent([
                    'status' => STATUS_OK,
                    'companies' => $companies2
                ]);
                return $response;
            }

            $response->setJsonContent([
                'status' => STATUS_OK,
                'companies' => $companies
            ]);
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Создает компанию.
     *
     * @method POST
     * @params (Обязательные)name, fullName
     * @params (необязательные) TIN, regionId, webSite, email, description
     * @params (для модератора) isMaster - если true, то еще и userId - кому будет принадлежать
     * @return Response
     */
    public function addCompanyAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $company = new Companies();
            $company->setName($this->request->getPost("name"));
            $company->setFullname($this->request->getPost("fullName"));
            $company->setTin($this->request->getPost("TIN"));
            $company->setRegionId($this->request->getPost("regionId"));
            $company->setWebSite($this->request->getPost("webSite"));
            $company->setEmail($this->request->getPost("email"));
            $company->setDescription($this->request->getPost("description"));

            if ($this->request->getPost("isMaster") && $this->request->getPost("isMaster") != 0) {
                if ($auth['role'] != ROLE_MODERATOR) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['Ошибка доступа']
                        ]
                    );
                    return $response;
                }

                $company->setIsMaster(true);

                if ($this->request->getPost("userId"))
                    $company->setUserid($this->request->getPost("userId"));
            } else {
                $company->setIsMaster(0);
                $company->setUserid($userId);
            }

            if (!$company->save()) {
                $errors = [];
                foreach ($company->getMessages() as $message) {
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
     * Удаляет указанную компанию
     * @method DELETE
     *
     * @param $companyId
     * @return string - json array Status
     */
    public function deleteCompanyAction($companyId)
    {
        if ($this->request->isDelete()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            if (!Companies::checkUserHavePermission($userId, $companyId, 'deleteCompany')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $company = Companies::findFirstByCompanyid($companyId);
            if (!$company->delete()) {
                $errors = [];
                foreach ($company->getMessages() as $message) {
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
     * Редактирует данные компании
     * @method PUT
     * @params companyId, name, fullName,TIN, regionId, webSite, email, description
     * @params isMaster - если true, то еще и userId - кому будет принадлежать
     * @return Response
     */
    public function editCompanyAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            if (!Companies::checkUserHavePermission($userId, $this->request->getPut("companyId"),
                'editCompany')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $company = Companies::findFirstByCompanyid($this->request->getPut("companyId"));

            $company->setName($this->request->getPut("name"));
            $company->setFullname($this->request->getPut("fullName"));
            $company->setTin($this->request->getPut("TIN"));
            $company->setRegionId($this->request->getPut("regionId"));
            $company->setWebSite($this->request->getPut("webSite"));
            $company->setEmail($this->request->getPut("email"));
            $company->setDescription($this->request->getPut("description"));

            if ($this->request->getPut("isMaster") && $this->request->getPut("isMaster") != 0) {

                if ($auth['role'] != ROLE_MODERATOR) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['Ошибка доступа']
                        ]
                    );
                    return $response;
                }

                $company->setIsMaster(true);

                if ($this->request->getPut("userId"))
                    $company->setUserid($this->request->getPut("userId"));
            } else {
                $company->setIsMaster(0);
                $company->setUserid($userId);
            }

            if (!$company->save()) {
                $errors = [];
                foreach ($company->getMessages() as $message) {
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
     * Устанавливает логотип для компании. Сам логотип должен быть передан в файлах. ($_FILES)
     * @method POST
     * @params companyId
     * @return Response
     */
    public function setCompanyLogotypeAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            if (!Companies::checkUserHavePermission($userId, $this->request->getPost("companyId"),
                'editCompany')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$this->request->hasFiles()) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Логотип не был загружен']
                    ]
                );
                return $response;
            }
            $files = $this->request->getUploadedFiles();

            $file = $files[0];

            $format = pathinfo($file->getName(),PATHINFO_EXTENSION);

            $logotype = ImageLoader::formFullImageName('companies',$format,
                $this->request->getPost("companyId"),$this->request->getPost("companyId"));

            $company = Companies::findFirstByCompanyid($this->request->getPost("companyId"));

            $company->setLogotype($logotype);
            $this->db->begin();
            if (!$company->update()) {
                $this->db->rollback();
                $errors = [];
                foreach ($company->getMessages() as $message) {
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

            $result = ImageLoader::loadCompanyLogotype($file->getTempName(),$file->getName(),$company->getCompanyId(),$company->getCompanyId());

            if($result != ImageLoader::RESULT_ALL_OK || $result === null){
                if($result == ImageLoader::RESULT_ERROR_FORMAT_NOT_SUPPORTED){
                    $error = 'Формат одного из изображений не поддерживается';
                } elseif($result == ImageLoader::RESULT_ERROR_NOT_SAVED){
                    $error = 'Не удалось сохранить изображение';
                }
                else{
                    $error = 'Ошибка при загрузке изображения';
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => [$error]
                    ]
                );
                return $response;
            }

            $this->db->commit();

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
     * Делает указанного пользователя менеджером компании
     *
     * @method POST
     *
     * @params userId, companyId
     *
     * @return string - json array - объект Status
     */
    public function setManagerAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            if (!Companies::checkUserHavePermission($userId, $this->request->getPost('companyId'),
                'addManager')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $companyManager = new CompaniesManagers();
            $companyManager->setUserId($this->request->getPost('userId'));
            $companyManager->setCompanyId($this->request->getPost('companyId'));


            if (!$companyManager->save()) {
                $errors = [];
                foreach ($companyManager->getMessages() as $message) {
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
     * Удаляет пользователя из менеджеров компании
     *
     * @method DELETE
     *
     * @param $userManagerId
     * @param $companyId
     *
     * @return string - json array - объект Status
     */
    public function deleteManagerAction($companyId, $userManagerId)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            if (!Companies::checkUserHavePermission($userId, $companyId, 'deleteManager')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $companyManager = CompaniesManagers::findByIds($companyId, $userManagerId);

            if (!$companyManager) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Пользователь не является менеджером компании']
                    ]
                );
                return $response;
            }


            if (!$companyManager->delete()) {
                $errors = [];
                foreach ($companyManager->getMessages() as $message) {
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
     * Восстанавливает компанию
     *
     * @method POST
     *
     * @params companyId
     *
     * @return string - json array - объект Status - результат операции
     */
    public function restoreCompanyAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $company = Companies::findFirst(['companyid = :companyId:',
                'bind' => ['companyId' => $this->request->getPost('companyId')]], false);

            if (!$company || !Companies::checkUserHavePermission($userId, $company->getCompanyId(), 'restoreCompany')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$company->restore()) {
                $errors = [];
                foreach ($company->getMessages() as $message) {
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
                    "status" => STATUS_OK,
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function deleteCompanyTestAction($companyId)
    {
        if ($this->request->isDelete()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            if (!Companies::checkUserHavePermission($userId, $companyId, 'deleteCompany')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $company = Companies::findFirstByCompanyid($companyId);
            if (!$company->delete(true)) {
                $errors = [];
                foreach ($company->getMessages() as $message) {
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
     * Возвращает публичную информацию о компании.
     * Публичный доступ
     *
     * @method GET
     *
     * @param $companyId
     * @return string - json array компаний
     */
    public function getCompanyInfoAction($companyId)
    {
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $response = new Response();

            $company = Companies::findFirst(['companyid = :companyId:',
                'bind'=>['companyId' => $companyId],
            'columns' => Companies::publicColumns]);

            if(!$company){
                $response->setJsonContent([
                    'status' => STATUS_WRONG,
                    'errors' => ['Компания не существует']
                ]);
                return $response;
            }

            $company = json_encode($company);
            $company = json_decode($company,true);

            $phones = PhonesCompanies::getCompanyPhones($companyId);

            $response->setJsonContent([
                'status' => STATUS_OK,
                'company' => $company,
                'phones' => $phones,
            ]);
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}
