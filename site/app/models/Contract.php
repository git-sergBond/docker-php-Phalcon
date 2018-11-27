<?php

class Contract extends SubjectsWithNotDeleted
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $contractid;

    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    protected $contractnumber;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $subjectidtwo;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $subjecttypetwo;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=true)
     */
    protected $requisitesone;

    /**
     *
     * @var string
     * @Column(type="string", length=500, nullable=true)
     */
    protected $requisitestwo;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $userorganizer;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $sum;

    /**
     * Method to set the value of field contractId
     *
     * @param integer $contractId
     * @return $this
     */
    public function setContractId($contractId)
    {
        $this->contractid = $contractId;

        return $this;
    }

    /**
     * Method to set the value of field contractnumber
     *
     * @param string $contractnumber
     * @return $this
     */
    public function setContractNumber($contractnumber)
    {
        $this->contractnumber = $contractnumber;

        return $this;
    }

    /**
     * Method to set the value of field subjectidtwo
     *
     * @param integer $subjectidtwo
     * @return $this
     */
    public function setSubjectIdTwo($subjectidtwo)
    {
        $this->subjectidtwo = $subjectidtwo;

        return $this;
    }

    /**
     * Method to set the value of field subjecttypetwo
     *
     * @param integer $subjecttypetwo
     * @return $this
     */
    public function setSubjectTypeTwo($subjecttypetwo)
    {
        $this->subjecttypetwo = $subjecttypetwo;

        return $this;
    }

    /**
     * Method to set the value of field requisitesone
     *
     * @param string $requisitesone
     * @return $this
     */
    public function setRequisitesOne($requisitesone)
    {
        $this->requisitesone = $requisitesone;

        return $this;
    }

    /**
     * Method to set the value of field requisitestwo
     *
     * @param string $requisitestwo
     * @return $this
     */
    public function setRequisitesTwo($requisitestwo)
    {
        $this->requisitestwo = $requisitestwo;

        return $this;
    }

    /**
     * Method to set the value of field userOrganizer
     *
     * @param integer $userOrganizer
     * @return $this
     */
    public function setUserOrganizer($userOrganizer)
    {
        $this->userorganizer = $userOrganizer;

        return $this;
    }

    /**
     * Method to set the value of field sum
     *
     * @param integer $sum
     * @return $this
     */
    public function setSum($sum)
    {
        $this->sum = $sum;

        return $this;
    }

    /**
     * Returns the value of field contractId
     *
     * @return integer
     */
    public function getContractId()
    {
        return $this->contractid;
    }

    /**
     * Returns the value of field contractnumber
     *
     * @return string
     */
    public function getContractNumber()
    {
        return $this->contractnumber;
    }

    /**
     * Returns the value of field subjectidtwo
     *
     * @return integer
     */
    public function getSubjectIdTwo()
    {
        return $this->subjectidtwo;
    }

    /**
     * Returns the value of field subjecttypetwo
     *
     * @return integer
     */
    public function getSubjectTypeTwo()
    {
        return $this->subjecttypetwo;
    }

    /**
     * Returns the value of field requisitesone
     *
     * @return string
     */
    public function getRequisitesOne()
    {
        return $this->requisitesone;
    }

    /**
     * Returns the value of field requisitestwo
     *
     * @return string
     */
    public function getRequisitesTwo()
    {
        return $this->requisitestwo;
    }

    /**
     * Returns the value of field userOrganizer
     *
     * @return integer
     */
    public function getUserOrganizer()
    {
        return $this->userorganizer;
    }

    /**
     * Returns the value of field sum
     *
     * @return integer
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("contract");
        $this->belongsTo('userorganizer', '\Users', 'userid', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'contract';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Contract[]|Contract|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Contract|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
