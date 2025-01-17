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
 * @Table(name="tblGraduationScoreConditionGradeTypeList")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreConditionGradeTypeList extends Element
{
    const ATTR_TBL_GRADE_TYPE = 'tblGraduationGradeType';
    const ATTR_TBL_SCORE_CONDITION = 'tblGraduationScoreCondition';

    /**
     * @Column(type="integer")
     */
    protected int $Count;
    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationGradeType;
    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationScoreCondition;

    /**
     * @param int $count
     * @param TblGradeType $tblGradeType
     * @param TblScoreCondition $tblScoreCondition
     */
    public function __construct(int $count, TblGradeType $tblGradeType, TblScoreCondition $tblScoreCondition)
    {
        $this->Count = $count;
        $this->tblGraduationGradeType = $tblGradeType->getId();
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
     * @return TblGradeType|false
     */
    public function getTblGradeType()
    {
        return Grade::useService()->getGradeTypeById($this->tblGraduationGradeType);
    }

    /**
     * @param TblGradeType $tblGradeType
     */
    public function setTblGradeType(TblGradeType $tblGradeType)
    {
        $this->tblGraduationGradeType = $tblGradeType->getId();
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