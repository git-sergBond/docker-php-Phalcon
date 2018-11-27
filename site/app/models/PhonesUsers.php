<?php

class PhonesUsers extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $phoneid;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $userid;

    /**
     * Method to set the value of field phoneid
     *
     * @param integer $phoneid
     * @return $this
     */
    public function setPhoneId($phoneid)
    {
        $this->phoneid = $phoneid;

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
     * Returns the value of field phoneid
     *
     * @return integer
     */
    public function getPhoneId()
    {
        return $this->phoneid;
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
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("phones_users");
        $this->belongsTo('phoneid', '\Phones', 'phoneid', ['alias' => 'Phones']);
        $this->belongsTo('userid', '\Users', 'userid', ['alias' => 'Users']);
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
            'phoneid',
            new Callback(
                [
                    "message" => "Телефон не был создан",
                    "callback" => function($phoneUser) {
                        $phone = Phones::findFirstByPhoneid($phoneUser->getPhoneId());

                        if($phone)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'userid',
            new Callback(
                [
                    "message" => "Такой пользователь не существует",
                    "callback" => function($phoneUser) {
                        $phone = Users::findFirstByUserid($phoneUser->getUserId());

                        if($phone)
                            return true;
                        return false;
                    }
                ]
            )
        );

        return $this->validate($validator);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'phones_users';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return PhonesUsers[]|PhonesUsers|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return PhonesUsers|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findByIds($userId, $phoneId)
    {
        return PhonesUsers::findFirst(["userid = :userId: AND phoneid = :phoneId:",
            'bind' =>
                [
                    'userId' => $userId,
                    'phoneId' => $phoneId
                ]]);
    }

    public static function getUserPhones($userId)
    {
        $db = Phalcon\DI::getDefault()->getDb();

        $query = $db->prepare("SELECT p.phone FROM phones_users p_u INNER JOIN phones p ON 
            (p_u.phoneid = p.phoneid) where p_u.userid = :userId"
        );

        $query->execute([
            'userId' => $userId,
        ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

}
