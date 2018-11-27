<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 07.08.2018
 * Time: 15:23
 */

class BodyMethodConverter
{
    /**
     *
     * @param Event $event
     * @param Dispatcher $dispatcher
     * @return bool
     */
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        if($this->request->getRawBody()!= null && $this->request->getRawBody()!= ""){
            $params = $this->request->getRawBody();
            $params = json_decode($params, true);

            if($params!= null){
                foreach($params as $key=>$params){
                    /*if($this->request->isPost()){
                        $_POST[]
                    } else if($this->request->isPut()){

                    }*/
                    $_REQUEST[$key] = $params;
                }
            }
        }
    }
}