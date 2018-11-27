<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Callback;

class Events extends SubjectsWithNotDeleted
{
    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $eventid;

    /**
     *
     * @var string
     * @Column(type="string", length=150, nullable=false)
     */
    protected $name;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $pointid;

    /**
     *
     * @var string
     * @Column(type="string", length=53, nullable=true)
     */
    protected $longitude;

    /**
     *
     * @var string
     * @Column(type="string", length=53, nullable=true)
     */
    protected $latitude;

    protected $pathtoimage;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $datepublication;

     const publicColumns = ['eventid', 'name', 'description', 'pointid', 'longitude', 'latitude', 'pathtoimage',
        'datepublication'];

    /**
     * Method to set the value of field eventid
     *
     * @param integer $eventid
     * @return $this
     */
    public function setEventId($eventid)
    {
        $this->eventid = $eventid;

        return $this;
    }

    /**
     * Method to set the value of field name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Method to set the value of field description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Method to set the value of field pointid
     *
     * @param integer $pointid
     * @return $this
     */
    public function setPointId($pointid)
    {
        $this->pointid = $pointid;

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

    public function setPathToImage($pathtoimage)
    {
        $this->pathtoimage = $pathtoimage;

        return $this;
    }

    /**
     * Method to set the value of field datePublication
     *
     * @param string $datepublication
     * @return $this
     */
    public function setDatePublication($datepublication)
    {
        $this->datepublication = $datepublication;

        return $this;
    }

    /**
     * Returns the value of field datePublication
     *
     * @return string
     */
    public function getDatePublication()
    {
        return $this->datepublication;
    }

    /**
     * Returns the value of field eventid
     *
     * @return integer
     */
    public function getEventId()
    {
        return $this->eventid;
    }

    /**
     * Returns the value of field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the value of field description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the value of field pointid
     *
     * @return integer
     */
    public function getPointId()
    {
        return $this->pointid;
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

    public function getPathToImage()
    {
        return $this->pathtoimage;
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        if($this->getLongitude() == null && $this->getLatitude() == null)
        $validator->add(
            'pointid',
            new Callback(
                [
                    "message" => "Для акции должна быть указана точка оказания услуг или ее геопозиция",
                    "callback" => function ($event) {
                        $point = TradePoints::findFirstByPointid($event->getPointId());
                        if ($point)
                            return true;
                        return false;
                    }
                ]
            )
        );

        if ($this->getLongitude() != null) {
            $validator->add(
                'latitude',
                new Callback(
                    [
                        "message" => "Не указана широта для акции",
                        "callback" => function ($service) {
                            if ($service->getLatitude() != null && SupportClass::checkDouble($service->getLatitude()))
                                return true;
                            return false;
                        }
                    ]
                )
            );
        }

        if ($this->getLatitude() != null) {
            $validator->add(
                'longitude',
                new Callback(
                    [
                        "message" => "Не указана долгота для акции",
                        "callback" => function ($service) {
                            if ($service->getLongitude() != null && SupportClass::checkDouble($service->getLongitude()))
                                return true;
                            return false;
                        }
                    ]
                )
            );
        }

        if($this->getPathToImage() != null)
            $validator->add(
                'pathtoimage',
                new Callback(
                    [
                        "message" => "Формат картинки не поддерживается",
                        "callback" => function ($user) {
                            $format = pathinfo($user->getPathToImage(), PATHINFO_EXTENSION);

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

        $validator->add(
            'name',
            new PresenceOf(
                [
                    "message" => "Требуется указать название акции",
                ]
            )
        );

        return $this->validate($validator) && parent::validation();
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("public");
        $this->setSource("events");
        $this->belongsTo('pointid', '\TradePoints', 'pointid', ['alias' => 'Tradepoints']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'events';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Events[]|Events|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Events|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
