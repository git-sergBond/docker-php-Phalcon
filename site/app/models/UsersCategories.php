<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
use Phalcon\Validation\Validator\PresenceOf;
class UsersCategories extends \Phalcon\Mvc\Model
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
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $categoryid;

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
     * Returns the value of field userid
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userid;
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
                    "message" => "Пользователь не существует",
                    "callback" => function($userCat) {
                        $user = Users::findFirstByUserid($userCat->getUserId());
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
                    "callback" => function($userCat) {
                        //$company = Categories::findFirstByCompanyId($userCat->getCategoryId());
                        if($userCat->categories!=null)
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
        $this->setSource("userscategories");
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
        return 'userscategories';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return UsersCategories[]|UsersCategories|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return UsersCategories|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function getCategoriesByUser($userId)
    {
        $db = Phalcon\DI::getDefault()->getDb();

        $query = $db->prepare('SELECT c.* FROM categories c INNER JOIN "userscategories" u_c  
            USING(categoryid) where u_c.userid = :userId'
        );

        $query->execute([
            'userId' => $userId,
        ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
}
