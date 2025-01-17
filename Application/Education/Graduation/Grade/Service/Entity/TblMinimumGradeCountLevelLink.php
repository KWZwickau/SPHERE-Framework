<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGraduationMinimumGradeCountLevelLink")
 * @Cache(usage="READ_ONLY")
 */
class TblMinimumGradeCountLevelLink extends Element
{
    const ATTR_TBL_MINIMUM_GRADE_COUNT = 'tblGraduationMinimumGradeCount';
    const ATTR_SERVICE_TBL_SCHOOL_TYPE = 'serviceTblSchoolType';
    const ATTR_LEVEL = 'Level';

    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationMinimumGradeCount;
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblSchoolType;
    /**
     * @Column(type="integer")
     */
    protected int $Level;

    /**
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     * @param TblType $tblSchoolType
     * @param int $Level
     */
    public function __construct(TblMinimumGradeCount $tblMinimumGradeCount, TblType $tblSchoolType, int $Level)
    {
        $this->tblGraduationMinimumGradeCount = $tblMinimumGradeCount->getId();
        $this->serviceTblSchoolType = $tblSchoolType->getId();
        $this->Level = $Level;
    }

    /**
     * @return TblMinimumGradeCount|false
     */
    public function getMinimumGradeCount()
    {
        return Grade::useService()->getMinimumGradeCountById($this->tblGraduationMinimumGradeCount);
    }

    /**
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     */
    public function setMinimumGradeCount(TblMinimumGradeCount $tblMinimumGradeCount)
    {
        $this->tblGraduationMinimumGradeCount = $tblMinimumGradeCount->getId();
    }

    /**
     * @return false|TblType
     */
    public function getServiceTblSchoolType()
    {
        return Type::useService()->getTypeById($this->serviceTblSchoolType);
    }

    /**
     * @param TblType $tblSchoolType
     */
    public function setServiceTblSchoolType(TblType $tblSchoolType): void
    {
        $this->serviceTblSchoolType = $tblSchoolType->getId();
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->Level;
    }

    /**
     * @param int $Level
     */
    public function setLevel(int $Level): void
    {
        $this->Level = $Level;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        if (($tblSchoolType = $this->getServiceTblSchoolType())) {
            $typeName = $tblSchoolType->getName();
            if ($typeName == 'Grundschule') {
                $typeName = 'GS';
            } elseif ($typeName == TblType::IDENT_OBER_SCHULE) {
                $typeName = 'OS';
            } elseif ($typeName == 'Gymnasium') {
                $typeName = 'GYM';
            }

            return $this->getLevel() . ' (' . $typeName . ')';
        }

        return '-NA-';
    }
}