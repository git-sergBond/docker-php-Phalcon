<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

/**
 * Контроллер для работы с новостями.
 * Реализует CRUD для новостей, позволяет просматривать новости тех, на кого подписан текущий пользователь.
 * Ну и методы для прикрепления изображений к новости.
 */
class NewsAPIController extends Controller
{
    /**
     * Возвращает новости для ленты текущего пользователя
     * Пока прростая логика с выводом только лишь новостей (без других объектов типа заказов, услуг)
     *
     * @method GET
     *
     * @return string - json array с новостями (или их отсутствием)
     */
    public function getNewsAction()
    {
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            /*$favCompanies = FavoriteCompanies::findByUserid($userId);
            $favUsers = Favoriteusers::findByUsersubject($userId);

            $query = '';
            foreach ($favCompanies as $favCompany){
                if($query != '')
                    $query.=' OR ';
                $query .= '(subjectid = ' . $favCompany->getCompanyId() . ' AND subjecttype = 1)';
            }

            foreach ($favUsers as $favUser){
                if($query != '')
                    $query.=' OR ';
                $query .= '(subjectid = ' . $favUser->getUserObject() . ' AND subjecttype = 0)';
            }

            $news = News::find([$query, "order" => "News.date DESC"]);*/
            $news = News::getNewsForCurrentUser($userId);

            $response->setJsonContent($news);
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Создает новость компании или пользователя (в зависимости от subjectType).
     * Если прикрепить изображения, они будут добавлены к новости.
     *
     * @access private
     *
     * @method POST
     *
     * @params int companyId (если не передать, то от имени юзера)
     * @params string newsText
     * @params string title
     * @params файлы изображений.
     * @return string - json array объекта Status
     */
    public function addNewsAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $new = new News();
            //проверки
            if ($this->request->getPost('companyId') == null) {
                //Значит все просто
                $new->setSubjectId($userId);
                $new->setSubjectType(0);
            } else {

                if (!Companies::checkUserHavePermission($userId, $this->request->getPost('companyId'), 'addNew')) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }
                $company = Companies::findFirstByCompanyid($this->request->getPost('companyId'));
                $new->setSubjectId($company->getCompanyId());
                $new->setSubjectType(1);
            }

            $new->setPublishDate(date('Y-m-d H:i:s'));

            $new->setNewsText($this->request->getPost('newsText'));
            $new->setTitle($this->request->getPost('title'));

