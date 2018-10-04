<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 14.09.2018
 * Time: 10:00
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblScoreConditionGroupRequirement")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreConditionGroupRequirement extends Element
{

    const ATTR_TBL_SCORE_GROUP = 'tblScoreGroup';
    const ATTR_TBL_SCORE_CONDITION = 'tblScoreCondition';

    /**
     * @Column(type="bigint")
     */
    protected $tblScoreGroup;

    /**
     * @Column(type="bigint")
     */
    protected $tblScoreCondition;

    /**
     * @Column(type="integer")
     */
    protected $Count;

    /**
     * @return bool|TblScoreGroup
     */
    public function getTblScoreGroup()
    {

        if (null === $this->tblScoreGroup) {
            return false;
        } else {
            return Gradebook::useService()->getScoreGroupById($this->tblScoreGroup);
        }
    }

    /**
     * @param TblScoreGroup|null $tblScoreGroup
     */
    public function setTblScoreGroup($tblScoreGroup)
    {

        $this->tblScoreGroup = ( null === $tblScoreGroup ? null : $tblScoreGroup->getId() );
    }

    /**
     * @return bool|TblScoreCondition
     */
    public function getTblScoreCondition()
    {

        if (null === $this->tblScoreCondition) {
            return false;
        } else {
            return Gradebook::useService()->getScoreConditionById($this->tblScoreCondition);
        }
    }

    /**
     * @param TblScoreCondition|null $tblScoreCondition
     */
    public function setTblScoreCondition($tblScoreCondition)
    {

        $this->tblScoreCondition = ( null === $tblScoreCondition ? null : $tblScoreCondition->getId() );
    }

    /**
     * @return integer
     */
    public function getCount()
    {
        return $this->Count;
    }

    /**
     * @param integer $Count
     */
    public function setCount($Count)
    {
        $this->Count = $Count;
    }
}