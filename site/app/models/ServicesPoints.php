<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Callback;

class ServicesPoints extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $serviceid;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $pointid;

    /**
     * Method to set the value of field serviceId
     *
     * @param integer $serviceid
     * @return $this
     */
    public function setServiceId($serviceid)
    {
        $this->serviceid = $serviceid;

        return $this;
    }

    /**
     * Method to set the value of field pointId
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
     * Returns the value of field serviceId
     *
     * @return integer
     */
    public function getServiceId()
    {
        return $this->serviceid;
    }

    /**
     * Returns the value of field pointId
     *
     * @return integer
     */
    public function getPointId()
    {
        return $this->pointid;
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
            'serviceid',
            new Callback(
                [
                    "message" => "Такая услуга не существует",
                    "callback" => function ($servicePoint) {
                        $service = Services::findFirstByServiceid($servicePoint->getServiceId());

                        if ($service)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'pointid',
            new Callback(
                [
                    "message" => "Такая точка оказания услуг не существует или не связана с компанией услуги",
                    "callback" => function ($servicePoint) {
                        $point = TradePoints::findFirstByPointid($servicePoint->getPointId());
                        $service = Services::findFirstByServiceid($servicePoint->getServiceId());
                        if ($point && $service &&
                            (SubjectsWithNotDeleted::equals($point->getSubjectId(), $point->getSubjectType(),$service->getSubjectId(), $service->getSubjectType())))
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
        $this->setSchema("public");
        $this->setSource("servicesPoints");
        $this->belongsTo('pointid', '\TradePoints', 'pointid', ['alias' => 'Tradepoints']);
        $this->belongsTo('serviceid', '\Services', 'serviceid', ['alias' => 'Services']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'servicesPoints';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ServicesPoints[]|ServicesPoints|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ServicesPoints|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findByIds($serviceId, $pointId)
    {
        return ServicesPoints::findFirst(['serviceid = :serviceId: AND pointid = :pointId:',
            'bind' => ['serviceId' => $serviceId, 'pointId' => $pointId]]);;
    }

    public function beforeDelete(){
        //Проверка, можно ли удалить связь с услугой (услуга обязательно должна быть связана с точкой оказания услуг или регионом)
        $service = Services::findFirstByServiceid($this->getServiceId());

        if($service->getRegionId() != null){
            return true;
        }

        $servicesPoints = ServicesPoints::findByServiceid($this->getServiceId());

        if(count($servicesPoints) > 1){
            return true;
        }
        return false;
    }

}
