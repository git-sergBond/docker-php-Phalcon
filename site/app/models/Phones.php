<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use \libphonenumber\PhoneNumberUtil;

class Phones extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $phoneid;

    /**
     *
     * @var string
     * @Column(type="string", length=20, nullable=false)
     */
    protected $phone;

    const publicColumns = ['phoneid', 'phone'];


    /**
     * Method to set the value of field phoneId
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
     * Method to set the value of field phone
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Returns the value of field phoneId
     *
     * @return integer
     */
    public function getPhoneId()
    {
        return $this->phoneid;
    }

    /**
     * Returns the value of field phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
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
            'phone',
            new Callback(
                [
                    "message" => "Неверный номер",
                    "callback" => function ($phone) {
                        $formatPhone = $this->formatPhone($phone->getPhone());
                        return $this->isValidPhone($formatPhone);
                    }]
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
        $this->setSource("phones");
        $this->hasMany('phoneid', 'Phonescompanies', 'phoneid', ['alias' => 'Phonescompanies']);
        $this->hasMany('phoneid', 'Phonespoints', 'phoneid', ['alias' => 'Phonespoints']);
    }


    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public
    function getSource()
    {
        return 'phones';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Phones[]|Phones|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public
    static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Phones|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function save($data = null, $whiteList = null)
    {
        $phone = Phones::findFirstByPhone($this->formatPhone($this->getPhone()));

        if($phone){
            $this->setPhone($phone->getPhone());
            $this->setPhoneId($phone->getPhoneId());
            return true;
        } else {

            $this->setPhone($this->formatPhone($this->getPhone()));
            $result = parent::save($data, $whiteList);
            return $result;
        }
    }

    /**
     * @param $phone - неотформатированный номер
     * @return string - отформатированный номер, если сумел
     */
    public static function formatPhone($phone){
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            if($phone[0] != '+') {
                if ($phone[0] == '8') {
                    $phone = '+7' . substr($phone, 1);
                }
                else{
                    $phone = '+' . $phone;
                }
            }
            $swissNumberProto = $phoneUtil->parse($phone, '');
        }catch(\libphonenumber\NumberParseException $exception){
            return $phone;
        }

        $formatPhone = $phoneUtil->formatOutOfCountryCallingNumber($swissNumberProto, "");
        return $formatPhone;
    }

    /**
     * @param $phone - неотформатированный номер
     * @return boolean
     */
    public static function isValidPhone($phone){

        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try{
            $swissNumberProto = $phoneUtil->parse($phone, '');

            return $phoneUtil->isValidNumber($swissNumberProto);
        }catch(\libphonenumber\NumberParseException $exception){
            return false;
        }
    }

    /**
     * @return integer - количество ссылок
     */
    public function countOfReferences(){
        $phonesPoints = PhonesPoints::findByPhoneid($this->getPhoneId());
        $phonesCompanies = PhonesCompanies::findByPhoneid($this->getPhoneId());
        $phonesUsers = PhonesUsers::findByPhoneid($this->getPhoneId());

        return $phonesCompanies->count() + $phonesPoints->count() + $phonesUsers->count();
    }
}
