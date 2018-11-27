<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

/**
 * Class MessagesAPIController
 * Контроллер для работы с сообщениями.
 * Содержит методы для добавления сообщения, просмотра сообщений пользователя,
 * просмотра "чатов", а также для просмотров переписки с одним человеком.
 */
class MessagesAPIController extends Controller
{

    /**
     * Отправляет сообщение
     *
     * @method POST
     *
     * @params int userIdObject, string message,
     * @return Phalcon\Http\Response с json массивом в формате Status
     */
    public function addMessageAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $response = new Response();

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $message = new Messages();
            $message->setUserIdObject($this->request->getPost("userIdObject"));
            $message->setUserIdSubject($userId);
            $message->setMessage($this->request->getPost("message"));
            $message->setDate(date('Y-m-d H:i:s'));

            if (!$message->save()) {
                foreach ($message->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }

                $response->setJsonContent(
                    [
                        "errors" => $errors,
                        "status" => "WRONG_DATA"
                    ]
                );

                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }


    /**
     * Возвращает все сообщения данного пользователя
     *
     * @method GET
     *
     * @return string с json массивом объектов Message
     */
    public function getMessagesAction()
    {
        if ($this->request->isGet() && $this->session->get('auth')) {

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $messages = Messages::find(['userIdObject = :userId: OR userIdSubject = :userId:', 'bind' => ['userId' => $userId]]);

            return json_encode($messages);

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }


    /**
     *
     * Возвращает всех пользователей, с которыми текущий пользователь чатился
     *
     * @method GET
     *
     * @return string json массив с - фамилией, именем, отчеством и последнее сообщение в чате
     */
    public function getChatsAction()
    {
        if ($this->request->isGet() && $this->session->get('auth')) {

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $messages = Messages::find(["userIdObject = :userId: OR userIdSubject = :userId:",
                'bind' => ['userId' => $userId], 'order' => 'date DESC']);

            $chats = [];

            foreach ($messages as $message) {
                if ($userId == $message->getUserIdObject()) {
                    //Юзер - получатель
                    $otherUserId = $message->getUserIdSubject();
                } else
                    $otherUserId = $message->getUserIdObject();

                if (!isset($chats[$otherUserId])) {

                    $userinfo = Userinfo::findFirstByUserId($otherUserId);

                    if (!$userinfo) {
                        continue;
                    }

                    $chats[$otherUserId] = ['Userinfo' =>
                        ['firstname' => $userinfo->getFirstname(), 'lastname' => $userinfo->getLastname(),
                            'patronymic' => $userinfo->getPatronymic(), 'pathToPhoto' => $userinfo->getPathToPhoto()],
                        'message' => $message];

                }

            }

            return json_encode($chats);

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     *
     * Возвращает сообщения переписки с одним пользователем
     *
     * @method GET
     *
     * @param $otherUserId
     *
     * @return string json массив с сообщениями (Messages)
     */
    public function getChatAction($otherUserId)
    {
        if ($this->request->isGet() && $this->session->get('auth')) {

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $messages = Messages::find(["(userIdObject = :userId: OR userIdSubject = :userId:) 
            AND (userIdObject = :otherUserId: OR userIdSubject = :otherUserId:)",
                'bind' => ['userId' => $userId, 'otherUserId' => $otherUserId], 'order' => 'date DESC']);

            return json_encode($messages);

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }
}