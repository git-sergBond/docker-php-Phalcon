<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

class Userssocial extends \Phalcon\Mvc\Model
{
    /**
     *
     * @var string
     * @Column(type="string", length=50, nullable=false)
     */
    protected $network;

    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=false)
     */
    protected $identity;

    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=true)
     */
    protected $profile;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $usersocialid;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $userid;

    /**
     * Method to set the value of field network
     *
     * @param string $network
     * @return $this
     */
    public function setNetwork($network)
    {
        $this->network = $network;

        return $this;
    }

    /**
     * Method to set the value of field identity
     *
     * @param string $identity
     * @return $this
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * Method to set the value of field profile
     *
     * @param string $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Method to set the value of field usersocialid
     *
     * @param integer $usersocialid
     * @return $this
     */
    public function setUserSocialId($usersocialid)
    {
        $this->usersocialid = $usersocialid;

        return $this;
    }

    /**
     * Method to set the value of field userid
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
     * Returns the value of field network
     *
     * @return string
     */
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * Returns the value of field identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * Returns the value of field profile
     *
     * @return string
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Returns the value of field usersocialid
     *
     * @return integer
     */
    public function getUserSocialId()
    {
        return $this->usersocialid;
    }

    /**
     * Returns the value of field userid
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
                    "message" => "Пользователь не был создан",
                    "callback" => function($usersocial) {
                        $user = Users::findFirstByUserid($usersocial->getUserId());
                        if($user)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'network',
            new PresenceOf(
                [
                    "message" => "Не указана социальная сеть",
                ]
            )
        );

        $validator->add(
            'identity',
            new PresenceOf(
                [
                    "message" => "Не указан уникальный идентификатор пользователя",
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
        $this->setSource("userssocial");
        $this->belongsTo('userid', '\Users', 'userid', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'userssocial';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Userssocial[]|Userssocial|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Userssocial|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findByIdentity($network, $identity)
    {
        return parent::findFirst(['network = :network: AND identity = :identity:',
            'bind' => [
                'network' => $network,
                'identity' => $identity
            ]]);
    }

}
