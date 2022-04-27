<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
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
    protected int $serviceTblSubject;

    /**
     * @Column(type="boolean")
     */
    protected bool $IsAdvancedCourse;

    /**
     * @Column(type="datetime")
     */
    protected ?DateTime $LeaveDate;

    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblPeriod;

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
        return Subject::useService()->getSubjectById($this->serviceTblSubject);
    }

    /**
     * @param TblSubject $tblSubject
     */
    public function setServiceTblSubject(TblSubject $tblSubject)
    {
        $this->serviceTblSubject = $tblSubject->getId();
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
    public function isIsAdvancedCourse(): bool
    {
        return $this->IsAdvancedCourse;
    }

    /**
     * @param bool $IsAdvancedCourse
     */
    public function setIsAdvancedCourse(bool $IsAdvancedCourse): void
    {
        $this->IsAdvancedCourse = $IsAdvancedCourse;
    }

    /**
     * @return false|TblPeriod
     */
    public function getServiceTblPeriod()
    {
        return $this->serviceTblPeriod ? Term::useService()->getPeriodById($this->serviceTblPeriod) : false;
    }

    /**
     * @param TblPeriod|null $tblPeriod
     */
    public function setServiceTblPeriod(?TblPeriod $tblPeriod): void
    {
        $this->serviceTblPeriod = $tblPeriod ? $tblPeriod->getId() : null;
    }
}