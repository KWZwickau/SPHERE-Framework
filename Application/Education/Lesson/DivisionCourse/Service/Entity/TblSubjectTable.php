<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity;

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
 * @Table(name="tblLessonSubjectTable")
 * @Cache(usage="READ_ONLY")
 */
class TblSubjectTable extends Element
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
     * @Column(type="string")
     */
    protected string $TypeName;

    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblSubject = null;

    /**
     * @Column(type="string")
     */
    protected string $StudentMetaIdentifier;

    /**
     * @Column(type="boolean")
     */
    protected bool $HasGrading;

    /**
     * @Column(type="integer")
     */
    protected ?int $HoursPerWeek  = null;

    /**
     * @param TblType $tblSchoolType
     * @param int $level
     * @param TblSubject|null $tblSubject
     * @param string $typeName
     * @param int|null $hoursPerWeek
     * @param string $studentMetaIdentifier
     * @param bool $hasGrading
     *
     * @return TblSubjectTable
     */
    public static function withParameter(TblType $tblSchoolType, int $level, ?TblSubject $tblSubject, string $typeName, ?int $hoursPerWeek,
        string $studentMetaIdentifier = '', bool $hasGrading = true): TblSubjectTable
    {
        $instance = new self();

        $instance->setServiceTblSchoolType($tblSchoolType);
        $instance->setLevel($level);
        $instance->setServiceTblSubject($tblSubject);
        $instance->setTypeName($typeName);
        $instance->setHoursPerWeek($hoursPerWeek);
        $instance->setStudentMetaIdentifier($studentMetaIdentifier);
        $instance->setHasGrading($hasGrading);

        return  $instance;
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
    public function getTypeName(): string
    {
        return $this->TypeName;
    }

    /**
     * @param string $TypeName
     */
    public function setTypeName(string $TypeName): void
    {
        $this->TypeName = $TypeName;
    }

    /**
     * @return false|TblSubject
     */
    public function getServiceTblSubject()
    {
        return Subject::useService()->getSubjectById($this->serviceTblSubject);
    }

    /**
     * @param ?TblSubject $tblSubject
     */
    public function setServiceTblSubject(?TblSubject $tblSubject)
    {
        $this->serviceTblSubject = $tblSubject ? $tblSubject->getId() : null;
    }

    /**
     * @return string
     */
    public function getStudentMetaIdentifier(): string
    {
        return $this->StudentMetaIdentifier;
    }

    /**
     * @param string $StudentMetaIdentifier
     */
    public function setStudentMetaIdentifier(string $StudentMetaIdentifier): void
    {
        $this->StudentMetaIdentifier = $StudentMetaIdentifier;
    }

    /**
     * @return bool
     */
    public function isHasGrading(): bool
    {
        return $this->HasGrading;
    }

    /**
     * @param bool $HasGrading
     */
    public function setHasGrading(bool $HasGrading): void
    {
        $this->HasGrading = $HasGrading;
    }

    /**
     * @return int|null
     */
    public function getHoursPerWeek(): ?int
    {
        return $this->HoursPerWeek;
    }

    /**
     * @param int|null $HoursPerWeek
     */
    public function setHoursPerWeek(?int $HoursPerWeek): void
    {
        $this->HoursPerWeek = $HoursPerWeek;
    }
}