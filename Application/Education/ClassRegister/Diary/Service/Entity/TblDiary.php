<?php

namespace SPHERE\Application\Education\ClassRegister\Diary\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblClassRegisterDiary")
 * @Cache(usage="READ_ONLY")
 */
class TblDiary extends Element
{
    const ATTR_SERVICE_TBL_DIVISION_COURSE = 'serviceTblDivisionCourse';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivisionCourse;
    /**
     * @Column(type="string")
     */
    protected string $Subject;
    /**
     * @Column(type="string")
     */
    protected string $Content;
    /**
     * @Column(type="datetime")
     */
    protected ?DateTime $Date;
    /**
     * @Column(type="string")
     */
    protected string $Location;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @return bool|TblDivisionCourse
     */
    public function getServiceTblDivisionCourse()
    {
        if (null === $this->serviceTblDivisionCourse) {
            return false;
        } else {
            return DivisionCourse::useService()->getDivisionCourseById($this->serviceTblDivisionCourse);
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     */
    public function setServiceTblDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        $this->serviceTblDivisionCourse = $tblDivisionCourse->getId();
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->Subject;
    }

    /**
     * @param string $Subject
     */
    public function setSubject(string $Subject): void
    {
        $this->Subject = $Subject;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->Content;
    }

    /**
     * @param string $Content
     */
    public function setContent(string $Content): void
    {
        $this->Content = $Content;
    }

    /**
     * @return DateTime|null
     */
    public function getDateTime(): ?DateTime
    {
        return $this->Date;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        if (null === $this->Date) {
            return false;
        }

        return $this->Date->format('d.m.Y');
    }

    /**
     * @param DateTime|null $Date
     */
    public function setDate(?DateTime $Date): void
    {
        $this->Date = $Date;
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->Location;
    }

    /**
     * @param string $Location
     */
    public function setLocation(string $Location): void
    {
        $this->Location = $Location;
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {
        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {
        $this->serviceTblPerson = (null === $tblPerson ? null : $tblPerson->getId());
    }
}