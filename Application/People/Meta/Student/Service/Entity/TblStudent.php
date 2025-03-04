<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudent")
 * @Cache(usage="READ_ONLY")
 */
class TblStudent extends Element
{

    const SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_TBL_PREFIX = 'Prefix';
    const ATTR_TBL_IDENTIFIER = 'Identifier';

    /**
     * @Column(type="string")
     */
    protected $Prefix;
    /**
     * @Column(type="string")
     */
    protected $Identifier;
    /**
     * @Column(type="datetime")
     */
    protected $SchoolAttendanceStartDate;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentMedicalRecord;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentTransport;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentBilling;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentLocker;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentBaptism;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentSpecialNeeds;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentTechnicalSchool;

    /**
     * @Column(type="boolean")
     */
    protected $HasMigrationBackground;
    /**
     * @Column(type="string")
     */
    protected $MigrationBackground;

    /**
     * @Column(type="boolean")
     */
    protected $IsInPreparationDivisionForMigrants;

    /**
     * @return bool|TblStudentMedicalRecord
     */
    public function getTblStudentMedicalRecord()
    {

        if (null === $this->tblStudentMedicalRecord) {
            return false;
        } else {
            return Student::useService()->getStudentMedicalRecordById($this->tblStudentMedicalRecord);
        }
    }

    /**
     * @param null|TblStudentMedicalRecord $tblStudentMedicalRecord
     */
    public function setTblStudentMedicalRecord(TblStudentMedicalRecord $tblStudentMedicalRecord = null)
    {

        $this->tblStudentMedicalRecord = ( null === $tblStudentMedicalRecord ? null : $tblStudentMedicalRecord->getId() );
    }

    /**
     * @return bool|TblStudentTransport
     */
    public function getTblStudentTransport()
    {

        if (null === $this->tblStudentTransport) {
            return false;
        } else {
            return Student::useService()->getStudentTransportById($this->tblStudentTransport);
        }
    }

    /**
     * @param null|TblStudentTransport $tblStudentTransport
     */
    public function setTblStudentTransport(TblStudentTransport $tblStudentTransport = null)
    {

        $this->tblStudentTransport = ( null === $tblStudentTransport ? null : $tblStudentTransport->getId() );
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

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblStudentBilling
     */
    public function getTblStudentBilling()
    {

        if (null === $this->tblStudentBilling) {
            return false;
        } else {
            return Student::useService()->getStudentBillingById($this->tblStudentBilling);
        }
    }

    /**
     * @param null|TblStudentBilling $tblStudentBilling
     */
    public function setTblStudentBilling(TblStudentBilling $tblStudentBilling = null)
    {

        $this->tblStudentBilling = ( null === $tblStudentBilling ? null : $tblStudentBilling->getId() );
    }

    /**
     * @return bool|TblStudentBaptism
     */
    public function getTblStudentBaptism()
    {

        if (null === $this->tblStudentBaptism) {
            return false;
        } else {
            return Student::useService()->getStudentBaptismById($this->tblStudentBaptism);
        }
    }

    /**
     * @param null|TblStudentBaptism $tblStudentBaptism
     */
    public function setTblStudentBaptism(TblStudentBaptism $tblStudentBaptism = null)
    {

        $this->tblStudentBaptism = ( null === $tblStudentBaptism ? null : $tblStudentBaptism->getId() );
    }

    /**
     * @return bool|TblStudentLocker
     */
    public function getTblStudentLocker()
    {

        if (null === $this->tblStudentLocker) {
            return false;
        } else {
            return Student::useService()->getStudentLockerById($this->tblStudentLocker);
        }
    }

    /**
     * @param null|TblStudentLocker $tblStudentLocker
     */
    public function setTblStudentLocker(TblStudentLocker $tblStudentLocker = null)
    {

        $this->tblStudentLocker = ( null === $tblStudentLocker ? null : $tblStudentLocker->getId() );
    }

    /**
     * @return string
     */
    public function getPrefix()
    {

        return $this->Prefix;
    }

    /**
     * @param string $Prefix
     */
    public function setPrefix($Prefix)
    {

        $this->Prefix = $Prefix;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {

        return $this->Identifier;
    }

    /**
     * @param string $Identifier
     */
    public function setIdentifier($Identifier)
    {

        $this->Identifier = $Identifier;
    }

    /**
     * @return string $Prefix.$Identifier
     */
    public function getIdentifierComplete()
    {

        return $this->getPrefix().$this->getIdentifier();
    }

    /**
     * @return string
     */
    public function getSchoolAttendanceStartDate()
    {

        if (null === $this->SchoolAttendanceStartDate) {
            return false;
        }
        /** @var DateTime $SchoolAttendanceStartDate */
        $SchoolAttendanceStartDate = $this->SchoolAttendanceStartDate;
        if ($SchoolAttendanceStartDate instanceof DateTime) {
            return $SchoolAttendanceStartDate->format('d.m.Y');
        } else {
            return (string)$SchoolAttendanceStartDate;
        }
    }

    /**
     * @param null|DateTime $SchoolAttendanceStartDate
     */
    public function setSchoolAttendanceStartDate(DateTime $SchoolAttendanceStartDate = null)
    {

        $this->SchoolAttendanceStartDate = $SchoolAttendanceStartDate;
    }

    /**
     * @return boolean
     */
    public function getHasMigrationBackground()
    {
        return (boolean) $this->HasMigrationBackground;
    }

    /**
     * @param boolean $HasMigrationBackground
     */
    public function setHasMigrationBackground($HasMigrationBackground)
    {
        $this->HasMigrationBackground = (boolean) $HasMigrationBackground;
    }

    /**
     * @return string|null
     */
    public function getMigrationBackground(): ?string
    {
        return $this->MigrationBackground;
    }

    /**
     * @param string $HasMigrationBackground
     */
    public function setMigrationBackground(string $MigrationBackground = ''): void
    {
        $this->MigrationBackground = $MigrationBackground;
    }

    /**
     * @return boolean
     */
    public function isInPreparationDivisionForMigrants()
    {
        return (boolean) $this->IsInPreparationDivisionForMigrants;
    }

    /**
     * @param boolean $IsInPreparationDivisionForMigrants
     */
    public function setIsInPreparationDivisionForMigrants($IsInPreparationDivisionForMigrants)
    {
        $this->IsInPreparationDivisionForMigrants = (boolean) $IsInPreparationDivisionForMigrants;
    }

    /**
     * @deprecated
     *
     * @return bool|TblCourse
     */
    public function getCourse()
    {

        return Student::useService()->getCourseByStudent($this);
    }

    /**
     * @return bool|\SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject
     */
    public function getTblSubjectOrientation()
    {
        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($this,
                $tblStudentSubjectType))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            $tblStudentSubject = current($tblStudentSubjectList);

            return $tblStudentSubject->getServiceTblSubject();
        }

        return false;
    }

