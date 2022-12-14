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
 * @Table(name="tblGraduationScoreGroupGradeTypeList")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreGroupGradeTypeList extends Element
{
    const ATTR_TBL_GRADE_TYPE = 'tblGraduationGradeType';
    const ATTR_TBL_SCORE_GROUP = 'tblGraduationScoreGroup';

    /**
     * @Column(type="string")
     */
    protected string $Multiplier = '';
    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationGradeType;
    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationScoreGroup;

    /**
     * @param string $multiplier
     * @param TblGradeType $tblGradeType
     * @param TblScoreGroup $tblScoreGroup
     */
    public function __construct(string $multiplier, TblGradeType $tblGradeType, TblScoreGroup $tblScoreGroup)
    {
        $this->Multiplier = $multiplier;
        $this->tblGraduationGradeType = $tblGradeType->getId();
        $this->tblGraduationScoreGroup = $tblScoreGroup->getId();
    }

    /**
     * @return string
     */
    public function getMultiplier(): string
    {
        return $this->Multiplier;
    }

    /**
     * @return string
     */
    public function getDisplayMultiplier(): string
    {
        return str_replace('.', ',', $this->Multiplier);
    }

    /**
     * @param string $Multiplier
     */
    public function setMultiplier(string $Multiplier): void
    {
        $this->Multiplier = $Multiplier;
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
}