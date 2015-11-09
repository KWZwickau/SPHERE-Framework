<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 05.11.2015
 * Time: 13:51
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblTest")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblTest extends Element
{
    const ATTR_DATE = 'Date';
    const ATTR_CORRECTION_DATE = 'CorrectionDate';
    const ATTR_RETURN_DATE = 'ReturnDate';
    const ATTR_TBL_GRADE_TYPE = 'tblGradeType';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_SERVICE_TBL_PERIOD = 'serviceTblPeriod';
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';

    /**
     * @Column(type="datetime")
     */
    protected $Date;

    /**
     * @Column(type="datetime")
     */
    protected $CorrectionDate;

    /**
     * @Column(type="datetime")
     */
    protected $ReturnDate;

    /**
     * @Column(type="string")
     */
    protected $Description;

    /**
     * @Column(type="bigint")
     */
    protected $tblGradeType;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPeriod;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @return string
     */
    public function getDate()
    {

        if (null === $this->Date) {
            return false;
        }
        /** @var \DateTime $Date */
        $Date = $this->Date;
        if ($Date instanceof \DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @param null|\DateTime $Date
     */
    public function setDate(\DateTime $Date = null)
    {

        $this->Date = $Date;
    }

    /**
     * @return string
     */
    public function getCorrectionDate()
    {

        if (null === $this->CorrectionDate) {
            return false;
        }
        /** @var \DateTime $CorrectionDate */
        $CorrectionDate = $this->CorrectionDate;
        if ($CorrectionDate instanceof \DateTime) {
            return $CorrectionDate->format('d.m.Y');
        } else {
            return (string)$CorrectionDate;
        }
    }

    /**
     * @param null|\DateTime $CorrectionDate
     */
    public function setCorrectionDate(\DateTime $CorrectionDate = null)
    {

        $this->CorrectionDate = $CorrectionDate;
    }

    /**
     * @return string
     */
    public function getReturnDate()
    {

        if (null === $this->ReturnDate) {
            return false;
        }
        /** @var \DateTime $ReturnDate */
        $ReturnDate = $this->ReturnDate;
        if ($ReturnDate instanceof \DateTime) {
            return $ReturnDate->format('d.m.Y');
        } else {
            return (string)$ReturnDate;
        }
    }

    /**
     * @param null|\DateTime $ReturnDate
     */
    public function setReturnDate(\DateTime $ReturnDate = null)
    {

        $this->ReturnDate = $ReturnDate;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {
        $this->Description = $Description;
    }

    /**
     * @return bool|TblGradeType
     */
    public function getTblGradeType()
    {
        if (null === $this->tblGradeType) {
            return false;
        } else {
            return Gradebook::useService()->getGradeTypeById($this->tblGradeType);
        }
    }

    /**
     * @param TblGradeType|null $tblGradeType
     */
    public function setTblGradeType($tblGradeType)
    {
        $this->tblGradeType = (null === $tblGradeType ? null : $tblGradeType->getId());
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubject()
    {

        if (null === $this->serviceTblSubject) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubject);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubject(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubject = (null === $tblSubject ? null : $tblSubject->getId());
    }

    /**
     * @return bool|TblPeriod
     */
    public function getServiceTblPeriod()
    {

        if (null === $this->serviceTblPeriod) {
            return false;
        } else {
            return Term::useService()->getPeriodById($this->serviceTblPeriod);
        }
    }

    /**
     * @param TblPeriod|null $tblPeriod
     */
    public function setServiceTblPeriod(TblPeriod $tblPeriod = null)
    {

        $this->serviceTblPeriod = (null === $tblPeriod ? null : $tblPeriod->getId());
    }

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
}