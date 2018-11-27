<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class FavoriteUsers extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $usersubject;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $userobject;

    /**
     * Method to set the value of field userSubject
     *
     * @param integer $usersubject
     * @return $this
     */
    public function setUserSubject($usersubject)
    {
        $this->usersubject = $usersubject;

        return $this;
    }

    /**
     * Method to set the value of field userObject
     *
     * @param integer $userobject
     * @return $this
     */
    public function setUserObject($userobject)
    {
        $this->userobject = $userobject;

        return $this;
    }

    /**
     * Returns the value of field userSubject
     *
     * @return integer
     */
    public function getUserSubject()
    {
        return $this->usersubject;
    }

    /**
     * Returns the value of field userObject
     *
     * @return integer
     */
    public function getUserObject()
    {
        return $this->userobject;
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
            'userobject',
            new Callback(
                [
                    "message" => "Пользователь для подписки не существует",
                    "callback" => function($favCompany) {
                        $user = Users::findFirstByUserid($favCompany->getUserObject());

                        if($user)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'usersubject',
            new Callback(
                [
                    "message" => "Пользователь подписчик не существует",
                    "callback" => function($favCompany) {
                        $user = Users::findFirstByUserid($favCompany->getUserSubject());

                        if($user)
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
        //$this->setSchema("service_services");
        $this->setSource("favoriteUsers");
        $this->belongsTo('userobject', '\Users', 'userid', ['alias' => 'Users']);
        $this->belongsTo('usersubject', '\Users', 'userid', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'favoriteUsers';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Favoriteusers[]|Favoriteusers|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Favoriteusers|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findByIds($userIdObject,$userIdSubject)
    {
        return Favoriteusers::findFirst(["userobject = :userIdObject: AND usersubject = :userIdSubject:",
            "bind" => [
                "userIdObject" => $userIdObject,
                "userIdSubject" => $userIdSubject,
            ]
        ]);
    }
}
