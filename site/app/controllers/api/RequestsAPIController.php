<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

/**
 * Class RequestsAPIController
 * Контроллер для работы с заявками на получение услуги.
 * Реализует CRUD для запросов и
 * методы изменения статуса выполнения заявки с точки зрения клиента (не все статусы):
 *      - отмена заявки;
 *      - подтверждение выполнения заявки.
 */
class RequestsAPIController extends Controller
{
    /**
     * Добавляет запрос на получение услуги
     *
     * @method POST
     *
     * @params serviceId, description, dateEnd.
     * @params (необязательный) companyId
     * @return Response с json массивом в формате Status
     */
    public function addRequestAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = new Requests();

            if ($this->request->getPost("companyId")) {
                if (!Companies::checkUserHavePermission($userId, $this->request->getPost("companyId"),
                    'addRequest')) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }

                $request->setSubjectId($this->request->getPost("companyId"));
                $request->setSubjectType(1);

            } else {
                $request->setSubjectId($userId);
                $request->setSubjectType(0);
            }

            $request->setServiceId($this->request->getPost("serviceId"));
            $request->setDescription($this->request->getPost("description"));
            $request->setDateEnd(date('Y-m-d H:i:s', strtotime($this->request->getPost("dateEnd"))));
            $request->setStatus(STATUS_WAITING_CONFIRM);

            if (!$request->save()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
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
     * Удаляет заявку
     *
     * @method DELETE
     *
     * @param $requestId
     * @return Response с json массивом в формате Status
     */
    public function deleteRequestAction($requestId)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = Requests::findFirstByRequestid($requestId);

            if (!$request ||
                (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $request->getSubjectId(), $request->getSubjectType(),
                    'deleteRequest'))) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$request->delete()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
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
     * Редактирует заявку
     *
     * @method PUT
     *
     * @params requestId, description, dateEnd, (необязательные)companyId, userId
     * @return Response с json массивом в формате Status
     */
    public function editRequestAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = Requests::findFirstByRequestid($this->request->getPut('requestId'));

            if (!$request ||
                (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $request->getSubjectId(), $request->getSubjectType(),
                    'editRequest'))) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }
            if ($this->request->getPut('companyId')) {
                $request->setSubjectId($this->request->getPut("companyId"));
                $request->setSubjectType(1);

            } else if ($this->request->getPut('userId')) {
                $request->setSubjectId($this->request->getPut('userId'));
                $request->setSubjectType(0);
            }

            $request->setDescription($this->request->getPut('description'));
            $request->setDateEnd(date('Y-m-d H:i:s', strtotime($this->request->getPut('dateEnd'))));

            if (!$request->save()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
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
     * Редактирует заявку
     *
     * @method GET
     *
     * @param $companyId (необязательный)
     * @return string - json массив с объектами Requests и Status-ом
     */
    public function getRequestsAction($companyId = null)
    {
        if ($this->request->isGet() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            if ($companyId == null) {
                $subjectId = $userId;
                $subjectType = 0;
            } else {
                $subjectId = $companyId;
                $subjectType = 1;
            }
            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $subjectId, $subjectType,
                'getRequests')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $requests = Requests::findBySubject($subjectId,$subjectType);

            $response->setJsonContent(
                [
                    'status' => STATUS_OK,
                    'requests' => $requests
                ]
            );
            return $response;


        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Заказчик отменяет заявку.
     *
     * @method PUT
     *
     * @params requestId
     *
     * @return string - json array в формате Status
     */
    public function cancelRequestAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = Requests::findFirstByRequestid($this->request->getPut("requestId"));

            if (!$request || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $request->getSubjectId(),
                    $request->getSubjectType(), 'cancelRequest')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if ($request->getStatus() != STATUS_WAITING_CONFIRM &&
                $request->getStatus() != STATUS_EXECUTING &&
                $request->getStatus() != STATUS_EXECUTED_EXECUTOR) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нельзя отменить заказ на данном этапе']
                    ]
                );
                return $response;
            }

            if ($request->getStatus() == STATUS_WAITING_CONFIRM)
                $request->setStatus(STATUS_CANCELED);
            else {
                $request->setStatus(STATUS_NOT_EXECUTED);
            }

            if (!$request->update()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
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
     * Заказчик подтверждает выполнение заявки
     *
     * @method PUT
     *
     * @params requestId
     *
     * @return string - json array в формате Status
     */
    public function confirmPerformanceRequestAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = Requests::findFirstByRequestid($this->request->getPut("requestId"));

            if (!$request || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $request->getSubjectId(),
                    $request->getSubjectType(), 'cancelRequest')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if ($request->getStatus() != STATUS_EXECUTED_EXECUTOR && $request->getStatus() != STATUS_EXECUTING) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нельзя на данном этапе подтвердить выполнение заказа']
                    ]
                );
                return $response;
            }

            $request->setStatus(STATUS_EXECUTED_CLIENT);

            if (!$request->update()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
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
}
