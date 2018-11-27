<?php

class CompaniesCategories extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $companyid;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $categoryid;

    /**
     * Method to set the value of field companyId
     *
     * @param integer $companyid
     * @return $this
     */
    public function setCompanyId($companyid)
    {
        $this->companyid = $companyid;

        return $this;
    }

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
     * Returns the value of field companyId
     *
     * @return integer
     */
    public function getCompanyId()
    {
        return $this->companyid;
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
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("public");
        $this->setSource("companiesCategories");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'companiesCategories';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return CompaniesCategories[]|CompaniesCategories|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return CompaniesCategories|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function getCategoriesByCompany($companyId)
    {
        $db = Phalcon\DI::getDefault()->getDb();

        $query = $db->prepare('SELECT c.* FROM categories c INNER JOIN "companiesCategories" c_c  
            USING(categoryid) where c_c.companyid = :companyId'
        );

        $query->execute([
            'companyId' => $companyId,
        ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
}
