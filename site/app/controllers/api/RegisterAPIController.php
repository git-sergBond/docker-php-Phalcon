<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

/**
 * Class RegisterAPIController
 * Контроллер для регистрации пользователей.
 * Содержит методы для регистрации пользователя и работы с активационным кодом.
 * На данный момент это касается только активационного кода через email.
 */
class RegisterAPIController extends Controller
{
    /**
     * Регистрирует пользователя в системе
     *
     * @method POST
     *
     * @params login, password,
     *
     * @return string json array. Если все прошло успешно - [status, token, lifetime (время, после которого токен будет недействительным)],
     * иначе [status,errors => <массив сообщений об ошибках>]
     */
    public function indexAction()
    {
        SupportClass::writeMessageInLogFile("Зашел в RegisterAPI");
        /*if ($this->request->isPost() || $this->request->isOptions()) {*/
            $response = new Response();

            SupportClass::writeMessageInLogFile("Прошел проверку на метод");

            $phone = $this->request->getPost('login');
            $email = $this->request->getPost('login');
            $password = $this->request->getPost('password');
            $formatPhone = Phones::formatPhone($phone);
            $phoneObj = Phones::findFirstByPhone($formatPhone);

            $user = Users::findFirst(
                [
                    "(email = :email: OR phoneid = :phoneId:)",
                    "bind" => [
                        "email" => $email,
                        "phoneId" => $phoneObj ? $phoneObj->getPhoneId() : null
                    ]
                ]
            );

            if ($user != false) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_ALREADY_EXISTS,
                        'errors' => ['Пользователь с таким телефоном/email-ом уже зарегистрирован']
                    ]
                );
                return $response;
            }

            SupportClass::writeMessageInLogFile("Проверил юзера");

            $this->db->begin();

            $user = new Users();

            if (Phones::isValidPhone($formatPhone)) {
                $phoneObject = new Phones();
                $phoneObject->setPhone($formatPhone);

                if ($phoneObject->save() == false) {
                    $this->db->rollback();
                    $errors = [];
                    foreach ($phoneObject->getMessages() as $message) {
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
                $user->setPhoneId($phoneObject->getPhoneId());
            } else {
                $user->setEmail($email);
            }

            $user->setPassword($password);
            $user->setRole(ROLE_GUEST);
            $user->setIsSocial(false);
            $user->setActivated(false);

            if ($user->save() == false) {
                $this->db->rollback();
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

            SupportClass::writeMessageInLogFile("Дошел до создания сессии");
            $tokens = $this->SessionAPI->createSession($user);

            $tokens = json_decode($tokens->getContent(), true);

            SupportClass::writeMessageInLogFile("Дошел до отправки кода активации");
            $res = $this->sendActivationCode($user);
            SupportClass::writeMessageInLogFile("Отправил код активации");
            $res = json_decode($res->getContent(),true);

            $res2 = $res['status'] == STATUS_OK;
            $tokens['role'] = $user->getRole();
            if($res2 === true){
                $this->db->commit();

                $response->setJsonContent(
                    $tokens
                );
                return $response;
            } else {
                $response->setJsonContent(
                    $res
                );
                return $response;
            }

        /*} else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }*/
    }

    /**
     * Активирует пользователя.
     *
     * @access defective
     *
     * @method POST
     *
     * @params (обязательные) firstname, lastname, male
     * @params (Необязательные) patronymic, birthday, about (много текста о себе),
     * @return string - json array Status
     */
    public function confirmAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $user = Users::findFirst(['userid = :userId:', 'bind' =>
                [
                    'userId' => $userId
                ]
            ]);

            if (!$user) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_UNRESOLVED_ERROR,
                        "errors" => ['Пользователь не создан']
                    ]
                );
                return $response;
            }

            if ($user->getActivated()) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Пользователь уже активирован']
                    ]
                );
                return $response;
            }

            $activationCode = ActivationCodes::findFirstByUserid($user->getUserId());

            if (!$activationCode || (strtotime(time() - $activationCode->getTime()) > 3600)) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Неправильный активационный код']
                    ]
                );
                return $response;
            }

            $this->db->begin();

            $userInfo = new Userinfo();
            $userInfo->setUserId($userId);
            $userInfo->setFirstname($this->request->getPost('firstname'));
            $userInfo->setLastname($this->request->getPost('lastname'));
            $userInfo->setMale($this->request->getPost('male'));

            if ($userInfo->save() == false) {
                $this->db->rollback();
                $errors = [];
                foreach ($userInfo->getMessages() as $message) {
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

            $setting = new Settings();
            $setting->setUserId($user->getUserId());

            if ($setting->save() == false) {
                $this->db->rollback();
                $errors = [];
                foreach ($setting->getMessages() as $message) {
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

            $user->setRole('User');

            if ($user->update() == false) {
                $this->db->rollback();
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

            $this->db->commit();

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
     * Подтверждает, что пользователь - владелец (пока только) почты.
     *
     * @access guest
     *
     * @method POST
     *
     * @params activationCode, login
     *
     * @return Status
     */
    public function activateLinkAction(){
        if ($this->request->isPost()) {

            $auth = $this->session->get('auth');
            $authUserId = null;
            if($auth)
                $authUserId = $auth['id'];

            $response = new Response();

            if($authUserId == null) {
                $user = Users::findFirst(['email = :email:', 'bind' =>
                    [
                        'email' => $this->request->getPost('login')
                    ]
                ]);
            } else{
                $user = Users::findFirst(['userid = :userId: and email = :email:', 'bind' =>
                    [
                        'userid' => $authUserId,
                        'email' => $this->request->getPost('login')
                    ]
                ]);
            }

            if (!$user) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_UNRESOLVED_ERROR,
                        "errors" => ['Пользователь не создан']
                    ]
                );
                return $response;
            }

            if ($user->getActivated()) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Пользователь уже активирован']
                    ]
                );
                return $response;
            }

            $activationCode = ActivationCodes::findFirstByUserid($user->getUserId());

            if (!$activationCode || (strtotime(time() - $activationCode->getTime()) > 3600)) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Неправильный активационный код']
                    ]
                );
                return $response;
            }

            if ($activationCode->getActivation() != $this->request->getPost('activationCode')) {
                if ($activationCode->getDeactivation() != $this->request->getPost('activationCode')) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['Неправильный активационный код']
                        ]
                    );
                    return $response;
                } else {
                    if ($user->delete() == false) {
                        $this->db->rollback();
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

                    if ($this->session->get('auth') != null) {
                        $this->SessionAPI->destroySession();
                    }

                    $response->setJsonContent(
                        [
                            "status" => STATUS_OK
                        ]
                    );
                    return $response;
                }
            }

            $this->db->begin();

            if(!$activationCode->delete()){
                $this->db->rollback();
                return SupportClass::getResponseWithErrors($activationCode);
            }

            $user->setRole(ROLE_USER_DEFECTIVE);

            if ($user->update() == false) {
                $this->db->rollback();
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

            $this->db->commit();

            if($authUserId == null) {
                $res = $this->SessionAPI->createSession($user);
                $res = json_decode($res->getContent(), true);

                $response->setJsonContent(
                    $res
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
     * Деактивирует ссылку и частично активирует пользователя, давая ему немного прав.
     * При необходимости авторизует пользователя.
     *
     * @access public
     *
     * @method POST
     *
     * @params email
     * @params activationCode
     * @return Response
     */
    public function deactivateLinkAction()
    {
        if ($this->request->isPost()) {
            $response = new Response();

            $user = Users::findFirstByEmail($this->request->getPost('email'));

            if (!$user) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_UNRESOLVED_ERROR,
                        "errors" => ['Пользователь не создан']
                    ]
                );

                return $response;
            }

            if ($user->getActivated()) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Пользователь уже активирован']
                    ]
                );
                return $response;
            }

            $activationCode = ActivationCodes::findFirstByUserid($user->getUserId());

            if (!$activationCode || $activationCode->getUsed() ||
                (strtotime($activationCode->getTime()) - time() > 3600)) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Неправильный или просроченный активационный код']
                    ]
                );
                return $response;
            }

            $this->db->begin();

            if ($activationCode->getActivation() != $this->request->getPost('activationCode')) {
                if ($activationCode->getDeactivation() != $this->request->getPost('activationCode')) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['Неправильный активационный код']
                        ]
                    );
                    return $response;
                } else {
                    if ($user->delete() == false) {
                        $this->db->rollback();
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

                    if ($this->session->get('auth') != null) {
                        $this->SessionAPI->destroySession();
                    }

                    $response->setJsonContent(
                        [
                            "status" => STATUS_OK
                        ]
                    );
                    return $response;
                }
            }
            //Нормальный активационный ключ

            $user->setRole(ROLE_USER_DEFECTIVE);
            if ($user->update() == false) {
                $this->db->rollback();
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

            $result = ["status" => STATUS_OK];
            if ($this->session->get('auth') == null) {
                $result = $this->SessionAPI->createSession($user);
            }

            $activationCode->setUsed(false);

            if (!$activationCode->update()) {
                $this->db->rollback();
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

            $this->db->commit();

            $response->setJsonContent($result);

            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Отправляет активационный код пользователю на почту.
     * @param $user - объект типа User
     * @return Response - json array в формате Status
     */
    public function sendActivationCode($user)
    {
        $response = new Response();
        $auth = $this->session->get('auth');
        $userId = $auth['id'];

        SupportClass::writeMessageInLogFile('all ok with SupportClass');
        $aapt = new AaaaPt(0);

        if (!$user || $user == null) {
            $response->setJsonContent(
                [
                    "status" => STATUS_WRONG,
                    "errors" => ['Пользователь не существует']
                ]
            );
            return $response;
        }

        if ($user->getActivated()) {
            $response->setJsonContent(
                [
                    "status" => STATUS_WRONG,
                    "errors" => ['Пользователь уже активирован']
                ]
            );
            return $response;
        }

        if ($user->getEmail() != null) {
            $activationCode = ActivationCodes::findFirstByUserid($userId);

            if (!$activationCode) {
                $activationCode = new ActivationCodes();
                $activationCode->setUserId($userId);
            }

            $activationCode->setActivation($user->generateActivation());
            $activationCode->setDeactivation($user->generateDeactivation());
            $activationCode->setTime(date('Y-m-d H:i:s'));

            if (!$activationCode->save()) {
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

            //Отправляем письмо.
            $mailer = new PHPMailerApp($this->config['mail']);
            $newTo = $this->config['mail']['from']['email'];

            $res = $mailer->createMessageFromView('emails/hello_world', 'hello_world',
                ['activation' => $activationCode->getActivation(),
                    'deactivation' => $activationCode->getDeactivation(),
                    'email'=>$user->getEmail()])
                ->to(/*$user->getEmail()*/$newTo)
                ->subject('Подтвердить регистрацию в нашем замечательном сервисе.')
                ->send();

            if ($res === true) {
                $response->setJsonContent([
                    'status' => STATUS_OK
                ]);
                return $response;
            } else {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => [$res],
                    ]
                );
                return $response;
            }
        }

        $response->setJsonContent(
            [
                "status" => STATUS_WRONG,
                "errors" => ['Активация через sms пока не предусмотрена'],
            ]
        );
        return $response;
    }

    /**
     * Отправляет активационный код пользователю. Пока только на почту.
     * @access defective
     * @method POST
     *
     * @return Response - json array в формате Status
     */
    public function getActivationCodeAction(){
        if ($this->request->isPost() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $user = Users::findFirstByUserid($userId);

            return $this->sendActivationCode($user);
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}

