<?php
use Phalcon\Validation;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Callback;
class ImagesUsers extends \Phalcon\Mvc\Model
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
    protected $userid;

    /**
     *
     * @var string
     * @Column(type="string", length=256, nullable=false)
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
     * Returns the value of field userid
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userid;
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
            'userid',
            new Callback(
                [
                    "message" => "Такая услуга не существует",
                    "callback" => function ($image) {
                        $user = Users::findFirstByUserid($image->getUserId());
                        if ($user)
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
        $this->setSource("imagesusers");
        $this->belongsTo('userid', '\Users', 'userid', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'imagesusers';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImagesUsers[]|ImagesUsers|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ImagesUsers|\Phalcon\Mvc\Model\ResultInterface
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

            $userinfo = Userinfo::findFirstByUserid($this->getUserId());
            if($userinfo->getPathToPhoto() == $path){
                $userinfo->setPathToPhoto(null);

                $userinfo->update();
            }
        }

        return $result;
    }
}
