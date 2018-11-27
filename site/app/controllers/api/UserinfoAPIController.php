<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;


/**
 * Class UserinfoAPIController
 * Контроллер, который содержит методы для работы в общем с пользователями.
 * Реализует CRUD для пользователей без создания, добавление изображений с привязкой к пользователю.
 *
 * Методы без документации старые и неактуальные, но могут пригодиться в дальнейшем.
 */
class UserinfoAPIController extends Controller
{
    public function indexAction()
    {
        $auth = $this->session->get("auth");
        if ($this->request->isGet()) {
            $response = new Response();
            $userinfo = Userinfo::findFirstByUserid($auth['id']);
            if (!$userinfo) {
                $response->setJsonContent(
                    [
                        "status" => "FAIL"
                    ]);

                return $response;
            }
            $user = Users::findFirstByuserid($auth['id']);
            if (!$user) {
                $response->setJsonContent(
                    [
                        "status" => "FAIL"
                    ]);
                return $response;
            }
            $user_min['email'] = $user->getEmail();
            $user_min['phone'] = $user->getPhone();

            $settings = Settings::findFirstByuserid($auth['id']);
            if (!$settings) {

                $response->setJsonContent(
                    [
                        "status" => "FAIL"
                    ]);

                return $response;
            }
            $info['Userinfo'] = $userinfo;
            $info['user'] = $user_min;
            $info['settings'] = $settings;

            return json_encode($info);
        } else if ($this->request->isPost()) {
            $response = new Response();

            $userId = $auth['id'];
            $userinfo = Userinfo::findFirstByuserid($userId);

            if (!$userinfo) {
                $errors[] = "Пользователь не авторизован";
                $response->setJsonContent(
                    [
                        "errors" => $errors,
                        "status" => "FAIL"
                    ]);

                return $response;
            }

            $userinfo->setFirstname($this->request->getPost("firstname"));
            $userinfo->setPatronymic($this->request->getPost("patronymic"));
            $userinfo->setLastname($this->request->getPost("lastname"));
            $userinfo->setAddress($this->request->getPost("address"));
            $userinfo->setBirthday(date('Y-m-d H:m', strtotime($this->request->getPost("birthday"))));
            $userinfo->setMale($this->request->getPost("male"));

            if (!$userinfo->save()) {
                $errors = [];
                foreach ($userinfo->getMessages() as $message) {
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
                ]);

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function aboutAction()
    {
        $auth = $this->session->get("auth");

        if ($this->request->isPost()) {
            $response = new Response();

            $userId = $auth['id'];
            $userinfo = Userinfo::findFirstByuserid($userId);

            if (!$userinfo) {
                $errors[] = "Пользователь не авторизован";
                $response->setJsonContent(
                    [
                        "errors" => $errors,
                        "status" => STATUS_WRONG
                    ]);

                return $response;
            }

            $userinfo->setAbout($this->request->getPost("about"));

            if (!$userinfo->save()) {

                foreach ($userinfo->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "errors" => $errors,
                        "status" => "WRONG_DATA"
                    ]);

                return $response;
            }
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]);

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function settingsAction()
    {
        $auth = $this->session->get("auth");
        if ($this->request->isPost()) {

            $response = new Response();

            $userId = $auth['id'];
            $settings = Settings::findFirstByuserid($userId);

            if (!$settings) {
                $errors[] = "Пользователь не авторизован";
                $response->setJsonContent(
                    [
                        "errors" => $errors,
                        "status" => "FAIL"
                    ]);

                return $response;
            }
            if (isset($_POST["notificationEmail"]))
                $settings->setNotificationEmail($this->request->getPost("notificationEmail"));
            if (isset($_POST["notificationSms"]))
                $settings->setNotificationSms($this->request->getPost("notificationSms"));
            if (isset($_POST["notificationPush"]))
                $settings->setNotificationPush($this->request->getPost("notificationPush"));

            /*if($settings->getNotificationEmail())
                $settings->setNotificationEmail(1);
            else
                $settings->setNotificationEmail(0);

            if($settings->getNotificationSms())
                $settings->setNotificationSms(1);
            else
                $settings->setNotificationSms(0);

            if($settings->getNotificationPush())
                $settings->setNotificationPush(1);
            else
                $settings->setNotificationPush(0);*/


            if (!$settings->save()) {

                foreach ($settings->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "errors" => $errors,
                        "status" => "WRONG_DATA"
                    ]);

                return $response;
            }
            $response->setJsonContent(
                [
                    "status" => "OK"
                ]);

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function handlerAction()
    {
        $response = new Response();
        include('../library/SimpleImage.php');
// Проверяем установлен ли массив файлов и массив с переданными данными
        if (isset($_FILES) && isset($_FILES['image'])) {
            // echo $_FILES;
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $userinfo = Userinfo::findFirstByuserid($userId);
            if ($userinfo) {
                $userinfo->setUserid($auth['id']);


                if (($_FILES['image']['size'] > 5242880)) {
                    $response->setJsonContent(
                        [
                            "error" => ['Размер файла слишком большой'],
                            "status" => "WRONG_DATA"
                        ]
                    );
                    return $response;
                }
                $image = new SimpleImage();
                $image->load($_FILES['image']['tmp_name']);
                $image->resizeToWidth(200);

                $imageFormat = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $format = $imageFormat;
                if ($imageFormat == 'jpeg' || 'jpg')
                    $imageFormat = IMAGETYPE_JPEG;
                elseif ($imageFormat == 'png')
                    $imageFormat = IMAGETYPE_PNG;
                elseif ($imageFormat == 'gif')
                    $imageFormat = IMAGETYPE_GIF;
                else {
                    $response->setJsonContent(
                        [
                            "error" => ['Данный формат не поддерживается'],
                            "status" => "WRONG_DATA"
                        ]
                    );
                    return $response;
                }
                $filename = $_SERVER['DOCUMENT_ROOT'] . '/public/img/' . hash('crc32', $userinfo->getUserId()) . '.' . $format;
                //if()
                {
                    $image->save($filename, $imageFormat);
                    $imageFullName = str_replace('C:/OpenServer/domains/simpleMod2', '', $filename);
                    $userinfo->setPathToPhoto($imageFullName);
                    $userinfo->save();


                    //return $userinfo->getPathToPhoto();
                    $response->setJsonContent(
                        [
                            'pathToPhoto' => $userinfo->getPathToPhoto(),
                            "status" => "OK"
                        ]
                    );
                    return $response;
                }

            }
            $response->setJsonContent(
                [
                    "status" => "WRONG_DATA"
                ]
            );
            return $response;
        }
        $response->setJsonContent(
            [
                "status" => "WRONG_DATA"
            ]
        );
        return $response;
    }

    /**
     * Устанавливает одну из фотографий пользователя, как основную.
     * @access private
     * @method POST
     * @params imageId
     * @return Response - json array в формате Status.
     */
    public function setPhotoAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $response = new Response();
            $userId = $this->session->get('auth')['id'];
            $image = ImagesUsers::findFirstByImageid($this->request->getPost('imageId'));

            if (!$image || $image->getUserId() != $userId) {
                $response->setJsonContent([
                    'status' => STATUS_WRONG,
                    'errors' => ['Фотография не существует']
                ]);
                return $response;
            }

            $userinfo = Userinfo::findFirstByUserid($userId);

            $userinfo->setPathToPhoto($image->getImagePath());

            if (!$userinfo->update()) {
                return SupportClass::getResponseWithErrors($userinfo);
            }


            $response->setJsonContent([
                'status' => STATUS_OK
            ]);
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /*public function setPhotoAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $response = new Response();
            if ($this->request->hasFiles()) {
                $auth = $this->session->get('auth');
                $userId = $auth['id'];
                $userinfo = Userinfo::findFirstByUserid($userId);

                $file = $this->request->getUploadedFiles();
                $file = $file[0];
                if ($userinfo) {
                    $format = pathinfo($file->getName(), PATHINFO_EXTENSION);

                    $filename = ImageLoader::formFullImageName('users', $format, $userId, 0);
                    $userinfo->setPathToPhoto($filename);

                    if (!$userinfo->update()) {
                        $errors = [];
                        foreach ($userinfo->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $response->setJsonContent(
                            [
                                "errors" => $errors,
                                "status" => STATUS_WRONG
                            ]);

                        return $response;
                    }

                    ImageLoader::loadUserPhoto($file->getTempName(), $file->getName(),110, $userId);

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
                        "errors" => ['Пользователя не существует (или он не активирован)']
                    ]
                );
                return $response;
            }
            $response->setJsonContent(
                [
                    "status" => STATUS_WRONG,
                    "errors" => ['Файл не отправлен']
                ]
            );
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }*/

    /**
     * Удаляет пользователя
     *
     * @method DELETE
     *
     * @param $userId
     *
     * @return string - json array - объект Status - результат операции
     */
    public function deleteUserAction($userId)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $currentUserId = $auth['id'];
            $response = new Response();

            $user = Users::findFirstByUserid($userId);

            if (!$user || !SubjectsWithNotDeleted::checkUserHavePermission($currentUserId, $userId, 0, 'deleteUser')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$user->delete()) {
                $errors = [];
                foreach ($user->getMessages() as $message) {
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

    /**
     * Восстанавливает пользователя
     *
     * @method POST
     *
     * @param userId
     *
     * @return string - json array - объект Status - результат операции
     */
    public function restoreUserAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $user = Users::findFirst(['userid = :userId:',
                'bind' => ['userId' => $this->request->getPost('userId')]], false);

            if (!$user || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $user->getUserId(), 0, 'restoreCompany')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$user->restore()) {
                $errors = [];
                foreach ($user->getMessages() as $message) {
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

    /**
     * Возвращает публичные данные о пользователе.
     * Публичный метод.
     *
     * @method GET
     *
     * @param $userid
     *
     * @return string - json array [userinfo, [phones], [images], countNews, countSubscribers,
     *          countSubscriptions];
     */
    public function getUserinfoAction($userid = null)
    {
        if ($this->request->isGet()) {
            $response = new Response();

            if ($userid == null) {
                $auth = $this->session->get('auth');
                if ($auth != null) {
                    $userid = $auth['id'];
                } else{
                    $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
                    throw $exception;
                }
            }

            $userinfo = Userinfo::findFirstByUserid($userid);


            if (!$userinfo) {

                $response->setJsonContent(
                    [
                        'status' => STATUS_WRONG,
                        'errors' => ['Пользователь с таким id не существует']
                    ]);

                return $response;
            }


            $phones = PhonesUsers::getUserPhones($userid);
            $images = ImagesUsers::findByUserid($userid);

            $countNews = count(News::findBySubject($userid, 0));
            $countSubscribers = count(FavoriteUsers::findByUserobject($userid));
            $countSubscriptions = count(FavoriteUsers::findByUsersubject($userid)) + count(FavoriteCompanies::findByUserid($userid));

            $response->setJsonContent([
                'userinfo' => $userinfo,
                'phones' => $phones,
                'images' => $images,
                'countNews' => $countNews,
                'countSubscribers' => $countSubscribers,
                'countSubscriptions' => $countSubscriptions,
            ]);

            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Меняет данные текущего пользоваателя.
     * Приватный метод.
     *
     * @method PUT
     *
     * @params firstname
     * @params lastname
     * @params patronymic
     * @params birthday
     * @params male
     * @params status
     * @params about
     * @params address
     *
     * @return string - json array - результат операции
     */
    public function editUserinfoAction()
    {
        if ($this->request->isPut()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $response = new Response();

            $userinfo = Userinfo::findFirstByUserid($userId);
            if (!$userinfo) {
                $response->setJsonContent(
                    [
                        'status' => STATUS_WRONG,
                        'errors' => ['Пользователь с таким id не существует']
                    ]);
                return $response;
            }

            if ($this->request->getPut('firstname'))
                $userinfo->setFirstname($this->request->getPut('firstname'));
            if ($this->request->getPut('lastname'))
                $userinfo->setLastname($this->request->getPut('lastname'));
            if ($this->request->getPut('patronymic'))
                $userinfo->setPatronymic($this->request->getPut('patronymic'));
            if ($this->request->getPut('address'))
                $userinfo->setAddress($this->request->getPut("address"));
            if ($this->request->getPut('birthday'))
                $userinfo->setBirthday(date('Y-m-d H:m', strtotime($this->request->getPut("birthday"))));
            if ($this->request->getPut('male'))
                $userinfo->setMale($this->request->getPut("male"));

            if ($this->request->getPut('status'))
                $userinfo->setStatus($this->request->getPut("status"));
            if ($this->request->getPut('about'))
                $userinfo->setAbout($this->request->getPut("about"));

            if (!$userinfo->update()) {
                $errors = [];
                foreach ($userinfo->getMessages() as $message) {
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

            $response->setJsonContent([
                'userinfo' => $userinfo,
            ]);

            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Добавляет все прикрепленные изображения к пользователю. Но суммарно изображений не больше 10.
     *
     * @access private
     *
     * @method POST
     *
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

            $result = $this->addImagesHandler($userId);

            return $result;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Добавляет все отправленные файлы изображений к пользователю. Общее количество
     * фотографий для пользователя на данный момент не более 10.
     * Доступ не проверяется.
     *
     * @param $userId
     * @return Response с json массивом типа Status
     */
    public function addImagesHandler($userId)
    {
        include(APP_PATH . '/library/SimpleImage.php');
        $response = new Response();
        if ($this->request->hasFiles()) {
            $files = $this->request->getUploadedFiles();

            $user = Users::findFirstByUserid($userId);

            if (!$user) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный идентификатор пользователя'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $images = ImagesUsers::findByUserid($userId);
            $countImages = count($images);

            if (($countImages + count($files)) > ImagesUsers::MAX_IMAGES) {
                $response->setJsonContent(
                    [
                        "errors" => ['Слишком много изображений для пользователя. 
                        Можно сохранить для одного пользователя не более чем ' . ImagesUsers::MAX_IMAGES . ' изображений'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $imagesIds = [];
            $this->db->begin();

            foreach ($files as $file) {

                $newimage = new ImagesUsers();
                $newimage->setUserId($userId);
                $newimage->setImagePath("");

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

                $filename = ImageLoader::formFullImageName('users', $imageFormat, $userId, $newimage->getImageId());

                $newimage->setImagePath($filename);

                if (!$newimage->update()) {
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($newimage);
                }
            }
            $i = 0;
            foreach ($files as $file) {
                $result = ImageLoader::loadUserPhoto($file->getTempName(), $file->getName(),
                    $userId, $imagesIds[$i]);
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

    public function addUsersAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {

            $response = new Response();

            $users = [];

            //юзеры
            $names = ['Родион', 'Всеслав', 'Никита', 'Бен', 'Ярополк', 'Абдула', 'Василиса'];
            $males = [1, 1, 1, 1, 1, 1, 0];
            $lastnames1 = ['Мраков', 'Стебль', 'Ладан', 'Маринов', 'Зрачков'];
            $lastnames0 = ['Мракова', 'Стебль', 'Ладан', 'Маринова', 'Зрачкова'];
            $emailsName = ['mrak', 'bigbranch', 'lastpoint', 'stronghunger', 'anyname',
                'littlemouse', 'stella', 'alldarkness'];

            $emailsPost = ['mail.ru', 'mail.com', 'yandex.ru', 'gmail.com', 'outlook.com'];
            $count = 10;

            $longhigh = 36.785256139080154;
            $longbottom = 37.73694681290828 - ($longhigh - 37.73694681290828);
            $latright = 55.23724689239517;
            $latleft = 55.748696337268484 - ($latright - 55.748696337268484);

            $diffLong = ($longhigh - $longbottom) / 1000;
            $diffLat = ($latright - $latleft) / 1000;

            for ($i = 0; $i < $count; $i++) {
                $pos = rand(0, count($names) - 1);
                $user['firstname'] = $names[$pos];
                $user['male'] = $males[$pos];
                if ($user['male'] == 0) {
                    $user['lastname'] = $lastnames0[rand(0, count($lastnames0) - 1)];
                } else {
                    $user['lastname'] = $lastnames1[rand(0, count($lastnames1) - 1)];
                }
                do {
                    $user['email'] = $emailsName[rand(0, count($emailsName) - 1)] . '@' .
                        $emailsPost[rand(0, count($emailsPost) - 1)];
                } while (Users::findFirstByEmail($user['email']));

                $user['password'] = '12345678';

                $user['latitude'] = $latleft + rand(0, 1000) * $diffLat;
                $user['longitude'] = $longbottom + rand(0, 1000) * $diffLong;
                $users[] = $user;
            }

            $this->db->begin();
            foreach ($users as $userArr) {
                $user = new Users();
                $user->setActivated(true);
                $user->setEmail($userArr['email']);
                $user->setPassword($userArr['password']);
                $user->setRole(ROLE_GUEST);

                if (!$user->save()) {
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($user);
                }

                $userinfo = new Userinfo();
                $userinfo->setUserId($user->getUserId());
                $userinfo->setFirstname($userArr['firstname']);
                $userinfo->setLastname($userArr['lastname']);
                $userinfo->setMale($userArr['male']);

                if (!$userinfo->save()) {
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($userinfo);
                }

                $userlocation = new UserLocation();
                $userlocation->setUserId($userinfo->getUserId());
                $userlocation->setLastTime('2019-09-08 16:00:30+00');
                $userlocation->setLatitude($userArr['latitude']);
                $userlocation->setLongitude($userArr['longitude']);

                if (!$userlocation->save()) {
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($userlocation);
                }
            }
            $this->db->commit();
            $response->setJsonContent(['status' => STATUS_OK]);

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }
}