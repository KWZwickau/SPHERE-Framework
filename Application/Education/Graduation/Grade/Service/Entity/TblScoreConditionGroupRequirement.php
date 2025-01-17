<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGraduationScoreConditionGroupRequirement")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreConditionGroupRequirement extends Element
{
    const ATTR_TBL_SCORE_GROUP = 'tblGraduationScoreGroup';
    const ATTR_TBL_SCORE_CONDITION = 'tblGraduationScoreCondition';

    /**
     * @Column(type="integer")
     */
    protected int $Count;
    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationScoreGroup;
    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationScoreCondition;

    /**
     * @param int $count
     * @param TblScoreGroup $tblScoreGroup
     * @param TblScoreCondition $tblScoreCondition
     */
    public function __construct(int $count, TblScoreGroup $tblScoreGroup, TblScoreCondition $tblScoreCondition)
    {
        $this->Count = $count;
        $this->tblGraduationScoreGroup = $tblScoreGroup->getId();
        $this->tblGraduationScoreCondition = $tblScoreCondition->getId();
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->Count;
    }

    /**
     * @param int $Count
     */
    public function setCount(int $Count): void
    {
        $this->Count = $Count;
    }

    /**
     * @return TblScoreGroup|false
     */
    public function getTblScoreGroup()
    {
        return Grade::useService()->getScoreGroupById($this->tblGraduationScoreGroup);
    }

    /**
     * @param TblScoreGroup $tblScoreGroup
     */
    public function setTblScoreGroup(TblScoreGroup $tblScoreGroup)
    {
        $this->tblGraduationScoreGroup = $tblScoreGroup->getId();
    }

    /**
     * @return TblScoreCondition|false
     */
    public function getTblScoreCondition()
    {
        return Grade::useService()->getScoreConditionById($this->tblGraduationScoreCondition);
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     */
    public function setTblScoreCondition(TblScoreCondition $tblScoreCondition)
    {
        $this->tblGraduationScoreCondition = $tblScoreCondition->getId();
    }
}