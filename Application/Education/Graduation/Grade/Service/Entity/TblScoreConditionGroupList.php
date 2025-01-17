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
 * @Table(name="tblGraduationScoreConditionGroupList")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreConditionGroupList extends Element
{
    const ATTR_TBL_SCORE_GROUP = 'tblGraduationScoreGroup';
    const ATTR_TBL_SCORE_CONDITION = 'tblGraduationScoreCondition';

    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationScoreGroup;
    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationScoreCondition;

    /**
     * @param TblScoreGroup $tblScoreGroup
     * @param TblScoreCondition $tblScoreCondition
     */
    public function __construct(TblScoreGroup $tblScoreGroup, TblScoreCondition $tblScoreCondition)
    {
        $this->tblGraduationScoreGroup = $tblScoreGroup->getId();
        $this->tblGraduationScoreCondition = $tblScoreCondition->getId();
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