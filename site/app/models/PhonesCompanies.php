<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\Callback;
class PhonesCompanies extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $phoneid;

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $companyid;

    /**
     * Method to set the value of field phoneId
     *
     * @param integer $phoneid
     * @return $this
     */
    public function setPhoneId($phoneid)
    {
        $this->phoneid = $phoneid;

        return $this;
    }

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
     * Returns the value of field phoneId
     *
     * @return integer
     */
    public function getPhoneId()
    {
        return $this->phoneid;
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
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'phoneid',
            new Callback(
                [
                    "message" => "Телефон не был создан",
                    "callback" => function($phoneCompany) {
                        $phone = Phones::findFirstByPhoneid($phoneCompany->getPhoneId());

                        if($phone)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'companyid',
            new Callback(
                [
                    "message" => "Такая компания не существует",
                    "callback" => function($phoneCompany) {
                        $phone = Companies::findFirstByCompanyid($phoneCompany->getCompanyId());

                        if($phone)
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
        //$this->setSchema("job");
        $this->setSource("phonesCompanies");
        $this->belongsTo('companyid', '\Companies', 'companyid', ['alias' => 'Companies']);
        $this->belongsTo('phoneid', '\Phones', 'phoneid', ['alias' => 'Phones']);
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return PhonesCompanies[]|PhonesCompanies|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return PhonesCompanies|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findByIds($companyId, $phoneId)
    {
        return PhonesCompanies::findFirst(["companyid = :companyId: AND phoneid = :phoneId:",
            'bind' =>
                ['companyId' => $companyId,
                    'phoneId' => $phoneId
                ]]);
    }

    public static function getCompanyPhones($companyId)
    {
        $db = Phalcon\DI::getDefault()->getDb();

        $query = $db->prepare('SELECT p.phone FROM "phonesCompanies" p_c INNER JOIN phones p ON 
            (p_c.phoneid = p.phoneid) where p_c.companyid = :companyId'
        );

        $query->execute([
            'companyId' => $companyId,
        ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'phonesCompanies';
    }

}
