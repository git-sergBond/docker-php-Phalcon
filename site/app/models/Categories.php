<?php
use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
class Categories extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $categoryid;

    /**
     *
     * @var string
     * @Column(type="string", length=45, nullable=false)
     */
    protected $categoryname;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $parentid;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     *
     * @var string
     * @Column(type="string", length=260, nullable=true)
     */
    protected $img;

    /**
     * Method to set the value of field categoryId
     *
     * @param integer $categoryid
     * @return $this
     */
    public function setCategoryId($categoryid)
    {
        $this->categoryid = $categoryid;

        return $this;
    }

    /**
     * Method to set the value of field categoryName
     *
     * @param string $categoryname
     * @return $this
     */
    public function setCategoryName($categoryname)
    {
        $this->categoryname = $categoryname;

        return $this;
    }

    /**
     * Method to set the value of field parentId
     *
     * @param integer $parentid
     * @return $this
     */
    public function setParentId($parentid)
    {
        $this->parentid = $parentid;

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
     * Method to set the value of field img
     *
     * @param string $img
     * @return $this
     */
    public function setImg($img)
    {
        $this->img = $img;

        return $this;
    }

    /**
     * Returns the value of field categoryId
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->categoryid;
    }

    /**
     * Returns the value of field categoryName
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->categoryname;
    }

    /**
     * Returns the value of field parentId
     *
     * @return integer
     */
    public function getParentId()
    {
        return $this->parentid;
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
     * Returns the value of field img
     *
     * @return string
     */
    public function getImg()
    {
        return $this->img;
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        if($this->getParentId()!= null)
        $validator->add(
            'parentid',
            new Callback(
                [
                    "message" => "Родительская категория не существует",
                    "callback" => function ($category) {
                        $categoryParent = Categories::findFirstByCategoryid($category->getParentId());

                        if ($categoryParent)
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
        //$this->setSchema("public");
        $this->setSource("categories");
        $this->hasMany('categoryId', 'Tasks', 'categoryId', ['alias' => 'Tasks']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'categories';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Categories[]|Categories|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Categories|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
