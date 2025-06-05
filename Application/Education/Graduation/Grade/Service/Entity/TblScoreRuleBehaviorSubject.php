<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblGraduationScoreRuleBehaviorSubject")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreRuleBehaviorSubject extends Element
{
    const ATTR_SERVICE_TBL_SCHOOL_TYPE = 'serviceTblSchoolType';
    const ATTR_LEVEL = 'Level';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblSchoolType;
    /**
     * @Column(type="integer")
     */
    protected int $Level;
    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblSubject;

    /**
     * @Column(type="string")
     */
    protected string $Multiplier = '';

    /**
     * @param TblType $tblSchoolType
     * @param int $level
     * @param TblSubject|null $tblSubject
     * @param string $multiplier
     */
    public function __construct(TblType $tblSchoolType, int $level, ?TblSubject $tblSubject, string $multiplier)
    {
        $this->serviceTblSchoolType = $tblSchoolType->getId();
        $this->Level = $level;
        $this->serviceTblSubject = $tblSubject ? $tblSubject->getId() : null;
        $this->Multiplier = $multiplier;
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
     * @return false|TblSubject
     */
    public function getServiceTblSubject()
    {
        return Subject::useService()->getSubjectById($this->serviceTblSubject);
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubject(?TblSubject $tblSubject)
    {
        $this->serviceTblSubject = $tblSubject ? $tblSubject->getId() : null;
    }

    /**
     * @return string
     */
    public function getMultiplier(): string
    {
        return $this->Multiplier;
    }

    /**
     * @return float|null
     */
    public function getMultiplierValue() : ?float
    {
        $multiplier = str_replace(',', '.', $this->getMultiplier());

        return is_numeric($multiplier) ? (float) $multiplier : null;
    }

    /**
     * @param string $Multiplier
     */
    public function setMultiplier(string $Multiplier): void
    {
        $this->Multiplier = $Multiplier;
    }

    /**
     * @return string
     */
    public function getSort(): string
    {
        if (($tblSubject = $this->getServiceTblSubject())) {
            return $tblSubject->getAcronym();
        } else {
            return '';
        }
    }
}