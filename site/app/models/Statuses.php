<?php

class Statuses extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $statusid;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=false)
     */
    protected $status;

    /**
     * Method to set the value of field statusId
     *
     * @param integer $statusid
     * @return $this
     */
    public function setStatusId($statusid)
    {
        $this->statusid = $statusid;

        return $this;
    }

    /**
     * Method to set the value of field status
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
     * Returns the value of field statusId
     *
     * @return integer
     */
    public function getStatusId()
    {
        return $this->statusid;
    }

    /**
     * Returns the value of field status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("public");
        $this->setSource("statuses");
        $this->hasMany('statusid', 'Requests', 'status', ['alias' => 'Requests']);
        $this->hasMany('statusid', 'Tasks', 'status', ['alias' => 'Tasks']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'statuses';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Statuses[]|Statuses|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Statuses|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
