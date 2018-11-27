<?php
use Phalcon\Validation;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\Callback;
class PhonesPoints extends \Phalcon\Mvc\Model
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
    protected $pointid;

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
     * Method to set the value of field pointId
     *
     * @param integer $pointid
     * @return $this
     */
    public function setPointId($pointid)
    {
        $this->pointid = $pointid;

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
     * Returns the value of field pointId
     *
     * @return integer
     */
    public function getPointId()
    {
        return $this->pointid;
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
            'pointid',
            new Callback(
                [
                    "message" => "Такая точка оказания услуг не существует",
                    "callback" => function($phonePoint) {
                        $point = TradePoints::findFirstByPointid($phonePoint->getPointId());

                        if($point)
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
        $this->setSource("phonesPoints");
        $this->belongsTo('phoneid', '\Phones', 'phoneid', ['alias' => 'Phones']);
        $this->belongsTo('pointid', '\TradePoints', 'pointid', ['alias' => 'TradePoints']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'phonesPoints';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return PhonesPoints[]|PhonesPoints|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return PhonesPoints|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public static function findByIds($pointId,$phoneId)
    {
        return PhonesPoints::findFirst(["pointid = :pointId: AND phoneid = :phoneId:",
            'bind' =>
                ['pointId' => $pointId,
                    'phoneId' => $phoneId
                ]]);
    }

    public static function getPhonesForPoint($pointId)
    {
        $modelsManager = Phalcon\DI::getDefault()->get('modelsManager');
        $result = $modelsManager->createBuilder()
            ->from(["p" => "Phones"])
            ->join('PhonesPoints','p.phoneid = pp.phoneid','pp')
            ->join('TradePoints', 'pp.pointid = tp.pointid', 'tp')
            ->where('tp.pointid = :pointId:',['pointId'=>$pointId])
            ->getQuery()
            ->execute();

        if(count($result) == 0){
            $point = TradePoints::findFirstByPointid($pointId);
            if($point->getSubjectType() == 1) {

                $result = $modelsManager->createBuilder()
                    ->from(["p" => "Phones"])
                    ->join('PhonesCompanies', 'p.phoneid = pc.phoneid', 'pc')
                    ->join('Companies', 'pc.companyid = c.companyid', 'c')
                    ->where('c.companyid = :companyId:', ['companyId' => $point->getSubjectId()])
                    ->getQuery()
                    ->execute();
            } else if($point->getSubjectType() == 0){
                $result = $modelsManager->createBuilder()
                    ->from(["p" => "Phones"])
                    ->join('Users', 'p.phoneid = u.phoneid', 'u')
                    ->where('u.userid = :userId:', ['userId' => $point->getSubjectId()])
                    ->getQuery()
                    ->execute();
            }
        }

        return $result;
    }
}
