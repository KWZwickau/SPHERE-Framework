<?php

namespace SPHERE\Application\Education\Absence\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Link\Repository\AbstractLink;
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

    const VALUE_SOURCE_STAFF = 0;
    const VALUE_SOURCE_ONLINE_CUSTODY = 1;
    const VALUE_SOURCE_ONLINE_STUDENT = 2;

    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_YEAR = 'serviceTblYear';
    const ATTR_FROM_DATE = 'FromDate';
    const ATTR_TO_DATE = 'ToDate';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblYear;
    /**
     * @Column(type="datetime")
     */
    protected ?DateTime $FromDate;
    /**
     * @Column(type="datetime")
     */
    protected ?DateTime $ToDate;
    /**
     * @Column(type="string")
     */
    protected string $Remark;
    /**
     * @Column(type="smallint")
     */
    protected int $Status;
    /**
     * @Column(type="smallint")
     */
    protected int $Type;
    /**
     * @Column(type="boolean")
     */
    protected bool $IsCertificateRelevant;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPersonStaff;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPersonCreator;
    /**
     * @Column(type="smallint")
     */
    protected int $Source;

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param DateTime|null $FromDate
     * @param DateTime|null $ToDate
     * @param string $Remark
     * @param int $Status
     * @param int $Type
     * @param bool $IsCertificateRelevant
     * @param TblPerson|null $serviceTblPersonStaff
     * @param TblPerson|null $serviceTblPersonCreator
     * @param int $Source
     */
    public function __construct(TblPerson $tblPerson, TblYear $tblYear, ?DateTime $FromDate, ?DateTime $ToDate, string $Remark, int $Status, int $Type,
        bool $IsCertificateRelevant, ?TblPerson $serviceTblPersonStaff, ?TblPerson $serviceTblPersonCreator, int $Source)
    {
        $this->serviceTblPerson = $tblPerson->getId();
        $this->serviceTblYear = $tblYear->getId();
        $this->FromDate = $FromDate;
        $this->ToDate = $ToDate;
        $this->Remark = $Remark;
        $this->Status = $Status;
        $this->Type = $Type;
        $this->IsCertificateRelevant = $IsCertificateRelevant;
        $this->serviceTblPersonStaff = $serviceTblPersonStaff ? $serviceTblPersonStaff->getId() : null;
        $this->serviceTblPersonCreator = $serviceTblPersonCreator ? $serviceTblPersonCreator->getId() : null;
        $this->Source = $Source;
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
     * @param string $format
     *
     * @return false|string
     */
    public function getFromDate(string $format = 'd.m.Y')
    {
        if (null === $this->FromDate) {
            return false;
        }
        $Date = $this->FromDate;
        if ($Date instanceof DateTime) {
            return $Date->format($format);
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
        if (null === $this->ToDate) {
            return false;
        }
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
     * @param DateTime|null $tillDate
     * @param int $countLessons
     * @param TblCompany|null $tblCompany
     *
     * @return int|string
     */
    public function getDays(DateTime $tillDate = null, int &$countLessons = 0, TblCompany $tblCompany = null)
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

    /**
     * @param int $countLessons
     *
     * @return string
     */
    public function getLessonStringByAbsence(int &$countLessons = 0): string
    {
        $result = '';
        if (($list = Absence::useService()->getAbsenceLessonAllByAbsence($this))) {
            $countLessons = count($list);
            foreach ($list as $tblAbsenceLesson) {
                $result .= ($result == '' ? '' : ', ') . $tblAbsenceLesson->getLesson() . '.UE';
            }
        }

        return $result;
    }

    /**
     * @param DateTime $date
     * @param $countDays
     * @param TblCompany|null $tblCompany
     *
     * @return int
     */
    private function countThisDay(DateTime $date, $countDays, TblCompany $tblCompany = null): int
    {
        // todo
        $tblDivision = $this->getServiceTblDivision();
        $DayAtWeek = $date->format('w');
        if ($tblDivision && ($tblSchoolType = $tblDivision->getType()) && Digital::useService()->getHasSaturdayLessonsBySchoolType($tblSchoolType)) {
            $isWeekend = $DayAtWeek == 0;
        } else {
            $isWeekend = $DayAtWeek == 0 || $DayAtWeek == 6;
        }

        if ($tblDivision
            && ($tblYear = $this->getServiceTblDivision()->getServiceTblYear())
            && !Term::useService()->getHolidayByDay($tblYear, $date, $tblCompany)
            && !$isWeekend
        ) {
            $countDays++;
        }

        return $countDays;
    }

    /**
     * @return bool
     */
    public function isSingleDay(): bool
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
    public function getStatusDisplayName(): string
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
    public function getStatusDisplayShortName(): string
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
    public function getType(): int
    {
        return $this->Type;
    }

    /**
     * @param int $Type
     */
    public function setType(int $Type)
    {
        $this->Type = $Type;
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPersonStaff()
    {
        if (null === $this->serviceTblPersonStaff) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPersonStaff);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPersonStaff(TblPerson $tblPerson = null)
    {
        $this->serviceTblPersonStaff = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPersonCreator()
    {
        if (null === $this->serviceTblPersonCreator) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPersonCreator);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPersonCreator(TblPerson $tblPerson = null)
    {
        $this->serviceTblPersonCreator = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return string
     */
    public function getTypeDisplayShortName(): string
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
    public function getTypeDisplayName(): string
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
    public function getWeekDay(): string
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

    /**
     * @return string
     */
    public function getDisplayStaff(): string
    {
        if (($tblPerson = $this->getServiceTblPersonStaff())){
            if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))){
                if ($tblTeacher->getAcronym()) {
                    return $tblTeacher->getAcronym();
                }
            }

            return $tblPerson->getLastName();
        }

        return '';
    }

    /**
     * @return bool
     */
    public function getIsCertificateRelevant() : bool
    {
        return (bool) $this->IsCertificateRelevant;
    }

    /**
     * @param bool $IsCertificateRelevant
     */
    public function setIsCertificateRelevant(bool $IsCertificateRelevant): void
    {
        $this->IsCertificateRelevant = $IsCertificateRelevant;
    }

    /**
     * @return int
     */
    public function getSource(): int
    {
        return $this->Source;
    }

    /**
     * @param int $Source
     */
    public function setSource(int $Source): void
    {
        $this->Source = $Source;
    }

    public function getDisplayPersonCreator(bool $isOnlineAbsenceView = true): string
    {
        if (($tblPerson = $this->getServiceTblPersonCreator())){
            if ($this->getSource() == self::VALUE_SOURCE_STAFF) {
                if ($isOnlineAbsenceView) {
                    return 'Schule';
                } else {
                    if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson)) && $tblTeacher->getAcronym()) {
                        return $tblTeacher->getAcronym();
                    } else {
                        return $tblPerson->getLastName();
                    }
                }
            } else {
                return $tblPerson->getSalutation() . ' ' . $tblPerson->getLastName();
            }
        }

        return $isOnlineAbsenceView ? 'Schule' : '';
    }

    /**
     * @return string
     */
    public function getLinkType(): string
    {
        if ($this->getIsOnlineAbsence()) {
            return AbstractLink::TYPE_ORANGE_LINK;
        } elseif (!$this->getIsCertificateRelevant()) {
            return AbstractLink::TYPE_MUTED_LINK;
        } else {
            return AbstractLink::TYPE_LINK;
        }
    }

    /**
     * @return string
     */
    public function getDisplayStaffToolTip(): string
    {
        if (($tblPersonStaff = $this->getDisplayStaff())) {
            return $tblPersonStaff;
        } else {
            return $this->getDisplayPersonCreator(false);
        }
    }

    /**
     * Noch nicht bearbeitete Online Fehlzeit
     *
     * @return bool
     */
    public function getIsOnlineAbsence(): bool
    {
        return $this->getSource() != TblAbsence::VALUE_SOURCE_STAFF && $this->getServiceTblPersonStaff() == false;
    }
}