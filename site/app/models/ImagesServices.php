<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\Url as UrlValidator;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Callback;

class ImagesServices extends \Phalcon\Mvc\Model
{
    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $imageid;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $serviceid;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=true)
     */
    protected $imagepath;

    const MAX_IMAGES = 10;

    /**
     * Method to set the value of field imageid
     *
     * @param integer $imageid
     * @return $this
     */
    public function setImageId($imageid)
    {
        $this->imageid = $imageid;

        return $this;
    }

    /**
     * Method to set the value of field serviceid
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
     * Method to set the value of field imagepath
     *
     * @param string $imagepath
     * @return $this
     */
    public function setImagePath($imagepath)
    {
        $this->imagepath = $imagepath;

        return $this;
    }

    /**
     * Returns the value of field imageid
     *
     * @return integer
     */
    public function getImageId()
    {
        return $this->imageid;
    }

    /**
     * Returns the value of field serviceid
     *
     * @return integer
     */
    public function getServiceId()
    {
        return $this->serviceid;
    }

    /**
     * Returns the value of field imagepath
     *
     * @return string
     */
    public function getImagePath()
    {
        return $this->imagepath;
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
                    "callback" => function ($image) {
                        $service = Services::findFirstByServiceid($image->getServiceId());
                        if ($service)
                            return true;
                        return false;
                    }
                ]
            )
        );


        $validator->add(
            'imagepath',
            new Callback(
                [
                    "message" => "Формат не поддерживается",
                    "callback" => function ($image) {
                        $format = pathinfo($image->getImagePath(), PATHINFO_EXTENSION);

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


        return $this->validate($validator);
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("public");
        $this->setSource("imagesservices");
        $this->belongsTo('serviceid', '\Services', 'serviceid', ['alias' => 'Services']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'imagesservices';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImagesServices[]|ImagesServices|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImagesServices|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function save($data = null, $whiteList = null)
    {
        $result = parent::save($data, $whiteList);
        return $result;
    }

    public function delete($data = null, $whiteList = null)
    {
        $path = $this->getImagePath();

        $result = parent::delete($data, $whiteList);

        if ($result && $path != null) {
            ImageLoader::delete($path);
        }
        return $result;
    }
}
