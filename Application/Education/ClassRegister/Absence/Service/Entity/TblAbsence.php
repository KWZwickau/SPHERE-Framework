<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 11.07.2016
 * Time: 08:59
 */

namespace SPHERE\Application\Education\ClassRegister\Absence\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblAbsence")
 * @Cache(usage="READ_ONLY")
 */
class TblAbsence extends Element
{

    const VALUE_STATUS_NULL = 0;
    const VALUE_STATUS_EXCUSED = 1;
    const VALUE_STATUS_UNEXCUSED = 2;

    const VALUE_TYPE_NULL = 0;
    const VALUE_TYPE_PRACTICE = 1;
    const VALUE_TYPE_THEORY = 2;

    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_FROM_DATE = 'FromDate';
    const ATTR_TO_DATE = 'ToDate';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="datetime")
     */
    protected $FromDate;

    /**
     * @Column(type="datetime")
     */
    protected $ToDate;

    /**
     * @Column(type="string")
     */
    protected $Remark;

    /**
     * @Column(type="smallint")
     */
    protected $Status;

    /**
     * @Column(type="smallint")
     */
    protected $Type;

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
     * @return string
     */
    public function getFromDate()
    {

        if (null === $this->FromDate) {
            return false;
        }
        /** @var DateTime $Date */
        $Date = $this->FromDate;
        if ($Date instanceof DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @param null|DateTime $Date
     */
    public function setFromDate(DateTime $Date = null)
    {

        $this->FromDate = $Date;
    }

    /**
     * @return string
     */
    public function getToDate()
    {

        if (null === $this->ToDate) {
            return false;
        }
        /** @var DateTime $Date */
        $Date = $this->ToDate;
        if ($Date instanceof DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @param null|DateTime $Date
     */
    public function setToDate(DateTime $Date = null)
    {

        $this->ToDate = $Date;
    }

    /**
     * @return mixed
     */
    public function getRemark()
    {
        return $this->Remark;
    }

    /**
     * @param mixed $Remark
     */
    public function setRemark($Remark)
    {
        $this->Remark = $Remark;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->Status;
    }

    /**
     * @param mixed $Status
     */
    public function setStatus($Status)
    {
        $this->Status = $Status;
    }

    /**
     * @param DateTime $tillDate
     * @param int $countLessons
     * @param TblCompany|null $tblCompany
     *
     * @return int|string
     */
    public function getDays(DateTime $tillDate = null, &$countLessons = 0, TblCompany $tblCompany = null)
    {
        $countDays = 0;
        $lessons = Absence::useService()->getLessonAllByAbsence($this);

        $fromDate = new DateTime($this->getFromDate());
        if ($tillDate === null) {
            if ($this->getToDate()) {
                $toDate = new DateTime($this->getToDate());
                if ($toDate >= $fromDate) {
                    $date = $fromDate;
                    while ($date <= $toDate) {

                        $countDays = $this->countThisDay($date, $countDays, $tblCompany);

                        $date = $date->modify('+1 day');
                    }
                }
            } else {
                $countDays = $this->countThisDay($fromDate, $countDays, $tblCompany);
            }
        } else {
            if ($tillDate >= $fromDate){
                if ($this->getToDate()) {
                    $toDate = new DateTime($this->getToDate());
                    if ($toDate >= $fromDate) {
                        $date = $fromDate;
                        while ($date <= $toDate && $date <= $tillDate) {
                            $countDays = $this->countThisDay($date, $countDays, $tblCompany);
                            $date = $date->modify('+1 day');
                        }
                    }
                } else {
                    $countDays = $this->countThisDay($fromDate, $countDays, $tblCompany);
                }
            }
        }

        $countLessons += $lessons ? (count($lessons) * $countDays) : 0;

        return $lessons ? '' : $countDays;
    }

    public function getLessonStringByAbsence()
    {
        $result = '';
        if (($list = Absence::useService()->getAbsenceLessonAllByAbsence($this))) {
            foreach ($list as $tblAbsenceLesson) {
                $result .= ($result == '' ? '' : ', ') . $tblAbsenceLesson->getLesson() . '.UE';
            }
        }

        return $result;
    }

    /**
     * @param DateTime $date
     * @param integer $countDays
     * @param TblCompany|null $tblCompany
     *
     * @return mixed
     */
    private function countThisDay(DateTime $date, $countDays, TblCompany $tblCompany = null)
    {

        if ($date->format('w') != 0 && $date->format('w') != 6) {
            if ($this->getServiceTblDivision()
                && ($tblYear = $this->getServiceTblDivision()->getServiceTblYear())
                && !Term::useService()->getHolidayByDay($tblYear, $date, $tblCompany)
            ) {
                $countDays++;
            }
        }

        return $countDays;
    }

    /**
     * @return bool
     */
    public function isSingleDay()
    {

        if ($this->getFromDate() && $this->getToDate()) {
            if ($this->getFromDate() == $this->getToDate()) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * @return string
     */
    public function getStatusDisplayName()
    {
        switch ($this->getStatus()) {
            case self::VALUE_STATUS_EXCUSED: return 'entschuldigt';
            case self::VALUE_STATUS_UNEXCUSED: return 'unentschuldigt';
            default: return '';
        }
    }

    /**
     * @return string
     */
    public function getStatusDisplayShortName()
    {
        switch ($this->getStatus()) {
            case self::VALUE_STATUS_EXCUSED: return 'E';
            case self::VALUE_STATUS_UNEXCUSED: return 'U';
            default: return '';
        }
    }

    /**
     * @return string
     */
    public function getDateSpan()
    {
        if ($this->getToDate()) {
            return $this->getFromDate() . ' - ' . $this->getToDate();
        } else {
            return $this->getFromDate();
        }
    }

    /**
     * @return integer
     */
    public function getType()
    {
        return $this->Type;
    }

    /**
     * @param integer $Type
     */
    public function setType($Type)
    {
        $this->Type = $Type;
    }

    /**
     * @return string
     */
    public function getTypeDisplayShortName()
    {
        switch ($this->getType()) {
            case self::VALUE_TYPE_THEORY: return 'T';
            case self::VALUE_TYPE_PRACTICE: return 'P';
            default: return '';
        }
    }

    /**
     * @return string
     */
    public function getTypeDisplayName()
    {
        switch ($this->getType()) {
            case self::VALUE_TYPE_THEORY: return 'Theorie';
            case self::VALUE_TYPE_PRACTICE: return 'Praxis';
            default: return '';
        }
    }

    /**
     * @return string
     */
    public function getWeekDay()
    {
        /** @var DateTime $date */
        if (($date = $this->FromDate)) {
            $data = array(
                0 => '(Sonntag)',
                1 => '(Montag)',
                2 => '(Dienstag)',
                3 => '(Mittwoch)',
                4 => '(Donnerstag)',
                5 => '(Freitag)',
                6 => '(Samstag)',
            );

            return $data[$date->format('w')];
        }

        return '';
    }
}