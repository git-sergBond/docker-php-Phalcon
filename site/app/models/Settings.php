<?php

class Settings extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $userid;

    /**
     *
     * @var integer
     * @Column(type="string", nullable=false)
     */
    protected $notificationemail;

    /**
     *
     * @var integer
     * @Column(type=""string", nullable=false)
     */
    protected $notificationsms;

    /**
     *
     * @var integer
     * @Column(type="string", nullable=false)
     */
    protected $notificationpush;

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
     * Method to set the value of field notificationEmail
     *
     * @param integer $notificationemail
     * @return $this
     */
    public function setNotificationEmail($notificationemail)
    {
        $this->notificationemail = $notificationemail;

        return $this;
    }

    /**
     * Method to set the value of field notificationSms
     *
     * @param integer $notificationsms
     * @return $this
     */
    public function setNotificationSms($notificationsms)
    {
        $this->notificationsms = $notificationsms;

        return $this;
    }

    /**
     * Method to set the value of field notificationPush
     *
     * @param integer $notificationpush
     * @return $this
     */
    public function setNotificationPush($notificationpush)
    {
        $this->notificationpush = $notificationpush;

        return $this;
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
     * Returns the value of field notificationEmail
     *
     * @return integer
     */
    public function getNotificationEmail()
    {
        return $this->notificationemail;
    }

    /**
     * Returns the value of field notificationSms
     *
     * @return integer
     */
    public function getNotificationSms()
    {
        return $this->notificationsms;
    }

    /**
     * Returns the value of field notificationPush
     *
     * @return integer
     */
    public function getNotificationPush()
    {
        return $this->notificationpush;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("service_services");
        $this->setSource("settings");
        $this->hasOne('userid', '\Userinfo', 'userid', ['alias' => 'Userinfo']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Settings[]|Settings|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Settings|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'settings';
    }

}
