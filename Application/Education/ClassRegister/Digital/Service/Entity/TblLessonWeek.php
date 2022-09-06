<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Service\Entity;
use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
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
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_SERVICE_TBL_GROUP = 'serviceTblGroup';
    const ATTR_DATE = 'Date';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblGroup;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblYear;

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
     * @return bool|TblDivision
     */
    public function getServiceTblDivision()
    {
        if (null === $this->serviceTblDivision) {
            return false;
        } else {
            return Division::useService()->getDivisionById($this->serviceTblDivision);
        }
    }

    /**
     * @param TblDivision|null $tblDivision
     */
    public function setServiceTblDivision(TblDivision $tblDivision = null)
    {
        $this->serviceTblDivision = (null === $tblDivision ? null : $tblDivision->getId());
    }

    /**
     * @return bool|TblGroup
     */
    public function getServiceTblGroup()
    {
        if (null === $this->serviceTblGroup) {
            return false;
        } else {
            return Group::useService()->getGroupById($this->serviceTblGroup);
        }
    }

    /**
     * @param null|TblGroup $serviceTblGroup
     */
    public function setServiceTblGroup(TblGroup $serviceTblGroup = null)
    {
        $this->serviceTblGroup = (null === $serviceTblGroup ? null : $serviceTblGroup->getId());
    }

    /**
     * @return bool|TblYear
     */
    public function getServiceTblYear()
    {
        if (null === $this->serviceTblYear) {
            return false;
        } else {
            return Term::useService()->getYearById($this->serviceTblYear);
        }
    }

    /**
     * @param TblYear|null $tblYear
     */
    public function setServiceTblYear(TblYear $tblYear = null)
    {
        $this->serviceTblYear = (null === $tblYear ? null : $tblYear->getId());
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
