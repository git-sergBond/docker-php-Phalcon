<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

/**
 * Контроллер для работы с подписками на компании
 * Реализует методы для подписки пользователя на компании, отписки и получения подписок.
 */
class FavouriteCompaniesAPIController extends Controller
{
    /**
     * Подписывает текущего пользователя на компанию
     *
     * @method POST
     *
     * @params companyId
     *
     * @return Response с json ответом в формате Status
     */
    public function setFavouriteAction()
    {
        if ($this->request->isPost()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $companyId = $this->request->getPost('companyId');

            $fav = FavoriteCompanies::findByIds($userId, $companyId);

            if(!$fav){
                $fav = new FavoriteCompanies();
                $fav->setUserId($userId);
                $fav->setCompanyId($companyId);

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
                    "errors" => ["Пользователь уже подписан на компанию"]
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }


    /**
     * Отменяет подписку на компанию
     *
     * @method DELETE
     *
     * @param $companyId
     *
     * @return Response с json ответом в формате Status
     */
    public function deleteFavouriteAction($companyId)
    {
        if ($this->request->isDelete()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $fav = FavoriteCompanies::findByIds($userId,$companyId);

            if($fav){
                if (!$fav->delete()) {
                    $errors =[];
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
                    "errors" => ["Пользователь не подписан на компанию"]
                ]
            );

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }


    /**
     * Возвращает подписки пользователя на компании
     *
     * @return string - json array с подписками (просто id-шники)
     */
    public function getFavouritesAction()
    {
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $favs = FavoriteCompanies::findByUserid($userId);

            $response->setJsonContent($favs);
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}
