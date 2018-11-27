<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

/**
 * Class ContactDetailsCompanyAPIController
 * Устаревший контроллер для сущности ContactDetailsCompany.
 */
class ContactDetailsCompanyAPIController extends Controller
{
    public function getContactDetailsAction($companyId)
    {
        if ($this->request->isGet() && $this->session->get('auth')) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $contactDetails = ContactDetailsCompany::findByCompanyId($companyId);

            return json_encode($contactDetails);

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function addContactDetailsAction(){
        if ($this->request->isPost()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $contact = ContactDetailsCompany::findFirstByCompanyId($this->request->getPost("companyId"));
            $company = Companies::findFirstByCompanyId($this->request->getPost("companyId"));

            if(!$contact && $company->getUserId() == $userId) {

                $contact = new ContactDetailsCompany();
                $contact->setCompanyId($this->request->getPost("companyId"));
                $contact->setEmail($this->request->getPost("email"));
                $contact->setFax($this->request->getPost("fax"));

                $webSite =$this->request->getPost("webSite");
                //Должно проверять правильность ввода URL
                $reg = '~^(?:(?:https?|ftp|telnet)://(?:[a-z0-9_-]{1,32}(?::[a-z0-9_-]{1,32})?@)?)?(?:(?:[a-z0-9-]{1,128}\.)+(?:ru|su|com|net|org|mil|edu|arpa|gov|biz|info|aero|inc|name|[a-z]{2})|(?!0)(?:(?!0[^.]|255)[0-9]{1,3}\.){3}(?!0|255)[0-9]{1,3})(?:/[a-z0-9.,_@%&?+=\~/-]*)?(?:#[^ \'\"&]*)?$~i';

                if(trim($webSite) != "" && !preg_match($reg,$webSite))
                {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['Неверно указан веб-сайт']
                        ]
                    );
                    return $response;
                }
                $contact->setWebSite($this->request->getPost("webSite"));



                $this->db->begin();

                if (!$contact->save()) {

                    $this->db->rollback();
                    $errors = [];
                    foreach ($contact->getMessages() as $message) {
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

                $result = $this->PhonesAPI->addPhonesAction();

                $result = json_decode($result->getContent());

                if ($result->status != STATUS_OK) {
                    $this->db->rollback();
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => $result->errors
                        ]
                    );
                    return $response;
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
                    'status' => STATUS_WRONG,
                    'errors' => ['permission error']
                ]
            );

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function editContactDetailsAction(){
        if ($this->request->isPut()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $contact = ContactDetailsCompany::findFirstByCompanyId($this->request->getPost("companyId"));
            $company = Companies::findFirstByCompanyId($this->request->getPost("companyId"));

            if($contact && $company->getUserId() == $userId) {

                $contact = ContactDetailsCompany::findFirstByCompanyId($company->getCompanyId());
                $contact->setEmail($this->request->getPost("email"));
                $contact->setFax($this->request->getPost("fax"));
                $contact->setWebSite($this->request->getPost("webSite"));

                if (!$contact->save()) {

                    $errors = [];
                    foreach ($contact->getMessages() as $message) {
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
            }
            $response->setJsonContent(
                [
                    'status' => STATUS_WRONG,
                    'errors' => ['permission error']
                ]
            );

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function deleteContactDetailsAction($companyId)
    {
        if ($this->request->isDelete()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();

            $contactDetails = ContactDetailsCompany::findFirstByCompanyId($companyId);
            $company = Companies::findFirstByCompanyId($companyId);

            if ($contactDetails && $company->getUserId() == $userId) {
                if (!$company->delete()) {
                    $errors = [];
                    foreach ($company->getMessages() as $message) {
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
}
