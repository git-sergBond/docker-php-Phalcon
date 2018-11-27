<?php
use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;

class FavoriteCategories extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $categoryid;

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
     * @Column(type="string", length=53, nullable=true)
     */
    protected $radius;

    /**
     * Method to set the value of field categoryid
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
     * Method to set the value of field radius
     *
     * @param string $radius
     * @return $this
     */
    public function setRadius($radius)
    {
        $this->radius = $radius;

        return $this;
    }

    /**
     * Returns the value of field categoryid
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->categoryid;
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
     * Returns the value of field radius
     *
     * @return string
     */
    public function getRadius()
    {
        return $this->radius;
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
                    "message" => "Пользователь подписчик не существует",
                    "callback" => function($favCompany) {
                        $user = Users::findFirstByUserid($favCompany->getUserId());
                        if($user)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'categoryid',
            new Callback(
                [
                    "message" => "Такая категория не существует",
                    "callback" => function($favCategory) {
                        //$company = Categories::findFirstByCompanyId($favCompany->getCompanyId());
                        if($favCategory->categories!=null)
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
        $this->setSource("favoriteCategories");
        $this->belongsTo('categoryid', '\Categories', 'categoryid', ['alias' => 'Categories']);
        $this->belongsTo('userid', '\Users', 'userid', ['alias' => 'Users']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'favoriteCategories';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return FavoriteCategories[]|FavoriteCategories|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return FavoriteCategories|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findByIds($userId, $categoryId)
    {
        return FavoriteCategories::findFirst(["userid = :userId: AND categoryid = :categoryId:",
            "bind" => [
                "userId" => $userId,
                "categoryId" => $categoryId,
            ]
        ]);
    }

}
