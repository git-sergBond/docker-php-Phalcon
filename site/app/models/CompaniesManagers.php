<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Callback;

class CompaniesManagers extends \Phalcon\Mvc\Model
{
    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $companyid;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $userid;

    /**
     * Method to set the value of field companyId
     *
     * @param integer $companyid
     * @return $this
     */
    public function setCompanyId($companyid)
    {
        $this->companyid = $companyid;

        return $this;
    }

    /**
     * Method to set the value of field userId
     *
     * @param integer $userid
     * @return $this
     */
    public function setUserId($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Returns the value of field companyId
     *
     * @return integer
     */
    public function getCompanyId()
    {
        return $this->companyid;
    }

    /**
     * Returns the value of field userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userid;
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
            'userid',
            new Callback(
                [
                    "message" => "Пользователь не существует",
                    "callback" => function ($companyManager) {
                        $user = Users::findFirstByUserid($companyManager->getUserId());
                        if ($user)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'companyid',
            new Callback(
                [
                    "message" => "Такая компания не существует",
                    "callback" => function ($companyManager) {
                        $company = Companies::findFirstByCompanyid($companyManager->getCompanyId());

                        if ($company)
                            return true;
                        return false;
                    }
                ]
            )
        );

        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("public");
        $this->setSource("companiesManagers");
        $this->belongsTo('companyid', '\Companies', 'companyid', ['alias' => 'Companies']);
        $this->belongsTo('userid', '\Users', 'userid', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'companiesManagers';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return CompaniesManagers[]|CompaniesManagers|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return CompaniesManagers|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findByIds($companyId, $userManagerId)
    {
        return CompaniesManagers::findFirst(['companyid = :companyId: AND userid = :userId:',
            'bind' => ['companyId' => $companyId, 'userId' => $userManagerId]]);
    }
}
