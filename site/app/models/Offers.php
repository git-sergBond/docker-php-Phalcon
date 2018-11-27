<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Mvc\Model\Message;

use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;

class Offers extends SubjectsWithNotDeleted
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $offerid;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $taskid;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $deadline;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $price;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $selected;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $confirmed;

    const publicColumns = ['offerid', 'taskid', 'deadline', 'description', 'price', 'selected', 'confirmed'];

    /**
     * Method to set the value of field offerId
     *
     * @param integer $offerid
     * @return $this
     */
    public function setOfferId($offerid)
    {
        $this->offerid = $offerid;

        return $this;
    }

    /**
     * Method to set the value of field taskId
     *
     * @param integer $taskid
     * @return $this
     */
    public function setTaskId($taskid)
    {
        $this->taskid = $taskid;

        return $this;
    }

    /**
     * Method to set the value of field deadline
     *
     * @param string $deadline
     * @return $this
     */
    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;

        return $this;
    }

    /**
     * Method to set the value of field description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Method to set the value of field price
     *
     * @param integer $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Method to set the value of field selected
     *
     * @param string $selected
     * @return $this
     */
    public function setSelected($selected)
    {
        $this->selected = $selected;

        return $this;
    }

    /**
     * Method to set the value of field confirmed
     *
     * @param string $confirmed
     * @return $this
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * Returns the value of field offerId
     *
     * @return integer
     */
    public function getOfferId()
    {
        return $this->offerid;
    }

    /**
     * Returns the value of field taskId
     *
     * @return integer
     */
    public function getTaskId()
    {
        return $this->taskid;
    }

    /**
     * Returns the value of field deadline
     *
     * @return string
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * Returns the value of field description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the value of field price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Returns the value of field selected
     *
     * @return string
     */
    public function getSelected()
    {
        return $this->selected;
    }

    /**
     * Returns the value of field confirmed
     *
     * @return string
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'taskid',
            new Callback(
                [
                    "message" => "Задание не существует",
                    "callback" => function ($offer) {
                        $task = Tasks::findFirstByTaskid($offer->getTaskId());

                        if ($task && !SubjectsWithNotDeleted::equals($offer->getSubjectId(),
                                $offer->getSubjectType(), $task->getSubjectId(),
                                $task->getSubjectType()))
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'deadline',
            new PresenceOf(
                [
                    "message" => "Дата завершения задания должна быть указана",
                ]
            )
        );

        $validator->add(
            'price',
            new PresenceOf(
                [
                    "message" => "Цена должна быть указана",
                ]
            )
        );

        $validator->add(
            'confirmed',
            new Callback(
                [
                    "message" => "Предложение не может быть подтверждено, пока оно не выбрано заказчиком",
                    "callback" => function ($offer) {
                        if ($offer->getConfirmed() && !$offer->getSelected())
                            return false;
                        return true;
                    }
                ]
            )
        );

        return $this->validate($validator) && parent::validation();
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("public");
        $this->setSource("offers");
        $this->belongsTo('taskid', '\Tasks', 'taskid', ['alias' => 'Tasks']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'offers';
    }

    /*public static function checkUserHavePermission($userId, $offerId, $right = null)
    {
        $offer = Offers::findFirstByOfferid($offerId);

        if (!$offer)
            return false;

        return SubjectsWithNotDeleted::checkUserHavePermission($userId, $offer->getSubjectId(), $offer->getSubjectType(), $right);
    }*/

    /**
     * Подтверждает готовность исполнителя выполнить заказ
     */
    public function confirm()
    {
        $task = Tasks::findFirstByTaskid($this->getTaskId());

        if ($task && $task->getStatus() == STATUS_WAITING_CONFIRM) {
            if ($this->getSelected()) {
                try {
                    // Создаем менеджера транзакций
                    $manager = new TxManager();
                    // Запрос транзакции
                    $transaction = $manager->get();
                    $this->setTransaction($transaction);

                    $this->setConfirmed(true);

                    if (!$this->update()) {
                        $transaction->rollback(
                            "Невозможно подтвердить предложение"
                        );
                        return false;
                    }

                    $task->setStatus(STATUS_EXECUTING);

                    if (!$task->update()) {
                        $transaction->rollback(
                            "Не удалось изменить статус задания"
                        );
                        return false;
                    }
                    $transaction->commit();
                    return true;
                } catch (TxFailed $e) {
                    $message = new Message(
                        $e->getMessage()
                    );

                    $this->appendMessage($message);
                    return false;
                }
            }
            $message = new Message(
                "Это предложение не выбрано"
            );

            $this->appendMessage($message);
            return false;
        }
        $message = new Message(
            "Нельзя подтвердить готовность выполнять задание в текущем состоянии"
        );

        $this->appendMessage($message);
        return false;
    }

    /**
     * Исполнитель отказывается от своего первоначального намерения выполнить заказ
     */
    public function reject()
    {
        $task = Tasks::findFirstByTaskid($this->getTaskId());

        if ($task && ($task->getStatus() == STATUS_WAITING_CONFIRM ||
                $task->getStatus() == STATUS_EXECUTING || $task->getStatus() == STATUS_EXECUTED_EXECUTOR ||
                $task->getStatus() == STATUS_EXECUTED_CLIENT)) {
            if ($this->getSelected()) {
                try {
                    // Создаем менеджера транзакций
                    $manager = new TxManager();
                    // Запрос транзакции
                    $transaction = $manager->get();
                    $this->setTransaction($transaction);

                    $this->setConfirmed(false);

                    if (!$this->update()) {
                        $transaction->rollback(
                            "Невозможно подтвердить предложение"
                        );
                        return false;
                    }

                    if($task->getStatus() == STATUS_WAITING_CONFIRM) {
                        $task->setStatus(STATUS_NOT_CONFIRMED);

                        if (strtotime(date("'Y-m-d H:i:s'")) < strtotime($task->getDateEnd())) {
                            $task->setStatus(STATUS_ACCEPTING);
                        }
                    } else{
                        $task->setStatus(STATUS_NOT_EXECUTED);
                    }

                    if (!$task->update()) {
                        $transaction->rollback(
                            "Не удалось изменить статус задания"
                        );
                        return false;
                    }

                    $transaction->commit();
                    return true;
                } catch (TxFailed $e) {
                    $message = new Message(
                        $e->getMessage()
                    );

                    $this->appendMessage($message);
                    return false;
                }
            }
            return false;
        }
        return false;
    }

    public static function findByTask($taskId){
        return Offers::find(['taskid = :taskId: AND selected = true AND confirmed = true',
            'bind' =>['taskId' => $taskId]
        ]);
    }
}
