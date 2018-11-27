<?php
use Phalcon\Mvc\Controller;
/**
 * ErrorsController
 *
 * Manage errors
 */
class ErrorsController extends Controller
{

    public function show404Action()
    {
        //$this->view->pick("errors/show404");
        $i = 120;
    }

    public function show401Action()
    {
        $i = 120;
    }

    public function show500Action()
    {
        $i = 120;
    }
}
