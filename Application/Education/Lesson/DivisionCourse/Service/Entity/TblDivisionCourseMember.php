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
    protected string $Description;

    /**
     * @Column(type="integer")
     */
    protected ?int $SortOrder;

    /**
     * @Column(type="datetime")
     */
    protected ?DateTime $LeaveDate;

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
        return DivisionCourse::useService()->getMemberTypeById($this->tblLessonDivisionCourseMemberType);
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
}