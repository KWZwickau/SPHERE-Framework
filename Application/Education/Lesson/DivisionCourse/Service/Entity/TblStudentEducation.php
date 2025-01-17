<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblLessonStudentEducation")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentEducation extends Element
{
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_YEAR = 'serviceTblYear';
    const ATTR_SERVICE_TBL_COMPANY = 'serviceTblCompany';
    const ATTR_SERVICE_TBL_SCHOOL_TYPE = 'serviceTblSchoolType';
    const ATTR_LEVEL = 'Level';
    const ATTR_LEAVE_DATE = 'LeaveDate';
    const ATTR_TBL_DIVISION = 'tblDivision';
    const ATTR_TBL_CORE_GROUP = 'tblCoreGroup';

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
    protected ?int $serviceTblCompany = null;

    /**
     * @Column(type="integer")
     */
    protected ?int $Level = null;

    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblSchoolType = null;

    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblCourse = null;

    /**
     * @Column(type="bigint")
     */
    protected ?int $tblDivision = null;

    /**
     * @Column(type="integer")
     */
    protected ?int $DivisionSortOrder = null;

    /**
     * @Column(type="bigint")
     */
    protected ?int $tblCoreGroup = null;

    /**
     * @Column(type="integer")
     */
    protected ?int $CoreGroupSortOrder = null;

    /**
     * @Column(type="datetime")
     */
    protected ?DateTime $LeaveDate = null;

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
     * @return false|TblCompany
     */
    public function getServiceTblCompany()
    {
        return $this->serviceTblCompany ? Company::useService()->getCompanyById($this->serviceTblCompany) : false;
    }

    /**
     * @param ?TblCompany $tblCompany
     */
    public function setServiceTblCompany(?TblCompany $tblCompany): void
    {
        $this->serviceTblCompany = $tblCompany ? $tblCompany->getId() : null;
    }

    /**
     * @return int|null
     */
    public function getLevel(): ?int
    {
        return $this->Level;
    }

    /**
     * @param int|null $Level
     */
    public function setLevel(?int $Level): void
    {
        $this->Level = $Level;
    }

    /**
     * @return false|TblType
     */
    public function getServiceTblSchoolType()
    {
        return $this->serviceTblSchoolType ? Type::useService()->getTypeById($this->serviceTblSchoolType) : false;
    }

    /**
     * @param TblType|null $tblSchoolType
     */
    public function setServiceTblSchoolType(?TblType $tblSchoolType): void
    {
        $this->serviceTblSchoolType = $tblSchoolType ? $tblSchoolType->getId() : null;
    }

    /**
     * @return false|TblCourse
     */
    public function getServiceTblCourse()
    {
        return $this->serviceTblCourse ? Course::useService()->getCourseById($this->serviceTblCourse) : false;
    }

    /**
     * @param TblCourse|null $tblCourse
     */
    public function setServiceTblCourse(?TblCourse $tblCourse): void
    {
        $this->serviceTblCourse = $tblCourse ? $tblCourse->getId() : null;
    }

    /**
     * @return false|TblDivisionCourse
     */
    public function getTblDivision()
    {
        return $this->tblDivision ? DivisionCourse::useService()->getDivisionCourseById($this->tblDivision) : false;
    }

    /**
     * @param ?TblDivisionCourse $tblDivision
     */
    public function setTblDivision(?TblDivisionCourse $tblDivision): void
    {
        $this->tblDivision = $tblDivision ? $tblDivision->getId() : null;
    }

    /**
     * @return int|null
     */
    public function getDivisionSortOrder(): ?int
    {
        return $this->DivisionSortOrder;
    }

    /**
     * @param int|null $DivisionSortOrder
     */
    public function setDivisionSortOrder(?int $DivisionSortOrder): void
    {
        $this->DivisionSortOrder = $DivisionSortOrder;
    }

    /**
     * @return false|TblDivisionCourse
     */
    public function getTblCoreGroup()
    {
        return $this->tblCoreGroup ? DivisionCourse::useService()->getDivisionCourseById($this->tblCoreGroup) : false;
    }

    /**
     * @param ?TblDivisionCourse $tblCoreGroup
     */
    public function setTblCoreGroup(?TblDivisionCourse $tblCoreGroup): void
    {
        $this->tblCoreGroup = $tblCoreGroup ? $tblCoreGroup->getId() : null;
    }

    /**
     * @return int|null
     */
    public function getCoreGroupSortOrder(): ?int
    {
        return $this->CoreGroupSortOrder;
    }

    /**
     * @param int|null $CoreGroupSortOrder
     */
    public function setCoreGroupSortOrder(?int $CoreGroupSortOrder): void
    {
        $this->CoreGroupSortOrder = $CoreGroupSortOrder;
    }

    /**
     * @return string
     */
    public function getLeaveDate(): string
    {
        return $this->LeaveDate instanceof DateTime ? $this->LeaveDate->format('d.m.Y') : '';
    }

    /**
     * @param null|DateTime $Date
     */
    public function setLeaveDate(DateTime $Date = null)
    {
        $this->LeaveDate = $Date;
    }

    /**
     * @return ?DateTime
     */
    public function getLeaveDateTime(): ?DateTime
    {
        return $this->LeaveDate;
    }

    /**
     * nicht entfernen, nur für Sortierung verwenden
     *
     * @return string
     */
    public function getYearNameForSorter(): string
    {
        if (($tblYear = $this->getServiceTblYear())) {
            return $tblYear->getDisplayName() . ' ' .  ($this->getLeaveDateTime() ? $this->getLeaveDateTime()->format('Y.m.d') : 'zzz');
        }

        return '';
    }

    /**
     * @return bool
     */
    public function isInActive(): bool
    {
        return $this->isInActiveByDateTime(new DateTime('now'));
    }

    /**
     * @param DateTime $dateTime
     *
     * @return bool
     */
    public function isInActiveByDateTime(DateTime $dateTime): bool
    {
        return $this->getLeaveDateTime() !== null && $dateTime > $this->getLeaveDateTime();
    }

    /**
     * für die Sortierung in Listen
     *
     * @return string
     */
    public function getSort(): string
    {
        return ($tblPerson = $this->getServiceTblPerson()) ? $tblPerson->getLastFirstName() : '';
    }
}