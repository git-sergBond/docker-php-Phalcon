<?php

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

/**
 * Class CoordinationAPIController
 * Устаревший контроллер, еще с диплома, который был предназначен для всего,
 * что касается чата и взаимодействия заказчика и исполнителя после того,
 * как заказчик выберет себе исполнителя. Содержит потенциально полезный код.
 */
class CoordinationAPIController extends Controller
{

    /**
     * Creates a new message
     */
    public function addMessageAction()
    {
        if ($this->request->isPut()) {
            $response = new Response();

            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $auctionId = $this->request->getPut('tenderId');
            $auction = Auctions::findFirstByAuctionId($auctionId);

            $offer = Offers::findFirst("userId = $userId and auctionId = $auctionId");
            if ($userId == $auction->tasks->getUserId()) {
                $input = 0;
            } else if ($offer != null && $offer->getSelected() == 1)
                $input = 1;
            else {
                //Вообще не имеет отношения к этому заданию
                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA"
                    ]
                );
                return $response;
            }


            $message = new Messages();
            $message->setAuctionid($auctionId);
            $message->setInput($input);
            $message->setMessage($this->request->getPut("message"));
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

            //$this->sendPush($message);

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

    public function getMessagesAction($auctionId, $date = null)
    {
        if ($this->request->isGet()) {

            $response = new Response();

            //$auctionId = $this->request->getPost('auctionId');

            $task = Auctions::findFirstByAuctionId($auctionId)->tasks;


            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //$auctionId = $this->request->getPut('auctionId');
            $auction = Auctions::findFirstByAuctionId($auctionId);

            $offer = Offers::findFirst("userId = $userId and auctionId = $auctionId");
            if ($userId == $auction->tasks->getUserId()) {
                $review = Reviews::findFirst(["auctionId =:auctionId: and executor = :executor:",
                        'bind' => [
                            'auctionId' => $auction->getAuctionId(),
                            'executor' => 1
                        ]
                    ]
                );
                $input = 0;
            } else if ($offer != null && $offer->getSelected() == 1) {
                $review = Reviews::findFirst(["auctionId =:auctionId: and executor = :executor:",
                        'bind' => [
                            'auctionId' => $auction->getAuctionId(),
                            'executor' => 0
                        ]
                    ]
                );
                $input = 1;
            } else {
                //Вообще не имеет отношения к этому заданию
                $response->setJsonContent(
                    [
                        "status" => ['status' => "WRONG_DATA"]
                    ]
                );
                return $response;
            }

            if($date == null){
                //Отдаем все сообщения
                $messages = Messages::find("auctionId = $auctionId");

                if(!$messages)
                    $messages = [];

                $written = $review?1:0;
                $response->setJsonContent(
                    [
                        "status" => ['status' => "OK"],
                        "messages" =>$messages,
                        'reviewWritten' =>$written
                    ]
                );
                return $response;
            }
            else{
                //Отдаем только после указанной даты
                $messages = Messages::find("auctionId = $auctionId and date < $date");

                if(!$messages)
                    $messages = [];

                $response->setJsonContent(
                    [
                        "status" => ['status' => "OK"],
                        "messages" =>$messages
                    ]
                );
                return $response;
            }

        }else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function selectOfferAction()
    {
        if($this->request->isPost()){
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $auction = Auctions::findFirstByAuctionId($this->request->getPost('auctionId'));

            if($auction->tasks->getUserId() == $userId){
                $offer = Offers::findFirstByOfferId($this->request->getPost('offerId'));


                if($offer->getAuctionId() == $auction->getAuctionId()){
                    $this->db->begin();
                    $auction->tasks->setStatus(1);
                    if(!$auction->tasks->save()){
                        $this->db->rollback();

                        foreach ($auction->tasks->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $response->setJsonContent(
                            [
                                "errors" => $errors,
                                "status" => "WRONG_DATA"
                            ]);

                        return $response;
                    }

                    $offer->setSelected(1);
                    if(!$offer->save()){
                        $this->db->rollback();

                        foreach ($offer->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $response->setJsonContent(
                            [
                                "errors" => $errors,
                                "status" => "WRONG_DATA"
                            ]);

                        return $response;
                    }

                    $this->db->commit();

                    $response->setJsonContent(
                        [
                            "status" => "OK"
                        ]);

                    return $response;
                }

                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => ["Что-то пошло не так"]
                    ]
                );

            }
            else{
                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA",
                        "errors" => ["Тендер не принадлежит пользователю"]
                    ]
                );
                return $response;
            }

        }else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function addTokenIdAction(){
        if($this->request->isPost()){

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            //$this->session->set("push_token_id",$this->responce->getPost("tokenId"));
            $response = new Response();

            /*$tokenstr = $this->request->getPost("tokenId");

            $count = strlen($tokenstr);*/

            $token = Tokens::findFirstByUserId($userId);

            if(!$token) {

                $token = new Tokens();
                $token->setUserId($userId);
            }
            $token->setToken($this->request->getPost("tokenId"));

            if(!$token->save()){
                foreach ($token->getMessages() as $message) {
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
                ]
            );
            return $response;

        }else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function clearTokensAction(){
        if($this->request->isPost()){

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $response = new Response();

            $tokens = Tokens::findByUserId($userId);

            foreach($tokens as $token){
                $token->delete();
            }

            $response->setJsonContent(
                [
                    "status" => "OK"
                ]
            );
            return $response;

        }else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function finishTaskAction(){
        if($this->request->isPost()){

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $response = new Response();

            $auction = Auctions::findFirstByAuctionId($this->request->getPost("tenderId"));
            $offer = Offers::findFirst(["auctionId =:auctionId: and selected = 1",
                'bind' => [
                    'auctionId' => $auction->getAuctionId()
                ]
            ]);

            $task = $auction->tasks;

            if($task->getUserId() == $userId || $offer->getUserId() == $userId) {
                $task->setStatus(3);

                if (!$task->save()) {
                    foreach ($task->getMessages() as $message) {
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
                    ]
                );
                return $response;
            }
            else{
                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA"
                    ]);

                return $response;
            }
        }else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public function completeTaskAction(){
        if($this->request->isPost()){

            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $response = new Response();

            $auction = Auctions::findFirstByAuctionId($this->request->getPost("tenderId"));

            $task = $auction->tasks;

            if($task->getUserId() == $userId) {
                $task->setStatus(2);

                if (!$task->save()) {
                    foreach ($task->getMessages() as $message) {
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
                    ]
                );
                return $response;
            }
            else{
                $response->setJsonContent(
                    [
                        "status" => "WRONG_DATA"
                    ]);

                return $response;
            }
        }else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }
}
