<?php

class PhonesUserinfo extends \Phalcon\Mvc\Model
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
    public function setPhoneid($phoneid)
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
    public function setUserid($userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Returns the value of field phoneid
     *
     * @return integer
     */
    public function getPhoneid()
    {
        return $this->phoneid;
    }

    /**
     * Returns the value of field userid
     *
     * @return integer
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("phonesUserinfo");
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
                    "callback" => function($phoneUserinfo) {
                        $phone = Phones::findFirstByPhoneid($phoneUserinfo->getPhoneId());

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
                    "callback" => function($phoneUserinfo) {
                        $phone = Users::findFirstByUserid($phoneUserinfo->getUserId());

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
        return 'phonesUserinfo';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return PhonesUserinfo[]|PhonesUserinfo|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return PhonesUserinfo|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findByIds($userId, $phoneId)
    {
        return PhonesUserinfo::findFirst(["userid = :userId: AND phoneid = :phoneId:",
            'bind' =>
                [
                    'userId' => $userId,
                    'phoneId' => $phoneId
                ]]);
    }

    public static function getUserPhones($userId)
    {
        $db = Phalcon\DI::getDefault()->getDb();

        $query = $db->prepare("SELECT p.phone FROM public.\"phonesUserinfo\" p_u INNER JOIN phones p ON 
            (p_u.phoneid = p.phoneid) where p_u.userid = :userId");

        $query->execute([
            'userId' => $userId,
        ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
}
