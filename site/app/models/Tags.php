<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\StringLength as StringLength;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;
use Phalcon\Validation\Validator\Alpha as AlphaValidator;
use Phalcon\Validation\Validator\Callback;

class Tags extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $tagid;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    protected $tag;

    /**
     * Method to set the value of field tagid
     *
     * @param integer $tagid
     * @return $this
     */
    public function setTagId($tagid)
    {
        $this->tagid = $tagid;

        return $this;
    }

    /**
     * Method to set the value of field tag
     *
     * @param string $tag
     * @return $this
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Returns the value of field tagid
     *
     * @return integer
     */
    public function getTagId()
    {
        return $this->tagid;
    }

    /**
     * Returns the value of field tag
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
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
            "tag",
            new StringLength(
                [
                    "max"            => 100,
                    "min"            => 3,
                    "messageMaximum" => "We don't like really long tags",
                    "messageMinimum" => "It's really too few for tag",
                ]
            )
        );

        $validator->add(
            "tag",
            new AlphaValidator(
                [
                    "message" => ":field must contain only letters",
                ]
            )
        );

        $validator->add(
            "tag",
            new UniquenessValidator(
                [
                    "model"   => new Tags(),
                    "message" => ":field must be unique",
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
        $this->setSource("tags");
        $this->hasMany('tagid', 'ServicesTags', 'tagid', ['alias' => 'ServicesTags']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'tags';
    }

    public function save($data = null, $whiteList = null)
    {
        $this->setTag(mb_strtolower($this->getTag()));
        $tag = Tags::findFirstByTag($this->getTag());
        if($tag){
            $this->setTag($tag->getTag());
            $this->setTagId($tag->getTagId());
            return true;
        } else {
            $result = parent::save($data, $whiteList);
            return $result;
        }
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Tags[]|Tags|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Tags|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
