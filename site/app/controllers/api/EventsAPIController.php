<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;


/**
 * Class EventsAPIController
 * Предназначен для работы с акциями.
 * Реализует CRUD для акций.
 */
class EventsAPIController extends Controller
{
    /**
     * Добавляет новую акцию для текущего пользователя или же для указанной компании.
     * Акция должна быть связана либо с конкретной точкой на карте, либо с одной из
     * точек оказания услуг пользователя/компании.
     *
     * @method POST
     *
     * @params name (обязательно)
     * @params description (не обязательно)
     * @params companyId (не обязательно)
     * @params pointId (не обязательно)
     * @params longitude (не обязательно)
     * @params latitude (не обязательно)
     *
     * @return string - json array формате Status и id созданной акции ([status, eventId])
     */
    public function addEventAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $event = new Events();

            if ($this->request->getPost("companyId")) {
                //Значит, от лица компании
                if (!Companies::checkUserHavePermission($userId,
                    $this->request->getPost("companyId"), 'addEvent')) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }
                $event->setSubjectId($this->request->getPost("companyId"));
                $event->setSubjectType(1);
            } else {
                $event->setSubjectId($userId);
                $event->setSubjectType(0);
            }

            $event->setName($this->request->getPost("name"));
            $event->setDescription($this->request->getPost("description"));
            $event->setDatePublication(date('Y-m-d H:i:s'));
            $event->setPointId($this->request->getPost("pointId"));
            $event->setLatitude($this->request->getPost("latitude"));
            $event->setLongitude($this->request->getPost("longitude"));

            if (!$event->save()) {
                $errors = [];
                foreach ($event->getMessages() as $message) {
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
                    'eventId' => $event->getEventId()
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Устанавливает для указанной акции изображение.
     *
     * @method POST
     * @params eventId
     * @params файл с изображением.
     * @return Response
     */
    public function setImageAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $response = new Response();
            if ($this->request->hasFiles()) {

                $auth = $this->session->get('auth');
                $userId = $auth['id'];
                $event = Events::findFirstByEventid($this->request->getPost('eventId'));

                if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $event->getSubjectId(),
                    $event->getSubjectType(), 'editEvent')) {

                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }

                $file = $this->request->getUploadedFiles();
                $file = $file[0];

                $format = pathinfo($file->getName(), PATHINFO_EXTENSION);

                $filename = ImageLoader::formFullImageName('events', $format, $event->getEventId(), 0);
                $event->setPathToImage($filename);

                if (!$event->update()) {
                    $errors = [];
                    foreach ($event->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $response->setJsonContent(
                        [
                            "errors" => $errors,
                            "status" => STATUS_WRONG
                        ]);

                    return $response;
                }

                ImageLoader::loadEventImage($file->getTempName(), $file->getName(), $event->getEventId());

                $response->setJsonContent(
                    [
                        "status" => STATUS_OK
                    ]
                );
                return $response;
            }
            $response->setJsonContent(
                [
                    "status" => STATUS_WRONG,
                    "errors" => ['Файл не был получен']
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Удаляет указанную акцию.
     *
     * @method DELETE
     *
     * @param $eventId
     *
     * @return string - json array в формате Status
     */
    public function deleteEventAction($eventId)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $response = new Response();

            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $event = Events::findFirstByEventid($eventId);

            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $event->getSubjectId(),
                $event->getSubjectType(), 'deleteEvent')) {

                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$event->delete()) {
                $errors = [];
                foreach ($event->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "errors" => $errors,
                        "status" => STATUS_WRONG
                    ]);

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
     * Редактирует указанную акцию.
     *
     * @method PUT
     *
     * @params eventId
     * @params name (обязательно)
     * @params description (не обязательно)
     * @params pointId (не обязательно)
     * @params longitude (не обязательно)
     * @params latitude (не обязательно)
     *
     * @return string - json array в формате Status
     */
    public function editEventAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();

            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $event = Events::findFirstByEventid($this->request->getPut('eventId'));

            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $event->getSubjectId(),
                $event->getSubjectType(), 'editEvent')) {

                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $event->setName($this->request->getPut("name"));
            $event->setDescription($this->request->getPut("description"));
            $event->setDatePublication(date('Y-m-d H:i:s'));
            $event->setPointId($this->request->getPut("pointId"));
            $event->setLatitude($this->request->getPut("latitude"));
            $event->setLongitude($this->request->getPut("longitude"));

            if (!$event->update()) {
                $errors = [];
                foreach ($event->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "errors" => $errors,
                        "status" => STATUS_WRONG
                    ]);

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
     * Возвращает акции конкретной компании или пользователя.
     *
     * @method DELETE
     *
     * @param $subjectId
     * @param $subjectType
     *
     * @return string - json array [status, [массив events]].
     */
    public function getEventsAction($subjectId, $subjectType)
    {
        if ($this->request->isGet()) {
            $response = new Response();
            $events = Events::findBySubject($subjectId,$subjectType);

            $response->setJsonContent(
                [
                    "status" => STATUS_OK,
                    'events' => $events
                ]
            );
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}