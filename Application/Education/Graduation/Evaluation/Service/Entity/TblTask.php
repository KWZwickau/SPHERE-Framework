<?php
namespace SPHERE\Application\Education\Graduation\Evaluation\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblTask")
 * @Cache(usage="READ_ONLY")
 */
class TblTask extends Element
{

    const ATTR_TBL_TEST_TYPE = 'tblTestType';
    const ATTR_SERVICE_TBL_PERIOD = 'serviceTblPeriod';
    const ATTR_SERVICE_TBL_YEAR = 'serviceTblYear';

    const FIRST_PERIOD_ID = -1;
    const FIRST_PERIOD_NAME = '1. Halbjahr';
    const SECOND_PERIOD_ID = -2;
    const SECOND_PERIOD_NAME = '2. Halbjahr';
    const SCHOOL_YEAR_PERIOD_ID = -3;
    const SCHOOL_YEAR_PERIOD_Name = 'Gesamtes Schuljahr';
    const ALL_YEARS_PERIOD_ID = -4;
    const ALL_YEARS_PERIOD_Name = 'Alle Schuljahre';

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="datetime")
     */
    protected $Date;

    /**
     * @Column(type="datetime")
     */
    protected $FromDate;

    /**
     * @Column(type="datetime")
     */
    protected $ToDate;

    /**
     * @Column(type="bigint")
     */
    protected $tblTestType;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPeriod;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblYear;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblScoreType;

    /**
     * @Column(type="boolean")
     */
    protected $IsLocked;

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {

        $this->Name = $Name;
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
     * @return false|DateTime
     */
    public function getDateTime()
    {
        return $this->Date;
    }

    /**
     * @param null|DateTime $Date
     */
    public function setDate(DateTime $Date = null)
    {

        $this->Date = $Date;
    }

    /**
     * @return bool|TblTestType
     */
    public function getTblTestType()
    {

        if (null === $this->tblTestType) {
            return false;
        } else {
            return Evaluation::useService()->getTestTypeById($this->tblTestType);
        }
    }

    /**
     * @param TblTestType|null $tblTestType
     */
    public function setTblTestType($tblTestType)
    {

        $this->tblTestType = ( null === $tblTestType ? null : $tblTestType->getId() );
    }

    /**
     * @return bool|TblPeriod
     */
    public function getServiceTblPeriod()
    {

        if (null === $this->serviceTblPeriod) {
            return false;
        }
        // ist Pseudo-Period
        elseif ($this->serviceTblPeriod < 0) {
            return self::getPseudoPeriod($this->serviceTblPeriod);
        } else {
            return Term::useService()->getPeriodById($this->serviceTblPeriod);
        }
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblPeriod
     */
    public function getServiceTblPeriodByDivision(TblDivision $tblDivision)
    {

        if ($this->serviceTblPeriod < 0) {
            if (($tblYear = $tblDivision->getServiceTblYear())) {
                if (($tblPeriodList = $tblYear->getTblPeriodAll($tblDivision))) {
                    if (isset($tblPeriodList[0]) && $this->serviceTblPeriod == self::FIRST_PERIOD_ID) {
                        return $tblPeriodList[0];
                    }
                    if (isset($tblPeriodList[1]) && $this->serviceTblPeriod == self::SECOND_PERIOD_ID) {
                        return $tblPeriodList[1];
                    }
                }
            }
        } else {
            return $this->getServiceTblPeriod();
        }

        return false;
    }

    /**
     * @param TblPeriod|null $tblPeriod
     */
    public function setServiceTblPeriod(TblPeriod $tblPeriod = null)
    {

        $this->serviceTblPeriod = ( null === $tblPeriod ? null : $tblPeriod->getId() );
    }

    /**
     * @return bool
     */
    public function isInEditPeriod()
    {

        if ($this->getFromDate() && $this->getToDate()){
            $fromDate = $this->FromDate;
            if ($fromDate instanceof DateTime) {
                $fromDate = $fromDate->format('Y-m-d');
            }

            $toDate = $this->ToDate;
            if ($toDate instanceof DateTime) {
                $toDate = $toDate->format('Y-m-d');
            }

            if ($fromDate && $toDate){
                $now = (new DateTime('now'))->format("Y-m-d");

                if ($fromDate <= $now && $now <= $toDate){
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isBeforeEditPeriod()
    {

        if ($this->getFromDate()){
            $fromDate = $this->FromDate;
            if ($fromDate instanceof DateTime) {
                $fromDate = $fromDate->format('Y-m-d');
            }

            if ($fromDate){
                $now = (new DateTime('now'))->format("Y-m-d");

                if ($fromDate > $now){
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isAfterEditPeriod()
    {

        if ($this->getFromDate()){
            $toDate = $this->ToDate;
            if ($toDate instanceof DateTime) {
                $toDate = $toDate->format('Y-m-d');
            }

            if ($toDate){
                $now = (new DateTime('now'))->format("Y-m-d");

                if ($toDate < $now){
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getFromDate()
    {

        if (null === $this->Date) {
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

    public function getFromDateTime()
    {
        return $this->FromDate;
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

        if (null === $this->Date) {
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

    public function getToDateTime()
    {
        return $this->ToDate;
    }

    /**
     * @param null|DateTime $Date
     */
    public function setToDate(DateTime $Date = null)
    {

        $this->ToDate = $Date;
    }

    /**
     * @return bool|TblScoreType
     */
    public function getServiceTblScoreType()
    {

        if (null === $this->serviceTblScoreType) {
            return false;
        } else {
            return Gradebook::useService()->getScoreTypeById($this->serviceTblScoreType);
        }
    }

    /**
     * @param TblScoreType|null $tblScoreType
     */
    public function setServiceTblScoreType(TblScoreType $tblScoreType = null)
    {

        $this->serviceTblScoreType = ( null === $tblScoreType ? null : $tblScoreType->getId() );
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

        $this->serviceTblYear = ( null === $tblYear ? null : $tblYear->getId() );
    }

    /**
     * Bestimmt ob zum Notenauftrag bereits Noten vergeben sind
     *
     * @return bool
     */
    public function isLocked()
    {

//        $tblTestAllByTask = Evaluation::useService()->getTestAllByTask($this);
//        if ($tblTestAllByTask){
//            foreach ($tblTestAllByTask as $tblTest){
//                $tblGradeListByTest = Gradebook::useService()->getGradeAllByTest($tblTest);
//                if ($tblGradeListByTest){
//                    return true;
//                }
//            }
//        }
//
//        return false;

        return $this->IsLocked;
    }

    /**
     * @param boolean $IsLocked
     */
    public function setIsLocked($IsLocked)
    {
        $this->IsLocked = (boolean) $IsLocked;
    }

    /**
     * @param $Id
     *
     * @return TblPeriod|false
     */
    public static function getPseudoPeriod($Id)
    {
        if ($Id == self::FIRST_PERIOD_ID || $Id == self::SECOND_PERIOD_ID || $Id == self::ALL_YEARS_PERIOD_ID) {

            $tblPeriod = new TblPeriod();
            $tblPeriod->setId($Id);
            switch ($Id) {
                case self::FIRST_PERIOD_ID: $tblPeriod->setName(self::FIRST_PERIOD_NAME); break;
                case self::SECOND_PERIOD_ID: $tblPeriod->setName(self::SECOND_PERIOD_NAME); break;
                case self::ALL_YEARS_PERIOD_ID: $tblPeriod->setName(self::ALL_YEARS_PERIOD_Name); break;
            }

            return $tblPeriod;
        } else {
            // $Id == self::SCHOOL_YEAR_PERIOD_ID -> false
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isAllYears()
    {
        return $this->serviceTblPeriod == self::ALL_YEARS_PERIOD_ID;
    }
}
