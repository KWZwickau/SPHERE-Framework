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
 * @Table(name="tblClassRegisterLessonWeek")
 * @Cache(usage="READ_ONLY")
 */
class TblLessonWeek extends Element
{
    const ATTR_SERVICE_TBL_DIVISION_COURSE = 'serviceTblDivision';
    const ATTR_DATE = 'Date';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblGroup = null;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblYear = null;

    /**
     * @Column(type="datetime")
     */
    protected $Date;

    /**
     * @Column(type="string")
     */
    protected string $Remark;

    /**
     * @Column(type="datetime")
     */
    protected $DateDivisionTeacher;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPersonDivisionTeacher;

    /**
     * @Column(type="datetime")
     */
    protected $DateHeadmaster;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPersonHeadmaster;

    /**
     * @return bool|TblDivisionCourse
     */
    public function getServiceTblDivisionCourse()
    {
        if (null === $this->serviceTblDivision) {
            return false;
        } else {
            return DivisionCourse::useService()->getDivisionCourseById($this->serviceTblDivision);
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     */
    public function setServiceTblDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        $this->serviceTblDivision = $tblDivisionCourse->getId();
    }

    /**
     * @return string
     */
    public function getDate()
    {
        if (null === $this->Date) {
            return false;
        }
        /** @var DateTime $Date */
        $Date = $this->Date;
        if ($Date instanceof DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @param null|DateTime $Date
     */
    public function setDate(DateTime $Date = null)
    {
        $this->Date = $Date;
    }

    /**
     * @return string
     */
    public function getRemark(): string
    {
        return $this->Remark;
    }

    /**
     * @param string $Remark
     */
    public function setRemark(string $Remark)
    {
        $this->Remark = $Remark;
    }

    /**
     * @return string
     */
    public function getDateDivisionTeacher()
    {
        if (null === $this->DateDivisionTeacher) {
            return false;
        }
        /** @var DateTime $DateDivisionTeacher */
        $DateDivisionTeacher = $this->DateDivisionTeacher;
        if ($DateDivisionTeacher instanceof DateTime) {
            return $DateDivisionTeacher->format('d.m.Y');
        } else {
            return (string)$DateDivisionTeacher;
        }
    }

    /**
     * @param null|DateTime $DateDivisionTeacher
     */
    public function setDateDivisionTeacher(DateTime $DateDivisionTeacher = null)
    {
        $this->DateDivisionTeacher = $DateDivisionTeacher;
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPersonDivisionTeacher()
    {
        if (null === $this->serviceTblPersonDivisionTeacher) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPersonDivisionTeacher);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPersonDivisionTeacher(TblPerson $tblPerson = null)
    {
        $this->serviceTblPersonDivisionTeacher = (null === $tblPerson ? null : $tblPerson->getId());
    }

    /**
     * @return string
     */
    public function getDateHeadmaster()
    {
        if (null === $this->DateHeadmaster) {
            return false;
        }
        /** @var DateTime $DateHeadmaster */
        $DateHeadmaster = $this->DateHeadmaster;
        if ($DateHeadmaster instanceof DateTime) {
            return $DateHeadmaster->format('d.m.Y');
        } else {
            return (string)$DateHeadmaster;
        }
    }

    /**
     * @param null|DateTime $DateHeadmaster
     */
    public function setDateHeadmaster(DateTime $DateHeadmaster = null)
    {
        $this->DateHeadmaster = $DateHeadmaster;
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPersonHeadmaster()
    {
        if (null === $this->serviceTblPersonHeadmaster) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPersonHeadmaster);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPersonHeadmaster(TblPerson $tblPerson = null)
    {
        $this->serviceTblPersonHeadmaster = (null === $tblPerson ? null : $tblPerson->getId());
    }
}
