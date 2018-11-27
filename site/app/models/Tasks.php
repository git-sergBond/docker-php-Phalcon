<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

class Tasks extends SubjectsWithNotDeleted
{
    /**
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $taskid;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $categoryid;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    protected $name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $deadline;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $price;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $status;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $polygon;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $regionid;

    /**
     *
     * @var string
     * @Column(type="string", length=53, nullable=true)
     */
    protected $longitude;

    /**
     *
     * @var string
     * @Column(type="string", length=53, nullable=true)
     */
    protected $latitude;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $datestart;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $dateend;

    const publicColumns = ['taskid', 'categoryid','name', 'description', 'deadline', 'price',
        'status', 'polygon', 'regionid', 'longitude', 'latitude', 'datestart', 'dateend'];

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
     * Method to set the value of field categoryId
     *
     * @param integer $categoryid
     * @return $this
     */
    public function setCategoryId($categoryid)
    {
        $this->categoryid = $categoryid;

        return $this;
    }

    /**
     * Method to set the value of field name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * Method to set the value of field dateStart
     *
     * @param string $datestart
     * @return $this
     */
    public function setDateStart($datestart)
    {
        $this->datestart = $datestart;

        return $this;
    }

    /**
     * Method to set the value of field dateEnd
     *
     * @param string $dateend
     * @return $this
     */
    public function setDateEnd($dateend)
    {
        $this->dateend = $dateend;

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
     * Method to set the value of field status
     *
     * @param integer $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Method to set the value of field polygon
     *
     * @param string $polygon
     * @return $this
     */
    public function setPolygon($polygon)
    {
        $this->polygon = $polygon;

        return $this;
    }

    /**
     * Method to set the value of field regionId
     *
     * @param integer $regionid
     * @return $this
     */
    public function setRegionId($regionid)
    {
        $this->regionid = $regionid;

        return $this;
    }

    /**
     * Method to set the value of field longitude
     *
     * @param string $longitude
     * @return $this
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Method to set the value of field latitude
     *
     * @param string $latitude
     * @return $this
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
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
     * Returns the value of field categoryId
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->categoryid;
    }

    /**
     * Returns the value of field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * Returns the value of field deadline
     *
     * @return string
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * Returns the value of field dateStart
     *
     * @return string
     */
    public function getDateStart()
    {
        return $this->datestart;
    }

    /**
     * Returns the value of field dateEnd
     *
     * @return string
     */
    public function getDateEnd()
    {
        return $this->dateend;
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
     * Returns the value of field status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns the value of field polygon
     *
     * @return string
     */
    public function getPolygon()
    {
        return $this->polygon;
    }

    /**
     * Returns the value of field regionId
     *
     * @return integer
     */
    public function getRegionId()
    {
        return $this->regionid;
    }

    /**
     * Returns the value of field longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Returns the value of field latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
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
            'categoryid',
            new Callback(
                [
                    "message" => "Такая категория не существует или она не является дочерней",
                    "callback" => function ($task) {
                        $category = Categories::findFirstByCategoryid($task->getCategoryId());

                        if ($category && ($category->getParentId() != null && $category->getParentId() != 0))
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'status',
            new Callback([
                "message" => "Поле статус имеет неверное значение.",
                'callback' => function ($task) {
                $status = Statuses::findFirstByStatusid($task->getStatus());
                    if (!$status)
                        return false;
                    return true;
                }
            ])
        );

        if($this->getLatitude() != null) {
            $validator->add(
                'latitude',
                new Callback([
                    "message" => "Не указана долгота",
                    'callback' => function ($task) {
                        if($task->getLongitude() == null)
                            return false;
                        return true;
                    }
                ])
            );
        }

        if($this->getLongitude() != null) {
            $validator->add(
                'longitude',
                new Callback([
                    "message" => "Не указана широта",
                    'callback' => function ($task) {
                        if($task->getLatitude() == null)
                            return false;
                        return true;
                    }
                ])
            );
        }

        $validator->add(
            'name',
            new PresenceOf([
                "message" => "Должно быть указано название задания"
            ])
        );

        $validator->add(
            'price',
            new Callback([
                "message" => "Должна быть указана цена",
                'callback' => function ($task) {
                    if($task->getPrice() == null || !SupportClass::checkDouble($task->getPrice()))
                        return false;
                    return true;
                }
            ])
        );

        if ($this->getRegionId() != null) {
            $validator->add(
                'regionid',
                new Callback(
                    [
                        "message" => "Указанный регион не существует",
                        "callback" => function ($task) {

                            if ($task->regions != null)
                                return true;
                            return false;
                        }
                    ]
                )
            );
        }

        $validator->add(
            'datestart',
            new PresenceOf([
                "message" => "Дата начала приема заявок должна быть указана"
            ])
        );

        $validator->add(
            'dateend',
            new PresenceOf([
                "message" => "Дата завершения приема заявок должна быть указана"
            ])
        );

        $validator->add(
            'dateend',
            new Callback(
                [
                    "message" => "Дата завершения приема заявок должна быть не раньше даты начала и не позже даты завершения задания",
                    "callback" => function ($task) {
                        if (strtotime($task->getDateStart()) < strtotime($task->getDateEnd())
                                && strtotime($task->getDateEnd()) < strtotime($task->getDeadline()))
                            return true;
                        return false;
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
        $this->setSource("tasks");
        $this->belongsTo('categoryid', '\Categories', 'categoryid', ['alias' => 'Categories']);
        $this->belongsTo('regionid', '\Regions', 'regionid', ['alias' => 'Regions']);
        $this->belongsTo('status', '\Statuses', 'statusid', ['alias' => 'Statuses']);
        $this->belongsTo('userid', '\Users', 'userid', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'tasks';
    }

    /*public static function checkUserHavePermission($userId, $taskId, $right = null)
    {
        $task = Tasks::findFirstByTaskid($taskId);
        $user = Users::findFirstByUserid($userId);

        if (!$task)
            return false;

        if ($task->getSubjectType() == 1) {
            //Предложение компании
            $rightCompany = 'Tasks';
            if($right == 'delete')
                $rightCompany = 'deleteTask';
            else if($right == 'get')
                $rightCompany = 'getTasks';
            else if($right == 'edit')
                $rightCompany = 'editTask';
            else if($right == 'getOffers')
                $rightCompany = 'getOffersForTask';

            if (!Companies::checkUserHavePermission($userId, $task->getSubjectId(), $rightCompany)) {
                return false;
            }
            return true;
        } else if ($task->getSubjectType() == 0) {
            if ($task->getSubjectId() != $userId && $user->getRole() != ROLE_MODERATOR) {
                return false;
            }
            return true;
        }
        return false;
    }*/

    public function beforeDelete(){
        //Проверка, можно ли удалить задание
        $offers = Offers::fincByTaskid($this->getTaskId());
        if(count($offers)!= 0)
            return false;
        return true;
    }
}
