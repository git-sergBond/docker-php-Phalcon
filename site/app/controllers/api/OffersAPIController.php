<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

/**
 * Контроллер для работы с предложениями.
 * Реализует CRUD для работы с предложениями.
 * Есть методы для изменения статуса выполнения заказа с точки зрения исполнителя:
 *      - подтвердил согласие выполнения;
 *      - отказался выполнить задание;
 *      - подтверждает выпоолнение задания.
 */
class OffersAPIController extends Controller
{
    /**
     * Возвращает предложения для определенного задания
     * @method GET
     * @param $taskId
     *
     * @return string - json array объектов Offers
     */
    public function getForTaskAction($taskId)
    {
        if ($this->request->isGet()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            if (!Tasks::checkUserHavePermission($userId, $taskId, 'getOffers')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $offers = Offers::findByTaskid($taskId);
            $response->setJsonContent($offers);
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Добавляет предложение на выполнение указанного задания
     *
     * @method POST
     *
     * @params (Обязательные) taskId, deadline, price.
     * @params (Необязательные) description, companyId.
     *
     * @return string - json array в формате Status
     */
    public function addOfferAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            if ($this->request->getPost("companyId")) {
                if (!Companies::checkUserHavePermission($userId, $this->request->getPost("companyId"), 'addOffer')) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }
            }

            $task = Tasks::findFirstByTaskid($this->request->getPost("taskId"));

            /*$offer = Offers::findFirst(['taskId = :taskId: AND selected = true',
                'bind' => ['taskId' => $this->request->getPost("taskId")]]);*/

            if ($task->getStatus() != STATUS_ACCEPTING) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Время приема заявок завершилось']
                    ]
                );
                return $response;
            }
            if ($this->request->getPost("companyId")) {
                $offer = Offers::findFirst(['taskid = :taskId: AND subjectid = :companyId: AND subjecttype = 1',
                    'bind' =>
                        ['taskId' => $this->request->getPost("taskId"),
                            'companyId' => $this->request->getPost("companyId")]]);
            } else {
                $offer = Offers::findFirst(['taskid = :taskId: AND subjectid = :userId: AND subjecttype = 0',
                    'bind' =>
                        ['taskId' => $this->request->getPost("taskId"),
                            'userId' => $userId]]);
            }

            if ($offer) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Заявка на выполнение уже оставлена текущим субъектом']
                    ]
                );
                return $response;
            }

            $offer = new Offers();

            if ($this->request->getPost("companyId")) {
                $offer->setSubjectId($this->request->getPost("companyId"));
                $offer->setSubjectType(1);
            } else {
                $offer->setSubjectId($userId);
                $offer->setSubjectType(0);
            }

            $offer->setTaskId($this->request->getPost("taskId"));
            $offer->setDeadline(date('Y-m-d H:i:s', strtotime($this->request->getPost("deadline"))));
            $offer->setPrice($this->request->getPost("price"));
            $offer->setDescription($this->request->getPost("description"));

            if (!$offer->save()) {
                $errors = [];
                foreach ($offer->getMessages() as $message) {
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
     * Возвращает офферсы субъекта
     *
     * @method GET
     *
     * @param $companyId (необязательный). Если не отправить, будут возвращены для текущего пользователя
     *
     * @return string - json array объектов Offers
     */
    public function getForSubjectAction($companyId = null)
    {
        if ($this->request->isGet()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            if($companyId == null){
                $offers = Offers::find(['subjectid = :userId: AND subjecttype = 0',
                    'bind' => ['userId' => $userId]]);
            } else{
                if(!Companies::checkUserHavePermission($userId,$companyId,'getOffers'))
                {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }
                $offers = Offers::find(['subjectid = :companyId: AND subjecttype = 1',
                    'bind' => ['companyId' => $companyId]]);
            }

            $response->setJsonContent($offers);

            return $response;


        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Удаляет предложение на выполнение заявки
     *
     * @method DELETE
     * @param $offerId
     *
     * @return string - json array в формате Status
     */
    public function deleteOfferAction($offerId)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $offer = Offers::findFirstByOfferid($offerId);

            if (!$offer) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['предложение не существует']
                    ]
                );
                return $response;
            }

            if ($offer->getSelected()) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нельзя удалить выбранное предложение']
                    ]
                );
                return $response;
            }

            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $offer->getSubjectId(),$offer->getSubjectType(),
                'deleteOffer')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$offer->delete()) {
                $errors = [];
                foreach ($offer->getMessages() as $message) {
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
     * Редактирует предложение на выполнение указанного задания
     *
     * @method PUT
     *
     * @params (Обязательные) offerId, deadline, price.
     * @params (Необязательные) description, companyId.
     *
     * @return string - json array в формате Status
     */
    public function editOfferAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $offer = Offers::findFirstByOfferid($this->request->getPut("offerId"));
            if(!$offer || !SubjectsWithNotDeleted::checkUserHavePermission($userId,$offer->getSubjectId(),$offer->getSubjectType(),'editOffer')){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $task = $offer->tasks;

            if(!$task->getStatus() == STATUS_ACCEPTING){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Задание уже выполняется']
                    ]
                );
                return $response;
            }

            if ($this->request->getPut("companyId")) {

                if(!Companies::checkUserHavePermission($userId,$this->request->getPut("companyId"),'addOffer')){
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }

                $offer->setSubjectId($this->request->getPut("companyId"));
                $offer->setSubjectType(1);
            } else {
                $offer->setSubjectId($userId);
                $offer->setSubjectType(0);
            }

            if($this->request->getPut("deadline"))
                $offer->setDeadline(date('Y-m-d H:i:s', strtotime($this->request->getPut("deadline"))));
            $offer->setPrice($this->request->getPut("price"));
            $offer->setDescription($this->request->getPut("description"));

            if (!$offer->save()) {
                foreach ($offer->getMessages() as $message) {
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
     * Подтверждает согласие исполнителя выполнить задание
     *
     * @method PUT
     *
     * @params offerId
     *
     * @return string - json array в формате Status
     */
    public function confirmOfferAction(){
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $offer = Offers::findFirstByOfferid($this->request->getPut("offerId"));
            if(!$offer ||!SubjectsWithNotDeleted::checkUserHavePermission($userId,$offer->getSubjectId(),$offer->getSubjectType(),'confirmOffer')){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$offer->confirm()) {
                $errors = [];
                foreach ($offer->getMessages() as $message) {
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
     * Исполнитель отказывается от своего первоначального намерения выполнить заказ
     *
     * @method PUT
     *
     * @params offerId
     *
     * @return string - json array в формате Status
     */
    public function rejectOfferAction(){
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $offer = Offers::findFirstByOfferid($this->request->getPut("offerId"));
            if(!$offer || !SubjectsWithNotDeleted::checkUserHavePermission($userId,$offer->getSubjectId(),$offer->getSubjectType(),'rejectOffer')){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }


            if (!$offer->reject()) {
                $errors = [];
                foreach ($offer->getMessages() as $message) {
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
     * Исполнитель утверждает, что выполнил заказ
     *
     * @method PUT
     *
     * @params offerId
     *
     * @return string - json array в формате Status
     */
    public function performTaskAction(){
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $offer = Offers::findFirstByOfferid($this->request->getPut("offerId"));

            if(!$offer || !SubjectsWithNotDeleted::checkUserHavePermission($userId,$offer->getSubjectId(),$offer->getSubjectType(),
                    'performTask')){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $task = $offer->tasks;

            if($task->getStatus() != STATUS_EXECUTING){
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нельзя отметить задание как выполненное в текущем состоянии']
                    ]
                );
                return $response;
            }

            $task->setStatus(STATUS_EXECUTED_EXECUTOR);

            if (!$task->update()) {
                $errors = [];
                foreach ($task->getMessages() as $message) {
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

    public function addStatusAction()
    {
        $status = new Statuses();
        $status->setStatus($this->request->getPost('status'));
        $status->setStatusId($this->request->getPost('statusId'));
        return $status->save();
    }
}
