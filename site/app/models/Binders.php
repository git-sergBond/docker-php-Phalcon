<?php

class Binders
{
    public static function checkUserHavePermission($userId, $binderId, $binderType, $executor, $right = null)
    {
        $user = Users::findFirstByUserid($userId);

        if (!$user)
            return false;

        if ($binderType == 'task') {
            //связующий объект - заказ (task)

            $task = Tasks::findFirstByTaskid($binderId);
            if(!$task)
                return false;

            if($executor)
            {
                $binder = Offers::findFirst(['taskid = :taskId: AND selected = true',
                    'bind' => ['taskId' => $task->getTaskId()]]);
                if(!$binder){
                    return false;
                }
            } else{
                $binder = $task;
            }
            return SubjectsWithNotDeleted::checkUserHavePermission($userId, $binder->getSubjectId(),$binder->getSubjectType(),$right);
        } else if ($binderType == 'request') {
            //связующий объект - заявка

            $request = Requests::findFirstByRequestid($binderId);
            if(!$request)
                return false;

            if($executor)
            {
                $binder = $request->services;
                if(!$binder){
                    return false;
                }
            } else{
                $binder = $request;
            }

            return SubjectsWithNotDeleted::checkUserHavePermission($userId, $binder->getSubjectId(),$binder->getSubjectType(),$right);
        }

        return false;
    }

    public static function checkBinderExists($binderId, $binderType)
    {
        if ($binderType == 'task') {
            $task = Tasks::findFirstByTaskid($binderId);
            if (!$task)
                return false;
            $offer = Offers::findFirst(['taskid = :taskId: AND selected = true',
                'bind' => ['taskId' => $task->getTaskId()]]);
            if (!$offer)
                return false;
            return true;
        } else if ($binderType == 'request') {
            $request = Requests::findFirstByRequestid($binderId);
            if ($request && $request->services)
                return true;
            return false;
        } else
            return false;
    }

}