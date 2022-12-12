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
     * @return string
     */
    public function getMultiplier(): string
    {
        return $this->Multiplier;
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