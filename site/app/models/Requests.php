<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

class Requests extends SubjectsWithNotDeleted
{
    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $requestid;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $serviceid;

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
    protected $dateend;

    /**
     *
     * @var integer
     * @Column(type="integer", nullable=false)
     */
    protected $status;

    public const publicColumns = ['requestid', 'serviceid', 'description', 'dateend', 'status'];

    /**
     * Method to set the value of field requestId
     *
     * @param integer $requestid
     * @return $this
     */
    public function setRequestId($requestid)
    {
        $this->requestid = $requestid;

        return $this;
    }

    /**
     * Method to set the value of field serviceId
     *
     * @param integer $serviceid
     * @return $this
     */
    public function setServiceId($serviceid)
    {
        $this->serviceid = $serviceid;

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
     * Method to set the value of field deleted
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Returns the value of field requestId
     *
     * @return integer
     */
    public function getRequestId()
    {
        return $this->requestid;
    }

    /**
     * Returns the value of field serviceId
     *
     * @return integer
     */
    public function getServiceId()
    {
        return $this->serviceid;
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
     * Returns the value of field dateEnd
     *
     * @return string
     */
    public function getDateEnd()
    {
        return $this->dateend;
    }

    /**
     * Returns the value of field deleted
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
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
            'serviceid',
            new Callback(
                [
                    "message" => "Такая услуга не существует",
                    "callback" => function ($request) {
                        $service = Services::findFirstByServiceid($request->getServiceId());

                        if ($service)
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
                'callback' => function ($request) {
                    $status = Statuses::findFirstByStatusid($request->getStatus());
                    if (!$status)
                        return false;
                    return true;
                }
            ])
        );

        if($this->getDateEnd()!= null)
        $validator->add(
            'dateend',
            new Callback([
                "message" => "Крайняя дата на получение услуги должна быть позже текущего времени",
                'callback' => function ($request) {
                    if (strtotime($request->getDateEnd()) > strtotime(date('Y-m-d H:i:s')))
                        return true;
                    return false;
                }
            ])
        );

        return $this->validate($validator) && parent::validation();
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("public");
        $this->setSource("requests");
        $this->belongsTo('serviceid', '\Services', 'serviceid', ['alias' => 'Services']);
        $this->belongsTo('status', '\Statuses', 'statusid', ['alias' => 'Statuses']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'requests';
    }

}
