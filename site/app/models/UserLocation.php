<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;

class UserLocation extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $userid;

    /**
     *
     * @var string
     * @Column(type="string", length=53, nullable=false)
     */
    protected $longitude;

    /**
     *
     * @var string
     * @Column(type="string", length=53, nullable=false)
     */
    protected $latitude;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $lasttime;

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
     * Method to set the value of field longitude
     *
     * @param string $longitude
     * @return $this
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Method to set the value of field latitude
     *
     * @param string $latitude
     * @return $this
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Method to set the value of field lasttime
     *
     * @param string $lasttime
     * @return $this
     */
    public function setLastTime($lasttime)
    {
        $this->lasttime = $lasttime;

        return $this;
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
     * Returns the value of field longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Returns the value of field latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Returns the value of field lasttime
     *
     * @return string
     */
    public function getLastTime()
    {
        return $this->lasttime;
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
                    "message" => "Пользователь не существует или был удален",
                    "callback" => function ($userlocation) {
                        $user = Users::findFirstByUserid($userlocation->getUserId());
                        if ($user)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'longitude',
            new Callback([
                "message" => "Не указана долгота",
                "callback" => function ($userlocation) {
                    if ($userlocation->getLongitude() != null && is_double($userlocation->getLongitude()))
                        return true;
                    return false;
                }
            ])
        );

        $validator->add(
            'latitude',
            new Callback([
                "message" => "Не указана широта",
                "callback" => function ($userlocation) {
                    if ($userlocation->getLatitude() != null && is_double($userlocation->getLatitude()))
                        return true;
                    return false;
                }
            ])
        );

        $validator->add(
            'lasttime',
            new PresenceOf([
                "message" => "Должны быть указаны дата и время, когда местоположение было актуально"
            ])
        );

        /*$validator->add(
            'lasttime',
            new Callback(
                [
                    "message" => "Время актуальности местоположения долно быть раньше текущего. Никуда не денешься, элементарная логика.",
                    "callback" => function ($userlocation) {
                        if (strtotime($userlocation->getLastTime()) <= time())
                            return true;
                        return false;
                    }
                ]
            )
        );*/

        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->setSource("user_location");
        $this->belongsTo('userid', '\Users', 'userid', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'user_location';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserLocation[]|UserLocation|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserLocation|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findUsersByQuery($query, $longitudeRH, $latitudeRH,
                                            $longitudeLB, $latitudeLB)
    {

        $db = Phalcon\DI::getDefault()->getDb();

        $query = str_replace('!', '', $query);
        $query = str_replace('|', '', $query);
        $query = str_replace('&', '', $query);
        $ress = explode(' ', $query);
        $res2 = [];
        foreach ($ress as $res) {
            if (trim($res) != "")
                $res2[] = trim($res);
        }

        $str = implode(' ', $res2);

        $query = $db->prepare("select userid, email, phone,
    firstname,lastname, patronymic, longitude, latitude, lasttime,
    male, birthday,pathtophoto,status from get_users_for_search_like(:str,:longituderh,
            :latituderh, :longitudelb, :latitudelb) 
            where lasttime > :lasttime
            LIMIT 50");

        $query->execute([
            'str' => $str,
            'longituderh' => $longitudeRH,
            'latituderh' => $latitudeRH,
            'longitudelb' => $longitudeLB,
            'latitudelb' => $latitudeLB,
            'lasttime' => date('Y-m-d H:i:s', time() + -3600),
        ]);
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $str = var_export($result, true);

        SupportClass::writeMessageInLogFile('Результат поиска по юзерам:');
        SupportClass::writeMessageInLogFile($str);
        /*return $query->fetchAll(\PDO::FETCH_ASSOC);*/
        return $result;
    }

    public static function findUsersByQueryWithFilters($query, $longitudeRH, $latitudeRH,
                                            $longitudeLB, $latitudeLB,$ageMin = null, $ageMax = null,
                                                       $male = null, $hasPhoto = null)
    {
        $db = Phalcon\DI::getDefault()->getDb();

        $query = str_replace('!', '', $query);
        $query = str_replace('|', '', $query);
        $query = str_replace('&', '', $query);
        $ress = explode(' ', $query);
        $res2 = [];
        foreach ($ress as $res) {
            if (trim($res) != "")
                $res2[] = trim($res);
        }

        $str = implode(' ', $res2);

        $sqlQuery = "select userid, email, phone,
    firstname,lastname, patronymic, longitude, latitude, lasttime,
    male, birthday,pathtophoto,status from get_users_for_search_like_2(:str,:longituderh,
            :latituderh, :longitudelb, :latitudelb) 
            where lasttime > :lasttime";

        $params = [
            'str' => $str,
            'longituderh' => $longitudeRH,
            'latituderh' => $latitudeRH,
            'longitudelb' => $longitudeLB,
            'latitudelb' => $latitudeLB,
            'lasttime' => date('Y-m-d H:i:s', time() + -3600),
        ];
        if($ageMin!=null){
            $dateMin = date('Y-m-d H:i:s', mktime(date('H'),date('i'),date('s'),
                date('m'),date('d'),date('Y') - $ageMin));
            $sqlQuery.= " and birthday >= :dateMin";
            $params['dateMin'] = $dateMin;
        }

        if($ageMax!=null){
            $dateMax = date('Y-m-d H:i:s', mktime(date('H'),date('i'),date('s'),
                date('m'),date('d'),date('Y') - $ageMax));
            $sqlQuery.= " and birthday <= :dateMax";
            $params['dateMax'] = $dateMax;
        }

        if($male!=null){
            $sqlQuery.= " and male = :male";
            $params['male'] = $male;
        }

        if($hasPhoto!=null){
            if($hasPhoto)
                $sqlQuery.= " and not (pathtophoto is null)";
            else{
                $sqlQuery.= " and (pathtophoto is null)";
            }
        }

        $sqlQuery.=" LIMIT 50";

        $query = $db->prepare($sqlQuery);

        $query->execute($params);
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $str = var_export($result, true);
        return $result;
    }

    function calculate_age($birthday) {
        $birthday_timestamp = strtotime($birthday);
        $age = date('Y') - date('Y', $birthday_timestamp);
        if (date('md', $birthday_timestamp) > date('md')) {
            $age--;
        }
        return $age;
    }

    public static function getAutoComplete($query, $longitudeRH, $latitudeRH,
                                           $longitudeLB, $latitudeLB)
    {

        $db = Phalcon\DI::getDefault()->getDb();

        $query = str_replace('!', '', $query);
        $query = str_replace('|', '', $query);
        $query = str_replace('&', '', $query);
        $ress = explode(' ', $query);
        $res2 = [];
        foreach ($ress as $res) {
            if (trim($res) != "")
                $res2[] = trim($res);
        }

        $str = implode(' ', $res2);

        $query = $db->prepare("select userid, firstname, lastname, patronymic,pathtophoto from 
            get_users_for_search_like(:str,:longituderh,
            :latituderh, :longitudelb, :latitudelb) 
            where lasttime > :lasttime
            LIMIT 50");

        $query->execute([
            'str' => $str,
            'longituderh' => $longitudeRH,
            'latituderh' => $latitudeRH,
            'longitudelb' => $longitudeLB,
            'latitudelb' => $latitudeLB,
            'lasttime' => date('Y-m-d H:i:s', time() + -3600),
        ]);
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        $str = var_export($result, true);

        SupportClass::writeMessageInLogFile('Результат поиска по юзерам для автокомплита:');
        SupportClass::writeMessageInLogFile($str);

        /* return $query->fetchAll(\PDO::FETCH_ASSOC);*/
        return $result;
    }

    public static function getUserinfo($userid)
    {

        $db = Phalcon\DI::getDefault()->getDb();

        $query = $db->prepare("select users.userid, users.email, phones.phone,
    firstname,lastname, patronymic, longitude, latitude, lasttime,
    male, birthday,pathtophoto,status
            from 
            users 
    INNER JOIN userinfo USING(userid)
    INNER JOIN user_location USING(userid)
    LEFT JOIN phones USING (phoneid)
    where userid =:userid
            and lasttime > :lasttime");

        $query->execute([
            'userid' => $userid,
            'lasttime' => date('Y-m-d H:i:s', time() + -3600),
        ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
}