    /**
     * @param $Ranking
     *
     * @return bool|\SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject
     */
    public function getTblSubjectForeignLanguage($Ranking)
    {
        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('FOREIGN_LANGUAGE'))
            && ($tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($Ranking))
            && ($tblStudentSubject = Student::useService()->getStudentSubjectByStudentAndSubjectAndSubjectRanking(
                $this,
                $tblStudentSubjectType,
                $tblStudentSubjectRanking
            ))
        ) {

            return $tblStudentSubject->getServiceTblSubject();
        }

        return false;
    }

    /**
     * @return bool|\SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject
     */
    public function getTblSubjectProfile()
    {
        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('PROFILE'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($this,
                $tblStudentSubjectType))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            $tblStudentSubject = current($tblStudentSubjectList);

            return $tblStudentSubject->getServiceTblSubject();
        }

        return false;
    }

    /**
     * @return bool|\SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject
     */
    public function getTblSubjectReligion()
    {
        if (($tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier('RELIGION'))
            && ($tblStudentSubjectList = Student::useService()->getStudentSubjectAllByStudentAndSubjectType($this,
                $tblStudentSubjectType))
        ) {
            /** @var TblStudentSubject $tblStudentSubject */
            $tblStudentSubject = current($tblStudentSubjectList);

            return $tblStudentSubject->getServiceTblSubject();
        }

        return false;
    }

    /**
     * @return bool|TblStudentSpecialNeeds
     */
    public function getTblStudentSpecialNeeds()
    {

        if (null === $this->tblStudentSpecialNeeds) {
            return false;
        } else {
            return Student::useService()->getStudentSpecialNeedsById($this->tblStudentSpecialNeeds);
        }
    }

    /**
     * @param null|TblStudentSpecialNeeds $tblStudentSpecialNeeds
     */
    public function setTblStudentSpecialNeeds(TblStudentSpecialNeeds $tblStudentSpecialNeeds = null)
    {

        $this->tblStudentSpecialNeeds = ( null === $tblStudentSpecialNeeds ? null : $tblStudentSpecialNeeds->getId() );
    }

    /**
     * @return bool|TblStudentTechnicalSchool
     */
    public function getTblStudentTechnicalSchool()
    {

        if (null === $this->tblStudentTechnicalSchool) {
            return false;
        } else {
            return Student::useService()->getStudentTechnicalSchoolById($this->tblStudentTechnicalSchool);
        }
    }

    /**
     * @param null|TblStudentTechnicalSchool $tblStudentTechnicalSchool
     */
    public function setTblStudentTechnicalSchool(TblStudentTechnicalSchool $tblStudentTechnicalSchool = null)
    {

        $this->tblStudentTechnicalSchool = ( null === $tblStudentTechnicalSchool ? null : $tblStudentTechnicalSchool->getId() );
    }

    /**
     * @param bool $DisplayError
     *
     * @return int|ToolTip|string
     */
    public function getSchoolAttendanceYear($DisplayError = true)
    {
        // SBJ (Schulbesuchsjahr): automatisch berechnet aus Datum / Jahr  der Ersteinschulung und richtig setzen entsprechend aktuelle Schuljahr (Stichtag vor und nach 1.8)
        if (($tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT'))
            && ($tblTransfer = Student::useService()->getStudentTransferByType($this, $tblStudentTransferType))
        ) {
            $enrollmentDate = $tblTransfer->getTransferDate();
        } else {
            $enrollmentDate = false;
        }

        if ($enrollmentDate) {
            $enrollmentDateTime = new DateTime($enrollmentDate);
            $enrollmentYear = intval($enrollmentDateTime->format('Y'));
            $now = new DateTime('now');
            $nowYear = intval($now->format('Y'));
            $endOfPeriod = new DateTime('01.08.' . $nowYear);

            return ($nowYear - $enrollmentYear + ($now > $endOfPeriod ? 1 : 0));
        } else {
            if($DisplayError){
                return new ToolTip(new Warning(new Exclamation()), 'Bitte pflegen Sie das Ersteinschulungsdatum ein.');
            } else {
                return '';
            }
        }
    }
}
