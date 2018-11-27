<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 02.05.2018
 * Time: 11:45
 */

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Paginator\Adapter\NativeArray as Paginator;

/**
 * Class ReviewsAPIController
 * Контроллер для работы с отзывами.
 * Реализует CRUD для отывов, методы для добавления изображений к отзыву.
 */
class ReviewsAPIController extends Controller
{
    /**
     * Добавляет отзыв.
     *
     * @method POST
     *
     * @params int binderId, int binderType, bool executor, int rating
     * @params (Необязатальные) textReview, fake.
     *
     * @return Response - Status
     */
    public function addReviewAction()
    {
        if ($this->request->isPost()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $response = new Response();
            $binderId = $this->request->getPost('binderId');
            $binderType = $this->request->getPost('binderType');
            $executor = $this->request->getPost('executor');

            if (!Binders::checkUserHavePermission($userId, $binderId,
                $binderType, $executor, 'addReview')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $review = Reviews::findFirst(['binderid = :binderId: AND bindertype = :binderType: AND executor = :executor:',
                'bind' => ['binderId' => $binderId, 'binderType' => $binderType, 'executor' => $executor]]);

            if ($review) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Отзыв в связи с заказом уже написан']
                    ]
                );
                return $response;
            }

            if ($binderType == 'task') {
                $binder = Tasks::findFirstByTaskid($binderId);
            } elseif ($binderType == 'request')
                $binder = Requests::findFirstByRequestid($binderId);

