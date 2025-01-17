<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Service\Entity;

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
 * @Table(name="tblClassRegisterFullTimeContent")
 * @Cache(usage="READ_ONLY")
 */
class TblFullTimeContent extends Element
{
    const ATTR_SERVICE_TBL_DIVISION_COURSE = 'serviceTblDivisionCourse';
    const ATTR_FROM_DATE = 'FromDate';
    const ATTR_TO_DATE = 'ToDate';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivisionCourse;
    /**
     * @Column(type="datetime")
     */
    protected DateTime $FromDate;
    /**
     * @Column(type="datetime")
     */
    protected ?DateTime $ToDate;
    /**
     * @Column(type="string")
     */
    protected string $Content;
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
     * @return DateTime
     */
    public function getFromDate(): DateTime
    {
        return $this->FromDate;
    }

    /**
     * @return string
     */
    public function getFromDateString(): string
    {
        return $this->FromDate->format('d.m.Y');
    }

    /**
     * @param DateTime $FromDate
     */
    public function setFromDate(DateTime $FromDate): void
    {
        $this->FromDate = $FromDate;
    }

    /**
     * @return DateTime|null
     */
    public function getToDate(): ?DateTime
    {
        return $this->ToDate;
    }

    /**
     * @return string
     */
    public function getToDateString(): string
    {
        if ($this->ToDate === null) {
            return  '';
        }

        return $this->ToDate->format('d.m.Y');
    }

    /**
     * @param DateTime|null $ToDate
     */
    public function setToDate(?DateTime $ToDate): void
    {
        $this->ToDate = $ToDate;
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
    public function setContent(string $Content)
    {
        $this->Content = $Content;
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