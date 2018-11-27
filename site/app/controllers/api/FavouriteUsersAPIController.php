<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

/**
 * Контроллер для работы с подписками на пользователей
 * Реализует методы для подписки пользователя на другого пользователя, отписки и получения подписок.
 */
class FavouriteUsersAPIController extends Controller
{
    /**
     * Подписывает текущего пользователя на указанного
     * @method POST
     * @params userId
     * @return string - json array Status
     */
    public function setFavouriteAction()
    {
        if ($this->request->isPost()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userIdSubject = $auth['id'];
            $userIdObject = $this->request->getPost('userId');

            $fav = FavoriteUsers::findByIds($userIdObject,$userIdSubject);

            if(!$fav){
                $fav = new FavoriteUsers();
                $fav->setUserObject($userIdObject);
                $fav->setUserSubject($userIdSubject);

                if (!$fav->save()) {
                    $errors = [];
                    foreach ($fav->getMessages() as $message) {
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
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_ALREADY_EXISTS,
                    "errors" => ["Пользователь уже подписан"]
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Отменяет подписку на пользователя
     * @method
     * @param $userIdObject
     * @return string - json array Status
     */
    public function deleteFavouriteAction($userIdObject)
    {
        if ($this->request->isDelete()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userSubject = $auth['id'];
            $userObject = $userIdObject;

            $fav = FavoriteUsers::findByIds($userObject,$userSubject);

            if($fav){
                if (!$fav->delete()) {
                    $errors = [];
                    foreach ($fav->getMessages() as $message) {
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
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_WRONG,
                    "errors" => ["Пользователь не подписан"]
                ]
            );

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Возвращает подписки текущего пользователя
     * @method GET
     * @return string - json array подписок
     */
    public function getFavouritesAction()
    {
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $fav = FavoriteUsers::findByUsersubject($userId);

            $response->setJsonContent($fav);
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}
