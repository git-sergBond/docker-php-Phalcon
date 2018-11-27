<?php
use Phalcon\Validation;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Callback;
class ImagesReviews extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $imageid;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $reviewid;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
     */
    protected $imagepath;

    const MAX_IMAGES = 3;
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
     * Method to set the value of field reviewid
     *
     * @param integer $reviewid
     * @return $this
     */
    public function setReviewId($reviewid)
    {
        $this->reviewid = $reviewid;

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
     * Returns the value of field reviewid
     *
     * @return integer
     */
    public function getReviewId()
    {
        return $this->reviewid;
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
            'reviewid',
            new Callback(
                [
                    "message" => "Такой отзыв не существует",
                    "callback" => function ($image) {
                        $service = Reviews::findFirstByReviewid($image->getReviewId());
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
        $this->setSchema("public");
        $this->setSource("imagesreviews");
        $this->belongsTo('reviewid', '\Reviews', 'reviewid', ['alias' => 'Reviews']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'imagesreviews';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImagesReviews[]|ImagesReviews|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImagesReviews|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function delete($delete = false, $data = null, $whiteList = null)
    {
        $path = $this->getImagePath();

        $result = parent::delete($delete, false, $data, $whiteList);

        if ($result && $path != null && $delete = true) {
            ImageLoader::delete($path);
        }
        return $result;
    }
}