            //$this->db->begin();
            if (!$new->save()) {
                $errors = [];
                //$this->db->rollback();
                foreach ($new->getMessages() as $message) {
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

            if ($this->request->hasFiles()) {
                $result = $this->addImagesHandler($new->getNewsId());

                $resultContent = json_decode($result->getContent(), true);
                if ($resultContent['status'] != STATUS_OK) {
                    $new->delete(true);
                } else {
                    $resultContent['newId'] = $new->getNewsId();
                    $result->setJsonContent($resultContent);
                }
                return $result;
            }

            // $this->db->commit();
            $response->setJsonContent(
                [
                    "status" => STATUS_OK,
                    'newsId' => $new->getNewsId()
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Удаляет указанную новость
     *
     * @method DELETE
     *
     * @param $newsId
     *
     * @return string - json array объекта Status
     */
    public function deleteNewsAction($newsId)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $new = News::findFirstByNewsid($newsId);
            //проверки

            if (!$new) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Новость не существует']
                    ]
                );

                return $response;
            }
            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $new->getSubjectId(), $new->getSubjectType(), 'deleteNew')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$new->delete(true)) {
                foreach ($new->getMessages() as $message) {
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
     * Редактирует новость.при этом предполагается, что меняться будут только newText и дата новости.
     * Дата устанавливается текущая (на сервере).
     *
     * @method PUT
     *
     * @params int newsId, string newsText, title
     *
     * @return string - json array объекта Status
     */
    public function editNewsAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $new = News::findFirstByNewsid($this->request->getPut('newsId'));
            //проверки

            if (!$new) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Новость не существует']
                    ]
                );

                return $response;
            }

            //проверки
            if ($new->getSubjectType() == 0) {

                if ($new->getSubjectId() != $userId && auth['role'] != ROLE_MODERATOR) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }

            } else if ($new->getSubjectType() == 1) {

                $company = Companies::findFirstByCompanyid($new->getSubjectId());

                if (!$company || ($company->getUserId() != $userId && $auth['role'] != ROLE_MODERATOR)) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );

                    return $response;
                }
            }

            //Редактирование
            $new->setPublishDate(date('Y-m-d H:i:s'));
            $new->setNewsText($this->request->getPut('newsText'));
            $new->setTitle($this->request->getPut('title'));

            if (!$new->save()) {
                foreach ($new->getMessages() as $message) {
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
     * Возвращает новости текущего пользователя/указанной компании
     *
     * @method GET
     *
     * @param $companyId
     *
     * @return string - json array объектов news или Status, если ошибка
     */
    public function getOwnNewsAction($companyId = null)
    {
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            if ($companyId != null) {
                //Возвращаем новости компании
                $company = Companies::findFirstByCompanyid($companyId);

                if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $companyId, 1, 'getOwnNews')) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }

                $news = News::getNewsForSubject($companyId, 1);
            } else {
                //Возвращаем новости текущего пользователя
                $news = News::getNewsForSubject($userId, 0);
            }
            $response->setJsonContent($news);
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Возвращает новости указанного объекта
     *
     * @method GET
     *
     * @param $subjectId , $subjecttype
     *
     * @return string - json array объектов news или Status, если ошибка
     */
    public function getSubjectNewsAction($subjectId, $subjecttype)
    {
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $news = News::getNewsForSubject($subjectId, $subjecttype);

            $response->setJsonContent($news);
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Добавляет все прикрепленные изображения к новости. Но суммарно изображений не больше некоторого количества.
     *
     * @access private
     *
     * @method POST
     *
     * @params newsId
     * @params (обязательно) изображения. Именование не важно.
     *
     * @return string - json array в формате Status - результат операции
     */
    public function addImagesAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {

            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $newsId = $this->request->getPost('newsId');

            $news = News::findFirstByNewsid($newsId);

            if (!$news) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный идентификатор новости'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $news->getSubjectId(), $news->getSubjectType(),
                'editNew')) {
                $response->setJsonContent(
                    [
                        "errors" => ['permission error'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }
            $result = $this->addImagesHandler($newsId);

            return $result;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Добавляет все отправленные файлы изображений к новости. Общее количество
     * фотографий для пользователя на данный момент не более некоторого количества.
     * Доступ не проверяется.
     *
     * @param $newId
     * @return Response с json массивом типа Status
     */
    public function addImagesHandler($newId)
    {
        include(APP_PATH . '/library/SimpleImage.php');
        $response = new Response();
        if ($this->request->hasFiles()) {
            $files = $this->request->getUploadedFiles();

            $new = News::findFirstByNewsid($newId);

            if (!$new) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный идентификатор новости'],
                        "status" => STATUS_WRONG,
                    ]
                );
                return $response;
            }

            $images = ImagesNews::findByNewsid($newId);
            $countImages = count($images);

            if (($countImages + count($files)) > ImagesNews::MAX_IMAGES) {
                $response->setJsonContent(
                    [
                        "errors" => ['Слишком много изображений для новости. 
                        Можно сохранить для одной новости не более чем ' . ImagesUsers::MAX_IMAGES . ' изображений'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $imagesIds = [];
            $this->db->begin();

            foreach ($files as $file) {

                $newimage = new ImagesNews();
                $newimage->setNewsId($newId);
                $newimage->setImagePath("");

                if (!$newimage->save()) {
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($newimage);
                }

                $imageFormat = pathinfo($file->getName(), PATHINFO_EXTENSION);
                $imageFILEName = $file->getKey();

                if ($imageFILEName == "title") {
                    $imagesIds[] = $imageFILEName;
                    $filename = ImageLoader::formFullImageName('news', $imageFormat, $newId, $imageFILEName);
                } else {
                    $imagesIds[] = $newimage->getImageId();
                    $filename = ImageLoader::formFullImageName('news', $imageFormat, $newId, $newimage->getImageId());
                }
                $newimage->setImagePath($filename);

                if (!$newimage->update()) {
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($newimage);
                }
            }
            $i = 0;
            foreach ($files as $file) {
                $result = ImageLoader::loadNewImage($file->getTempName(), $file->getName(),
                    $newId, $imagesIds[$i]);
                $i++;
                if ($result != ImageLoader::RESULT_ALL_OK || $result === null) {
                    if ($result == ImageLoader::RESULT_ERROR_FORMAT_NOT_SUPPORTED) {
                        $error = 'Формат одного из изображений не поддерживается';
                    } elseif ($result == ImageLoader::RESULT_ERROR_NOT_SAVED) {
                        $error = 'Не удалось сохранить изображение';
                    } else {
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
            }

            $this->db->commit();

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
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
    }

    /**
     * Удаляет картинку из списка изображений новости
     * @access private
     *
     * @method DELETE
     *
     * @param $image - путь к изображению
     *
     * @return string - json array в формате Status - результат операции
     */
    /*public function deleteImageAction($images, $subpath, $newid, $imageName)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $image = ImagesNews::findFirstByImagepath(
                ImageLoader::formFullImagePathFromImageName($subpath, $newid, $imageName));

            if (!$image) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный путь к изображению'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $news = News::findFirstByNewsid($image->getNewsId());

            if (!$news || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $news->getSubjectId(),
                    $news->getSubjectType(), 'editNews')) {
                $response->setJsonContent(
                    [
                        "errors" => ['permission error'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            if (!$image->delete()) {
                $errors = [];
                foreach ($image->getMessages() as $message) {
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

            //$result = ImageLoader::delete($image->getImagePath());
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
    }*/

    /**
     * Удаляет картинку из списка изображений новости
     * @access private
     *
     * @method DELETE
     *
     * @param $newsId id новости
     * @param $imageName название изображения с расширением
     *
     * @return string - json array в формате Status - результат операции
     */
    public function deleteImageByNameAction($newsId, $imageName)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $news = News::findFirstByNewsid($newsId);

            if (!$news || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $news->getSubjectId(),
                    $news->getSubjectType(), 'editNews')) {
                $response->setJsonContent(
                    [
                        "errors" => ['permission error'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $image = ImagesNews::findFirstByImagepath(
                ImageLoader::formFullImagePathFromImageName('news', $newsId, $imageName));

            if (!$image) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверное название изображения'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            if (!$image->delete()) {
                $errors = [];
                foreach ($image->getMessages() as $message) {
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

            //$result = ImageLoader::delete($image->getImagePath());
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
     * Удаляет картинку из списка изображений новости
     * @access private
     *
     * @method DELETE
     *
     * @param $imageId id изображения
     *
     * @return string - json array в формате Status - результат операции
     */
    public function deleteImageByIdAction($imageId)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $image = ImagesNews::findFirstByImageid($imageId);

            if (!$image) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный путь к изображению'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $news = News::findFirstByNewsid($image->getNewsId());

            if (!$news || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $news->getSubjectId(),
                    $news->getSubjectType(), 'editNews')) {
                $response->setJsonContent(
                    [
                        "errors" => ['permission error'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            if (!$image->delete()) {
                $errors = [];
                foreach ($image->getMessages() as $message) {
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

            //$result = ImageLoader::delete($image->getImagePath());
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