            if (!($binder->getStatus() == STATUS_CANCELED ||
                $binder->getStatus() == STATUS_NOT_EXECUTED ||
                $binder->getStatus() == STATUS_REJECTED_BY_SYSTEM ||
                $binder->getStatus() == STATUS_PAID_EXECUTOR ||
                $binder->getStatus() == STATUS_PAID_BY_SECURE_TRANSACTION)) {

                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нельзя писать отзыв на данном этапе']
                    ]
                );
                return $response;
            }

            $review = new Reviews();

            $review->setBinderId($binderId);
            $review->setBinderType($binderType);
            $review->setExecutor($executor);
            $review->setTextReview($this->request->getPost('textReview'));
            $review->setReviewDate(date('Y-m-d H:i:s'));
            $review->setUserId($userId);

            /*if($this->request->getPost('fake'))
                $review->setFake($this->request->getPost('fake'));*/

            $review->setRating($this->request->getPost('rating'));

            if (!$review->save()) {
                $errors = [];
                foreach ($review->getMessages() as $message) {
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
     * Редактирует отзыв.
     *
     * @method PUT
     *
     * @params int rating, reviewId
     * @param (Необязатальные) textReview.
     *
     * @return Response - Status
     */
    public function editReviewAction()
    {
        if ($this->request->isPut()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $review = Reviews::findFirstByReviewid($this->request->getPut('reviewId'));

            $response = new Response();

            if (!$review) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Отзыв не существует']
                    ]
                );
                return $response;
            }

            if (!Binders::checkUserHavePermission($userId, $review->getBinderId(),
                $review->getBinderType(), $review->getExecutor(), 'editReview')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $review->setTextReview($this->request->getPut('textReview'));
            $review->setReviewDate(date('Y-m-d H:i:s'));
            $review->setRating($this->request->getPut('rating'));
            $review->setUserId($userId);

            if (!$review->update()) {
                $errors = [];
                foreach ($review->getMessages() as $message) {
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
     * Удаляет отзыв.
     *
     * @method DELETE
     *
     * @param $reviewId
     *
     * @return Response - Status
     */
    public function deleteReviewAction($reviewId)
    {
        if ($this->request->isDelete()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $review = Reviews::findFirstByReviewid($reviewId);

            $response = new Response();

            if (!$review) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Отзыв не существует']
                    ]
                );
                return $response;
            }

            if ($review->getFake() == false) {
                if (!Binders::checkUserHavePermission($userId, $review->getBinderId(),
                    $review->getBinderType(), $review->getExecutor(), 'deleteReview')) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }
            } else {
                if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $review->getSubjectId(),
                    $review->getSubjectType(), 'deleteReview')) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }
            }

            if (!$review->delete()) {
                $errors = [];
                foreach ($review->getMessages() as $message) {
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
     * Возвращает отзывы об указанном субъекте, будь то пользователь или компания.
     *
     * @method GET
     *
     * @param $subjectId - id субъекта
     * @param $subjectType - тип субъекта
     *
     * @return string - json array [status,[reviews]]
     */
    public function getReviewsForSubjectAction($subjectId, $subjectType)
    {
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $reviews = Reviews::getReviewsForObject($subjectId, $subjectType);

            /*$i = count($reviews);
            $comp = Companies::findFirstByCompanyid($subjectId);
            $rating = $comp->getRatingExecutor();
            $rating2 = ($rating*($i+1)-2.5)/$i;*/
            $response->setJsonContent(
                [
                    "status" => STATUS_OK,
                    "reviews" => $reviews
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Возвращает отзывы, связанные с указанной услугой.
     *
     * @method GET
     *
     * @param $serviceId - id услуги
     * @param $numPage - номер страницы
     * @param $widthPage - размер страницы
     *
     * @return string - json array [status,reviews => [review,{userinfo or company}]]
     */
    public function getReviewsForServiceAction($serviceId, $numPage, $widthPage)
    {
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $reviews = Reviews::getReviewsForService2($serviceId);

            $reviews2_ar = [];
            foreach ($reviews as $review) {
                $reviews2['review'] = json_decode($review['review'], true);

                unset($reviews2['review']['deleted']);
                unset($reviews2['review']['deletedcascade']);
                unset($reviews2['review']['fake']);
                unset($reviews2['review']['subjectid']);
                unset($reviews2['review']['subjecttype']);
                unset($reviews2['review']['objectid']);
                unset($reviews2['review']['objecttype']);

                unset($reviews2['review']['userid']);
                $subject = json_decode($review['subject'], true);
                if (isset($subject['reviewid'])) {
                    //$reviews2['review']['username'] = $reviews2['review']['fakename'];
                    $userinfo = new Userinfo();
                    $userinfo->setFirstname($reviews2['review']['fakename']);
                    $reviews2['userinfo'] = $userinfo;
                } else if (isset($subject['companyid'])) {
                    $reviews2['company'] = $subject;
                    unset($reviews2['company']['deleted']);
                    unset($reviews2['company']['deletedcascade']);
                    unset($reviews2['company']['ismaster']);
                    unset($reviews2['company']['yandexMapPages']);

                } else {
                    $reviews2['userinfo'] = $subject;
                }
                unset($reviews2['review']['fakename']);

                $reviews2_ar[] = $reviews2;
            }

            $paginator = new Paginator([
                'data' => $reviews2_ar,
                'limit' => $widthPage,
                'page' => $numPage
            ]);

            $response->setJsonContent(
                [
                    "status" => STATUS_OK,
                    "reviews" => $paginator->getPaginate()->items
                ]
            );
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Добавляет все прикрепленные изображения к отзыву. Но суммарно изображений для отзыва не больше 3.
     *
     * @access private
     *
     * @method POST
     *
     * @params (обязательно) reviewId
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

            $review = Reviews::findFirstByReviewid($this->request->getPost('reviewId'));

            if (!$review) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный идентификатор услуги'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            if (!Binders::checkUserHavePermission($userId, $review->getBinderId(),$review->getBinderType(),
                $review->getExecutor(),'editReview')) {
                $response->setJsonContent(
                    [
                        "errors" => ['permission error'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $result = $this->addImagesHandler($review->getReviewId());

            return $result;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Добавляет все отправленные файлы изображений к отзыву. Общее количество
     * фотографий для одного отзыва на данный момент не более 3.
     * Доступ не проверяется.
     *
     * @param $reviewId
     * @return Response с json массивом типа Status
     */
    public function addImagesHandler($reviewId)
    {
        include(APP_PATH . '/library/SimpleImage.php');
        $response = new Response();
        if ($this->request->hasFiles()) {
            $files = $this->request->getUploadedFiles();

            $review = Reviews::findFirstByReviewid($reviewId);

            if (!$review) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный идентификатор отзыва'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $images = ImagesReviews::findByReviewid($reviewId);
            $countImages = count($images);

            if(($countImages + count($files)) > ImagesReviews::MAX_IMAGES ){
                $response->setJsonContent(
                    [
                        "errors" => ['Слишком много изображений для отзыва. 
                        Можно сохранить для одного отзыва не более чем '.ImagesReviews::MAX_IMAGES.' изображений'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }
            $this->db->begin();
            $imagesIds = [];
            foreach ($files as $file) {
                $newimage = new ImagesReviews();
                $newimage->setReviewId($reviewId);
                $newimage->setImagePath(' ');

                if (!$newimage->save()) {
                    $errors = [];
                    $this->db->rollback();
                    foreach ($newimage->getMessages() as $message) {
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

                $imagesIds[] = $newimage->getImageId();
                $imageFormat = pathinfo($file->getName(), PATHINFO_EXTENSION);

                $filename = ImageLoader::formFullImageName('reviews', $imageFormat, $reviewId, $newimage->getImageId());

                $newimage->setImagePath($filename);

                if(!$newimage->update()){
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($newimage);
                }
            }
            $i = 0;
            foreach ($files as $file) {
                $result = ImageLoader::loadReviewImage($file->getTempName(), $file->getName(), $reviewId,$imagesIds[$i]);
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
     *
     */
    public function addTypeAction()
    {
        $query = $this->db->prepare("ALTER TYPE bindertype AS ENUM ('task', 'request',);");

        return $query->execute();
    }

}