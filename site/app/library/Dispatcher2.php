<?php
use Phalcon\Mvc\Dispatcher;
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 15.08.2018
 * Time: 13:42
 */

class Dispatcher2 extends Dispatcher
{
    public function getControllerClass(){
        return $this->getControllerName().'Controller';
    }
}