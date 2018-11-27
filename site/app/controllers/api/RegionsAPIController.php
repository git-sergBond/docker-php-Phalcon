<?php
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

/**
 * Class RegionsAPIController
 * Контроллер для регионов.
 * Впрочем, он ничего не делает.
 */
class RegionsAPIController extends \Phalcon\Mvc\Controller
{
    public function pullRegionsAction()
    {
        if (($this->request->isGet() && $this->session->get('auth')) || $this->request->isPost()) {

            $response = new Response();

            $result = SupportClass::pullRegions('reg.txt',$this->db);

            if(!$result['result']){
                $response->setJsonContent([
                    'status' => STATUS_WRONG,
                    'errors' => $result['errors']
                ]);
                return $response;
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
}

