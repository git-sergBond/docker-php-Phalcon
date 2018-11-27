<?php

class Regions extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $regionid;

    /**
     *
     * @var string
     * @Column(type="string", length=70, nullable=false)
     */
    protected $regionname;

    /**
     * Method to set the value of field regionId
     *
     * @param integer $regionid
     * @return $this
     */
    public function setRegionId($regionid)
    {
        $this->regionid = $regionid;

        return $this;
    }

    /**
     * Method to set the value of field regionName
     *
     * @param string $regionname
     * @return $this
     */
    public function setRegionName($regionname)
    {
        $this->regionname = $regionname;

        return $this;
    }

    /**
     * Returns the value of field regionId
     *
     * @return integer
     */
    public function getRegionId()
    {
        return $this->regionid;
    }

    /**
     * Returns the value of field regionName
     *
     * @return string
     */
    public function getRegionName()
    {
        return $this->regionname;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("job");
        $this->setSource("regions");
        $this->hasMany('regionid', 'Companies', 'regionid', ['alias' => 'Companies']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'regions';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Regions[]|Regions|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Regions|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
