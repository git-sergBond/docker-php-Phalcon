<?php
use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;
class Accesstokens extends \Phalcon\Mvc\Model
{
    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $tokenid;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $userid;

    /**
     *
     * @var string
     * @Column(type="string", length=68, nullable=false)
     */
    protected $token;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $lifetime;

    /**
     * Method to set the value of field tokenid
     *
     * @param integer $tokenid
     * @return $this
     */
    public function setTokenid($tokenid)
    {
        $this->tokenid = $tokenid;

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
     * Method to set the value of field token
     *
     * @param string $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = hash('sha256',$token);

        return $this;
    }

    /**
     * Returns the value of field tokenid
     *
     * @return integer
     */
    public function getTokenid()
    {
        return $this->tokenid;
    }

    public function setLifetime($lifetime = null)
    {
        if($lifetime == null){
            $this->lifetime = date('Y-m-d H:i:s',time() + 604800);
        } else
            $this->lifetime = $lifetime;

        return $this;
    }

    public function getLifetime()
    {
        return $this->lifetime;
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
     * Returns the value of field token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
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
                    "callback" => function ($token) {
                        $user = Users::findFirst(['userid = :userId:','bind' => ['userId' => $token->getUserId()]],
                            false);
                        if ($user)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'token',
            new PresenceOf(
                [
                    "message" => "Токен не заполнен",
                ]
            )
        );

        $validator->add(
            'lifetime',
            new PresenceOf(
                [
                    "message" => "Не указано время жизни токена",
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
        $this->setSchema("public");
        $this->setSource("accesstokens");
        $this->belongsTo('userid', '\Users', 'userid', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'accesstokens';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Accesstokens[]|Accesstokens|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Accesstokens|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        //SupportClass::writeMessageInLogFile('Зашел в функцию findFirst модели accesstokens. Параметры: '.$parameters);
        $result = parent::findFirst($parameters);
        return $result;
    }

    public static function GenerateToken($userId, $login, $sessionId){
        //$security = Phalcon\DI::getDefault()->getSecurity();
        return  hash('sha256',$sessionId . $userId .
            $login . time());
    }
}
