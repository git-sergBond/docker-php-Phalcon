<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use ULogin\Auth;

/**
 * Class SessionAPIController
 * Контроллер, предназначеный для авторизации пользователей и содержащий методы сязанные с этим процессом
 * А именно, методы для авторизации пользователя, разрыва сессии, получение роли текущего пользователя
 * и авторизация через соц. сеть (которая по совместительству и регистрация).
 */
class SessionAPIController extends Controller
{
    public function _registerSession($user)
    {
        $this->session->set(
            "auth",
            [
                "id" => $user->getUserId(),
                "email" => $user->getEmail(),
                "role" => $user->getRole()
            ]
        );
    }

    /**
     * Разрывает сессию пользователя
     * @method POST
     *
     * @return string - json array Status
     */
    public function endAction()
    {
        if ($this->request->isPost()) {
            return $this->destroySession();
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function destroySession()
    {
        $response = new Response();
        $auth = $this->session->get('auth');
        $userId = $auth['id'];

        $tokenRecieved = SecurityPlugin::getTokenFromHeader();
        $token = Accesstokens::findFirst(['userid = :userId: AND token = :token:',
            'bind' => ['userId' => $userId,
                'token' => sha1($tokenRecieved)]]);

        if ($token) {
            $token->delete();
        }

        $this->session->remove('auth');
        $this->session->destroy();
        $response->setJsonContent(
            [
                "status" => STATUS_OK
            ]
        );

        return $response;
    }

    public function createSession($user){
        SupportClass::writeMessageInLogFile('Начало создания сессии для юзера '. $user->getEmail() != null ? $user->getEmail() : $user->phones->getPhone());
        $response = new Response();
        $token = Accesstokens::GenerateToken($user->getUserId(), ($user->getEmail() != null ? $user->getEmail() : $user->phones->getPhone()),
            $this->session->getId());

        $accToken = new Accesstokens();

        SupportClass::writeMessageInLogFile('ID юзера при этом - '. $user->getUserId());
        $accToken->setUserid($user->getUserId());
        $accToken->setToken($token);
        $accToken->setLifetime();

        if ($accToken->save() == false) {
            SupportClass::writeMessageInLogFile('Не смог создать токен по указанной причине');
            $this->session->destroy();
            $errors = [];
            foreach ($accToken->getMessages() as $message) {
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

        $this->_registerSession($user);

        $response->setJsonContent(
            [
                "status" => STATUS_OK,
                'token' => $token,
                'lifetime' => $accToken->getLifetime()
            ]
        );
        return $response;
    }

    /**
     * Выдает текущую роль пользователя.
     * @access public
     * @method POST
     *
     * @return string - json array - [status, role]
     */
    public function getCurrentRoleAction(){
        if ($this->request->isPost()) {
            $response = new Response();
            $auth = $this->session->get('auth');

            if($auth == null){
                $role = ROLE_GUEST;
            } else{
                $userId = $auth['id'];

                $user = Users::findFirstByUserid($userId);

                $role = $user->getRole();
            }

            $response->setJsonContent([
                'status' => STATUS_OK,
                'role' => $role
            ]);

            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Авторизует пользователя в системе
     *
     * @method POST
     * @params login (это может быть его email или номер телефона), password
     * @return string json array [status, allForUser => [user, userinfo, settings], token, lifetime]
     */
    public function indexAction()
    {
        if ($this->request->isPost()) {
            $login = $this->request->getPost("login");
            $password = $this->request->getPost("password");

            //$hash =  $this->security->hash($password);

            // Производим поиск в базе данных
            $var = Phones::formatPhone($login);
            $phone = Phones::findFirstByPhone($var);

            SupportClass::writeMessageInLogFile('var в SessionAPIController '. $var);

            $res = false;
            if ($phone) {
                SupportClass::writeMessageInLogFile('логин это телефон');
                $user = Users::findFirst(
                    [
                        "phoneid = :phoneId: AND issocial=false",
                        "bind" => [
                            "phoneId" => $phone->getPhoneId()
                        ]
                    ]
                );
                if ($user) {
                    SupportClass::writeMessageInLogFile('Юзер найден в бд');
                    $res = $this->security->checkHash($password, $user->getPassword());
                }
            } else {
                SupportClass::writeMessageInLogFile('логин это email');
                $user = Users::findFirst(
                    [
                        "email = :login: AND issocial=false",
                        "bind" => [
                            "login" => $login
                        ]
                    ]
                );
                if ($user) {
                    SupportClass::writeMessageInLogFile('Юзер найден в бд');
                    $res = $this->security->checkHash($password, $user->getPassword());
                }
            }
            // Формируем ответ
            $response = new Response();
            if ($user && $res) {

                $result = $this->createSession($user);

                $result = json_decode($result->getContent(),true);
                $result['role'] = $user->getRole();
                $response->setJsonContent($result);
            } else {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        'errors' => ['Неверные логин или пароль']
                    ]);
            }
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Авторизация через соц. сеть
     * Должен автоматически вызываться компонентом uLogin.
     * 
     * @method GET
     * @return string - json array в формате Status
     */
    public function authWithSocialAction()
    {
        if ($this->request->isGet()) {
            $ulogin = new Auth(array(
                'fields' => 'first_name,last_name,email,phone,sex',
                'url' => '/sessionAPI/authWithSocial',
                'optional' => 'pdate,photo_big,city,country',
                'type' => 'panel',
            ));
            return $ulogin->getForm();
        } else if ($this->request->isPost()) {
            $ulogin = new Auth(array(
                'fields' => 'first_name,last_name,email,phone,sex',
                'url' => '/sessionAPI/authWithSocial',
                'optional' => 'pdate,photo_big,city,country',
                'type' => 'panel',
            ));
            if ($ulogin->isAuthorised()) {
                $response = new Response();
                $ulogin->logout();
                $userSocial = Userssocial::findByIdentity($ulogin->getUser()['network'], $ulogin->getUser()['identity']);

                if (!$userSocial) {

                    //Регистрируем
                    $phone = $ulogin->getUser()['phone'];
                    $email = $ulogin->getUser()['email'];

                    $phoneObj = Phones::findFirstByPhone(Phones::formatPhone($phone));

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

                    $this->db->begin();

                    $user = new Users();

                    if ($phone != null) {
                        //Добавление телефона, если есть
                        $phoneObject = new Phones();
                        $phoneObject->setPhone($phone);

                        if ($phoneObject->save()) {
                            $user->setPhoneId($phoneObject->getPhoneId());
                        }
                    }

                    $user->setEmail($email);
                    $user->setIsSocial(true);
                    $user->setRole("User");

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

                    $userInfo = new Userinfo();
                    $userInfo->setUserId($user->getUserId());
                    $userInfo->setFirstname($ulogin->getUser()['first_name']);
                    $userInfo->setLastname($ulogin->getUser()['last_name']);
                    $userInfo->setMale(($ulogin->getUser()['sex'] - 1) >= 0 ? $ulogin->getUser()['sex'] - 1 : 1);
                    if (isset($ulogin->getUser()['country']) && isset($ulogin->getUser()['city']))
                        $userInfo->setAddress($ulogin->getUser()['country'] . ' ' . $ulogin->getUser()['city']);

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

                    $userSocial = new Userssocial();
                    $userSocial->setUserId($user->getUserId());
                    $userSocial->setNetwork($ulogin->getUser()['network']);
                    $userSocial->setIdentity($ulogin->getUser()['identity']);
                    $userSocial->setProfile($ulogin->getUser()['profile']);

                    if ($userSocial->save() == false) {
                        $this->db->rollback();
                        $errors = [];
                        foreach ($userSocial->getMessages() as $message) {
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

                    $this->SessionAPI->_registerSession($user);

                    $response->setJsonContent(
                        [
                            "status" => STATUS_OK
                        ]
                    );
                    return $response;
                }

                //Авторизуем
                $this->SessionAPI->_registerSession($userSocial->users);

                $response->setJsonContent([
                    'status' => STATUS_OK
                ]);
                return $response;
            } else {
                $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

                throw $exception;
            }
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }
}