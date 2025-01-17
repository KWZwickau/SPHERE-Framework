<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblLessonDivisionCourseMember")
 * @Cache(usage="READ_ONLY")
 */
class TblDivisionCourseMember extends Element
{
    const ATTR_TBL_DIVISION_COURSE = 'tblLessonDivisionCourse';
    const ATTR_TBL_MEMBER_TYPE = 'tblLessonDivisionCourseMemberType';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_LEAVE_DATE = 'LeaveDate';

    /**
     * @Column(type="bigint")
     */
    protected int $tblLessonDivisionCourse;

    /**
     * @Column(type="bigint")
     */
    protected int $tblLessonDivisionCourseMemberType;

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblPerson;

    /**
     * @Column(type="string")
     */
    protected string $Description = '';

    /**
     * @Column(type="integer")
     */
    protected ?int $SortOrder = null;

    /**
     * @Column(type="datetime")
     */
    protected ?DateTime $LeaveDate = null;

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourseMemberType $tblMemberType
     * @param TblPerson $tblPerson
     * @param string $description
     * @param DateTime|null $leaveDate
     * @param int|null $sortOrder
     *
     * @return TblDivisionCourseMember
     */
    public static function withParameter(TblDivisionCourse $tblDivisionCourse, TblDivisionCourseMemberType $tblMemberType, TblPerson $tblPerson,
        string $description = '', ?DateTime $leaveDate = null, ?int $sortOrder = null): TblDivisionCourseMember
    {
        $instance = new self();

        $instance->tblLessonDivisionCourse = $tblDivisionCourse->getId();
        $instance->tblLessonDivisionCourseMemberType = $tblMemberType->getId();
        $instance->serviceTblPerson = $tblPerson->getId();
        $instance->Description = $description;
        $instance->LeaveDate = $leaveDate;
        $instance->SortOrder = $sortOrder;

        return  $instance;
    }

    /**
     * @return false|TblDivisionCourse
     */
    public function getTblDivisionCourse()
    {
        return DivisionCourse::useService()->getDivisionCourseById($this->tblLessonDivisionCourse);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     */
    public function setTblDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        $this->tblLessonDivisionCourse = $tblDivisionCourse->getId();
    }

    /**
     * @return false|TblDivisionCourseMemberType
     */
    public function getTblMemberType()
    {
        return DivisionCourse::useService()->getDivisionCourseMemberTypeById($this->tblLessonDivisionCourseMemberType);
    }

    /**
     * @param TblDivisionCourseMemberType $tblMemberType
     */
    public function setTblMemberType(TblDivisionCourseMemberType $tblMemberType)
    {
        $this->tblLessonDivisionCourse = $tblMemberType->getId();
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
     * @return string
     */
    public function getDescription(): string
    {
        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription(string $Description): void
    {
        $this->Description = $Description;
    }

    /**
     * @return int|null
     */
    public function getSortOrder(): ?int
    {
        return $this->SortOrder;
    }

    /**
     * @param int $SortOrder
     */
    public function setSortOrder(int $SortOrder)
    {
        $this->SortOrder = $SortOrder;
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
     * virtuelle Methode für Sortierung der Mitglieder, falls diese nicht über die SortOrder sortiert sind
     *
     * @return string
     */
    public function getLastFirstName(): string
    {
        if (($tblPerson = $this->getServiceTblPerson())) {
            return $tblPerson->getLastFirstName();
        }

        return '';
    }
}