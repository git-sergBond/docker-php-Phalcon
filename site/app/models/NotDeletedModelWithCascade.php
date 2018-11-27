<?php

class NotDeletedModelWithCascade extends \Phalcon\Mvc\Model
{
    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $deleted;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $deletedcascade;

    /**
     * Method to set the value of field deleted
     *
     * @param string $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Returns the value of field deleted
     *
     * @return string
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Method to set the value of field deleted
     *
     * @param string $deletedcascade
     * @return $this
     */
    public function setDeletedCascade($deletedcascade)
    {
        $this->deletedcascade = $deletedcascade;

        return $this;
    }

    /**
     * Returns the value of field deleted
     *
     * @return string
     */
    public function getDeletedCascade()
    {
        return $this->deletedcascade;
    }

    public function delete($delete = false, $deletedCascade = false, $data = null, $whiteList = null)
    {
        if (!$delete) {
            $this->setDeleted(true);
            if($deletedCascade){
                $this->setDeletedCascade(true);
            } else{
                $this->setDeletedCascade(false);
            }
            return $this->save();
        } else {
            $result = parent::delete($data, $whiteList);
            return $result;
        }
    }

    public function restore()
    {
        $this->setDeleted(false);
        $this->setDeletedCascade(false);
        return $this->save();
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @$addParamNotDeleted - по умолчанию ищутся только те записи, что не помечены, как удаленные
     * @return TradePoints[]|TradePoints|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null, $addParamNotDeleted = true)
    {
        if ($addParamNotDeleted) {
            if(isset($parameters['conditions']))
                $conditions = $parameters['conditions'];
            else if(isset($parameters[0]))
                $conditions = $parameters[0];
            else
                $conditions = "";
            if ($conditions!= null && trim($conditions) != "") {
                $conditions .= ' AND deleted != true';
            }else{
                if($conditions!= null)
                    $conditions .= 'deleted != true';
                else
                    $conditions = 'deleted != true';
            }

            if(isset($parameters['conditions']))
                $parameters['conditions'] = $conditions;
            else
                $parameters[0] = $conditions;
        }
        $result = parent::find($parameters);

        return $result;
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @$addParamNotDeleted - по умолчанию ищутся только те записи, что не помечены, как удаленные
     * @return TradePoints|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null, $addParamNotDeleted = true)
    {
        if ($addParamNotDeleted) {
            if(isset($parameters['conditions']))
                $conditions = $parameters['conditions'];
            else if(isset($parameters[0]))
                $conditions = $parameters[0];
            else
                $conditions = "";
            if ($conditions!= null && trim($conditions) != "") {
                $conditions .= ' AND deleted != true';
            }else{
                if($conditions!= null)
                    $conditions .= 'deleted != true';
                else
                    $conditions = 'deleted != true';

            }
            if(isset($parameters['conditions']))
                $parameters['conditions'] = $conditions;
            else
                $parameters[0] = $conditions;
        }

        return parent::findFirst($parameters);
    }
}