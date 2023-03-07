<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblLessonStudentSubject")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentSubject extends Element
{
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_YEAR = 'serviceTblYear';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_SERVICE_TBL_SUBJECT_TABLE = 'serviceTblSubjectTable';
    const ATTR_TBL_DIVISION_COURSE = 'tblLessonDivisionCourse';
    const ATTR_HAS_GRADING = 'HasGrading';
    const ATTR_PERIOD_IDENTIFIER = 'PeriodIdentifier';

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblPerson;

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblYear;

    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblSubject = null;

    /**
     * @Column(type="boolean")
     */
    protected bool $HasGrading;

    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblSubjectTable = null;

    /**
     * @Column(type="bigint")
     */
    protected ?int $tblLessonDivisionCourse = null;

    /**
     * @Column(type="bigint")
     */
    protected ?string $PeriodIdentifier = null;

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject|null $tblSubject
     * @param bool $hasGrading
     * @param TblSubjectTable|null $tblSubjectTable
     * @param TblDivisionCourse|null $tblDivisionCourse
     * @param string|null $periodIdentifier
     *
     * @return TblStudentSubject
     */
    public static function withParameter(TblPerson $tblPerson, TblYear $tblYear, ?TblSubject $tblSubject, bool $hasGrading, ?TblSubjectTable $tblSubjectTable = null,
        ?TblDivisionCourse $tblDivisionCourse = null, ?string $periodIdentifier = null): TblStudentSubject
    {
        $instance = new self();

        $instance->setServiceTblPerson($tblPerson);
        $instance->setServiceTblYear($tblYear);
        $instance->setServiceTblSubject($tblSubject);
        $instance->setHasGrading($hasGrading);
        $instance->setServiceTblSubjectTable($tblSubjectTable);
        $instance->setTblDivisionCourse($tblDivisionCourse);
        $instance->setPeriodIdentifier($periodIdentifier);

        return  $instance;
    }

    /**
     * @param bool $IsForce
     *
     * @return false|TblPerson
     */
    public function getServiceTblPerson(bool $IsForce = false)
    {
        return Person::useService()->getPersonById($this->serviceTblPerson, $IsForce);
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson)
    {
        $this->serviceTblPerson = $tblPerson->getId();
    }

    /**
     * @return false|TblYear
     */
    public function getServiceTblYear()
    {
        return Term::useService()->getYearById($this->serviceTblYear);
    }

    /**
     * @param TblYear $tblYear
     */
    public function setServiceTblYear(TblYear $tblYear)
    {
        $this->serviceTblYear = $tblYear->getId();
    }

    /**
     * @return false|TblSubject
     */
    public function getServiceTblSubject()
    {
        // SekI
        if ($this->serviceTblSubject) {
            return Subject::useService()->getSubjectById($this->serviceTblSubject);
        }
        // SekII
        if (($tblDivisionCourse = $this->getTblDivisionCourse())) {
            return $tblDivisionCourse->getServiceTblSubject();
        }

        return false;
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubject(?TblSubject $tblSubject)
    {
        $this->serviceTblSubject = $tblSubject ? $tblSubject->getId() : null;
    }

    /**
     * @return bool
     */
    public function getHasGrading(): bool
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
     * @return false|TblSubjectTable
     */
    public function getServiceTblSubjectTable()
    {
        return $this->serviceTblSubjectTable ? DivisionCourse::useService()->getSubjectTableById($this->serviceTblSubjectTable) : false;
    }

    /**
     * @param TblSubjectTable|null $tblSubjectTable
     */
    public function setServiceTblSubjectTable(?TblSubjectTable $tblSubjectTable): void
    {
        $this->serviceTblSubjectTable = $tblSubjectTable ? $tblSubjectTable->getId() : null;
    }

    /**
     * @return false|TblDivisionCourse
     */
    public function getTblDivisionCourse()
    {
        return $this->tblLessonDivisionCourse ? DivisionCourse::useService()->getDivisionCourseById($this->tblLessonDivisionCourse) : false;
    }

    /**
     * @param ?TblDivisionCourse $tblDivisionCourse
     */
    public function setTblDivisionCourse(?TblDivisionCourse $tblDivisionCourse): void
    {
        $this->tblLessonDivisionCourse = $tblDivisionCourse ? $tblDivisionCourse->getId() : null;
    }

    /**
     * @return string|null
     */
    public function getPeriodIdentifier(): ?string
    {
        return $this->PeriodIdentifier;
    }

    /**
     * @param string|null $PeriodIdentifier
     */
    public function setPeriodIdentifier(?string $PeriodIdentifier): void
    {
        $this->PeriodIdentifier = $PeriodIdentifier;
    }

    /**
     * Für die Sortierung der Fächer
     *
     * @return string
     */
    public function getSort(): string
    {
        return ($tblSubject = $this->getServiceTblSubject()) ? $tblSubject->getAcronym() : '';
    }

    /**
     * @return bool
     */
    public function getIsAdvancedCourse(): bool
    {
        return ($tblDivisionCourse = $this->getTblDivisionCourse())
            && ($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_ADVANCED_COURSE);
    }
}