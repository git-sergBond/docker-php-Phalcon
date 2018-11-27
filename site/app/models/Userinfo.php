<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

class Userinfo extends \Phalcon\Mvc\Model
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
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    protected $firstname;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=true)
     */
    protected $patronymic;

    /**
     *
     * @var string
     * @Column(type="string", length=30, nullable=false)
     */
    protected $lastname;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $birthday;

    /**
     *
     * @var integer
     * @Column(type="integer", length=4, nullable=false)
     */
    protected $male;

    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=true)
     */
    protected $address;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $about;

    /**
     *
     * @var string
     * @Column(type="string", length=1000, nullable=true)
     */
    protected $status;

    /**
     * @var integer
     * @Column(type="tyniint", length=4, nullable=false)
     */
    protected $ratingexecutor;
    protected $ratingclient;
    protected $pathtophoto;

    protected $lasttime;

    protected $email;

    protected $phones;

    const publicColumns = ['userid', 'firstname', 'lastname', 'patronymic',
        'birthday', 'male', 'address', 'about', 'status', 'ratingexecutor', 'ratingclient', 'pathtophoto', 'lasttime'];

    const publicColumnsInStr = 'userid, firstname, lastname, patronymic,
        birthday, male, address, about, status, ratingexecutor, ratingclient, pathtophoto, lasttime';
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
     * Method to set the value of field firstname
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * @param mixed $phones
     */
    public function setPhones($phones)
    {
        $this->phones = $phones;
    }



    /**
     * Method to set the value of field patronymic
     *
     * @param string $patronymic
     * @return $this
     */
    public function setPatronymic($patronymic)
    {
        $this->patronymic = $patronymic;

        return $this;
    }

    /**
     * Method to set the value of field lastname
     *
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Method to set the value of field birthday
     *
     * @param string $birthday
     * @return $this
     */
    public function setBirthday($birthday)
    {
        if($birthday==="")
            $birthday=null;
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Method to set the value of field male
     *
     * @param integer $male
     * @return $this
     */
    public function setMale($male)
    {
        if($male == 'мужской' || $male == 'Мужской')
            $this->male = 1;
        else if($male == 'женский' || $male == 'Женский')
            $this->male = 0;
        else
            $this->male = $male;

        return $this;
    }

    /**
     * Method to set the value of field address
     *
     * @param string $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Method to set the value of field about
     *
     * @param string $about
     * @return $this
     */
    public function setAbout($about)
    {
        $this->about = $about;

        return $this;
    }

    /**
     * Method to set the value of field about
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }


    public function setRatingExecutor($ratingexecutor)
    {
        $this->ratingexecutor = $ratingexecutor;

        return $this;
    }

    public function setRatingClient($ratingclient)
    {
        $this->ratingclient = $ratingclient;

        return $this;
    }

    public function setPathToPhoto($pathtophoto)
    {
        $this->pathtophoto = $pathtophoto;

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
     * Returns the value of field status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns the value of field firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Returns the value of field patronymic
     *
     * @return string
     */
    public function getPatronymic()
    {
        return $this->patronymic;
    }

    /**
     * Returns the value of field lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }


    public function getFIO()
    {
        return "$this->lastname $this->firstname $this->patronymic";
    }

    /**
     * Returns the value of field birthday
     *
     * @return string
     */
    public function getBirthday()
    {
        return $this->birthday;
    }



    /**
     * Returns the value of field male
     *
     * @return integer
     */
    public function getMale()
    {
        return $this->male;
    }

    /**
     * Returns the value of field address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Returns the value of field about
     *
     * @return string
     */
    public function getAbout()
    {
        return $this->about;
    }

    public function getRatingExecutor()
    {
        return $this->ratingexecutor;
    }
    public function getRatingClient()
    {
        return $this->ratingclient;
    }
    public function getPathToPhoto()
    {
        return $this->pathtophoto;
    }

    /**
     * @return mixed
     */
    public function getLasttime()
    {
        return $this->lasttime;
    }

    /**
     * @param mixed $lasttime
     */
    public function setLasttime($lasttime)
    {
        $this->lasttime = $lasttime;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }


    //Поля не из базы






    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'firstname',
            new PresenceOf(
                [
                    "message" => "Требуется указать имя пользователя",
                ]
            )
        );

        $validator->add(
            'lastname',
            new PresenceOf(
                [
                    "message" => "Требуется указать фамилию пользователя",
                ]
            )
        );

        $validator->add(
            'male',
            new PresenceOf(
                [
                    "message" => "Требуется указать пол",
                ]
            )
        );

        if($this->getPathToPhoto() != null)
            $validator->add(
                'pathtophoto',
                new Callback(
                    [
                        "message" => "Формат картинки не поддерживается",
                        "callback" => function ($user) {
                            $format = pathinfo($user->getPathToPhoto(), PATHINFO_EXTENSION);

                            if ($format == 'jpeg' || 'jpg')
                                return true;
                            elseif ($format == 'png')
                                return true;
                            elseif ($format == 'gif')
                                return true;
                            else {
                                return false;
                            }
                        }
                    ]
                )
            );

        if($this->getEmail() != null)
        $validator->add(
            'email',
            new EmailValidator(
                [
                    'model' => $this,
                    'message' => 'Введите, пожалуйста, правильный адрес',
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
        $this->setSource("userinfo");
        $this->belongsTo('userid', '\Users', 'userid', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'userinfo';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Userinfo[]|Userinfo|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Userinfo|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function beforeSave()
    {
        /*if($this->getRatingClient() == null)
            $this->setRatingClient(5);
        if($this->getRatingExecutor() == null)
            $this->setRatingExecutor(5);*/
    }
}
