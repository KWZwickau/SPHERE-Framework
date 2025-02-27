<?php
namespace SPHERE\Application\People\Meta\Student;

use DateTime;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblSchoolDiploma;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalCourse;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalDiploma;
use SPHERE\Application\Education\School\Course\Service\Entity\TblTechnicalSubjectArea;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Student\Service\Data;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreement;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBaptism;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBilling;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentInsuranceState;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLiberationType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLocker;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMasernInfo;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSpecialNeeds;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSpecialNeedsLevel;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectRanking;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubjectType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTechnicalSchool;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTenseOfLesson;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTrainingStatus;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransport;
use SPHERE\Application\People\Meta\Student\Service\Service\Support;
use SPHERE\Application\People\Meta\Student\Service\Setup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\Application\Setting\Consumer\Consumer;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Meta\Student
 */
class Service extends Support
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @return array
     */
    public function migrateStudentSubjectLevels(): array
    {
        return (new Data($this->getBinding()))->migrateStudentSubjectLevels();
    }

    /**
     * @param $LockerNumber
     * @param $LockerLocation
     * @param $KeyNumber
     * @param string $CombinationLockNumber
     *
     * @return TblStudentLocker
     */
    public function insertStudentLocker(
        $LockerNumber,
        $LockerLocation,
        $KeyNumber,
        $CombinationLockNumber = ''
    ) {

        return (new Data($this->getBinding()))->createStudentLocker(
            $LockerNumber,
            $LockerLocation,
            $KeyNumber,
            $CombinationLockNumber
        );
    }

    /**
     * @param TblSiblingRank $tblSiblingRank
     *
     * @return TblStudentBilling
     */
    public function insertStudentBilling(TblSiblingRank $tblSiblingRank)
    {

        return (new Data($this->getBinding()))->createStudentBilling(
            $tblSiblingRank
        );
    }

    /**
     * @param string $Disease
     * @param string $Medication
     * @param string $Insurance
     * @param int $InsuranceState
     * @param string $AttendingDoctor
     * @param DateTime|null $MasernDate
     * @param TblStudentMasernInfo|null $MasernDocumentType
     * @param TblStudentMasernInfo|null $MasernCreatorType
     * @param string $InsuranceNumber
     *
     * @return TblStudentMedicalRecord
     */
    public function insertStudentMedicalRecord(
        $Disease,
        $Medication,
        $Insurance,
        $InsuranceState = 0,
        $AttendingDoctor = '',
        $MasernDate = null,
        TblStudentMasernInfo $MasernDocumentType = null,
        TblStudentMasernInfo $MasernCreatorType = null,
        $InsuranceNumber = ''
    ) {

        return (new Data($this->getBinding()))->createStudentMedicalRecord(
            $Disease,
            $Medication,
            $AttendingDoctor,
            $InsuranceState,
            $Insurance,
            $InsuranceNumber,
            $MasernDate,
            $MasernDocumentType,
            $MasernCreatorType
        );
    }

    /**
     * @param TblStudent              $tblStudent
     * @param TblStudentAgreementType $tblStudentAgreementType
     *
     * @return Service\Entity\TblStudentAgreement
     */
    public function insertStudentAgreement(
        TblStudent $tblStudent,
        TblStudentAgreementType $tblStudentAgreementType
    ) {

        return (new Data($this->getBinding()))->addStudentAgreement($tblStudent, $tblStudentAgreementType);
    }

    /**
     * @param        $Route
     * @param        $StationEntrance
     * @param        $StationExit
     * @param string $Remark
     * @param boolean $IsDriverStudent
     *
     * @return TblStudentTransport
     */
    public function insertStudentTransport(
        $Route,
        $StationEntrance,
        $StationExit,
        $Remark = '',
        $IsDriverStudent = false
    ) {

        return (new Data($this->getBinding()))->createStudentTransport(
            $Route,
            $StationEntrance,
            $StationExit,
            $Remark,
            $IsDriverStudent
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $Prefix
     * @param string $Identifier
     * @param TblStudentMedicalRecord|null $tblStudentMedicalRecord
     * @param TblStudentTransport|null $tblStudentTransport
     * @param TblStudentBilling|null $tblStudentBilling
     * @param TblStudentLocker|null $tblStudentLocker
     * @param TblStudentBaptism|null $tblStudentBaptism
     * @param TblStudentSpecialNeeds|null $tblStudentSpecialNeeds
     * @param string $SchoolAttendanceStartDate
     * @param TblStudentTechnicalSchool|null $tblStudentTechnicalSchool
     *
     * @return TblStudent
     */
    public function createStudent(
        TblPerson $tblPerson,
        $Prefix = '',
        $Identifier = '',
        TblStudentMedicalRecord $tblStudentMedicalRecord = null,
        TblStudentTransport $tblStudentTransport = null,
        TblStudentBilling $tblStudentBilling = null,
        TblStudentLocker $tblStudentLocker = null,
        TblStudentBaptism $tblStudentBaptism = null,
        TblStudentSpecialNeeds $tblStudentSpecialNeeds = null,
        TblStudentTechnicalSchool $tblStudentTechnicalSchool = null,
        $SchoolAttendanceStartDate = ''
    ) {

        return (new Data($this->getBinding()))->createStudent($tblPerson,
            $Prefix,
            $Identifier,
            $tblStudentMedicalRecord,
            $tblStudentTransport,
            $tblStudentBilling,
            $tblStudentLocker,
            $tblStudentBaptism,
            $tblStudentSpecialNeeds,
            $tblStudentTechnicalSchool,
            $SchoolAttendanceStartDate
        );
    }

    /**
     * @param TblStudent $tblStudent
     * @param $Prefix
     * @return bool|TblStudent
     */
    public function updateStudentPrefix(TblStudent $tblStudent, $Prefix)
    {

        return (new Data($this->getBinding()))->updateStudentPrefix($tblStudent,$Prefix);
    }

    /**
     * @param TblStudent $tblStudent
     * @param $Identifier
     * @return bool|TblStudent
     */
    public function updateStudentIdentifier(TblStudent $tblStudent, $Identifier)
    {

        return (new Data($this->getBinding()))->updateStudentIdentifier($tblStudent,$Identifier);
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblStudent
     */
    public function updateStudentBasic(TblPerson $tblPerson, $Meta)
    {

        $tblStudent = $tblPerson->getStudent(true);

        $Prefix = $Meta['Student']['Prefix'];
        $tblSetting = Consumer::useService()->getSetting('People', 'Meta', 'Student', 'Automatic_StudentNumber');

        if($tblSetting && $tblSetting->getValue() && !$tblStudent->getIdentifier()){
            // höchste Schülernummer setzen, wenn noch nicht vorhanden
            $biggestIdentifier = Student::useService()->getStudentMaxIdentifier();
            $Meta['Student']['Identifier'] = $biggestIdentifier + 1;
        } elseif($tblSetting && $tblSetting->getValue() && $tblStudent->getIdentifier()){
            // vorhandene Schülernummer übergeben
            $Meta['Student']['Identifier'] = $tblStudent->getIdentifier();
        } elseif(!isset($Meta['Student']['Identifier'])){
            // sollte nie vorkommen, wenn wird die vorhandene Einstellung übergeben (keine Änderung)
            $Meta['Student']['Identifier'] = $tblStudent->getIdentifier();
        }

        if ($tblStudent) {
            return (new Data($this->getBinding()))->updateStudentBasic(
                $tblStudent,
                $Prefix,
                $Meta['Student']['Identifier'],
                $Meta['Student']['SchoolAttendanceStartDate'],
                isset($Meta['Student']['HasMigrationBackground']),
                ($Meta['Student']['MigrationBackground'] ?? ''),
                isset($Meta['Student']['IsInPreparationDivisionForMigrants'])
            );
        } else {
            return (new Data($this->getBinding()))->createStudentBasic(
                $tblPerson,
                $Prefix,
                $Meta['Student']['Identifier'],
                $Meta['Student']['SchoolAttendanceStartDate'],
                isset($Meta['Student']['HasMigrationBackground']),
                ($Meta['Student']['MigrationBackground'] ?? ''),
                isset($Meta['Student']['IsInPreparationDivisionForMigrants'])
            );
        }
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return TblStudent
     */
    private function createStudentWithOnlyAutoIdentifier(TblPerson $tblPerson)
    {
        $identifier = '';
        $tblSetting = Consumer::useService()->getSetting('People', 'Meta', 'Student', 'Automatic_StudentNumber');
        if(($tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT))
        && // nur aktuelle Schüler sollen bei Datenerstellung (z.B. Transfer) bestückt werden
            Group::useService()->getMemberByPersonAndGroup($tblPerson, $tblGroup)
        && $tblSetting
        && $tblSetting->getValue()
        ){
            $biggestIdentifier = Student::useService()->getStudentMaxIdentifier();
            $identifier = $biggestIdentifier + 1;
        }

        return (new Data($this->getBinding()))->createStudent($tblPerson, '', $identifier);
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblStudent
     */
    public function updateStudentTransfer(TblPerson $tblPerson, $Meta)
    {

        // Student mit Automatischer Schülernummer anlegen falls noch nicht vorhanden
        $tblStudent = $tblPerson->getStudent(true);
        if (!$tblStudent) {
            $tblStudent = $this->createStudentWithOnlyAutoIdentifier($tblPerson);
        }

        if ($tblStudent) {
            $TransferTypeEnrollment = Student::useService()->getStudentTransferTypeByIdentifier('Enrollment');
            $tblStudentTransferByTypeEnrollment = Student::useService()->getStudentTransferByType(
                $tblStudent,
                $TransferTypeEnrollment
            );
            $tblCompany = Company::useService()->getCompanyById($Meta['Transfer'][$TransferTypeEnrollment->getId()]['School']);
            $tblStateCompany = false;
            $tblType = Type::useService()->getTypeById($Meta['Transfer'][$TransferTypeEnrollment->getId()]['Type']);
            $tblCourse = Course::useService()->getCourseById($Meta['Transfer'][$TransferTypeEnrollment->getId()]['Course']);
            $tblStudentSchoolEnrollmentType = $this->getStudentSchoolEnrollmentTypeById(
                $Meta['Transfer'][$TransferTypeEnrollment->getId()]['StudentSchoolEnrollmentType']
            );
            if ($tblStudentTransferByTypeEnrollment) {
                (new Data($this->getBinding()))->updateStudentTransfer(
                    $tblStudentTransferByTypeEnrollment,
                    $tblStudent,
                    $TransferTypeEnrollment,
                    $tblCompany ? $tblCompany : null,
                    $tblStateCompany ? $tblStateCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    $Meta['Transfer'][$TransferTypeEnrollment->getId()]['Date'],
                    $Meta['Transfer'][$TransferTypeEnrollment->getId()]['Remark'],
                    $tblStudentSchoolEnrollmentType ? $tblStudentSchoolEnrollmentType : null
                );
            } else {
                (new Data($this->getBinding()))->createStudentTransfer(
                    $tblStudent,
                    $TransferTypeEnrollment,
                    $tblCompany ? $tblCompany : null,
                    $tblStateCompany ? $tblStateCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    $Meta['Transfer'][$TransferTypeEnrollment->getId()]['Date'],
                    $Meta['Transfer'][$TransferTypeEnrollment->getId()]['Remark'],
                    $tblStudentSchoolEnrollmentType ? $tblStudentSchoolEnrollmentType : null
                );
            }

            $TransferTypeArrive = Student::useService()->getStudentTransferTypeByIdentifier('Arrive');
            $tblStudentTransferByTypeArrive = Student::useService()->getStudentTransferByType(
                $tblStudent,
                $TransferTypeArrive
            );
            $tblCompany = Company::useService()->getCompanyById($Meta['Transfer'][$TransferTypeArrive->getId()]['School']);
            if (isset($Meta['Transfer'][$TransferTypeArrive->getId()]['StateSchool'])) {
                $tblStateCompany = Company::useService()->getCompanyById($Meta['Transfer'][$TransferTypeArrive->getId()]['StateSchool']);
            } else {
                $tblStateCompany = false;
            }

            if (isset($Meta['Transfer'][$TransferTypeArrive->getId()]['Type'])) {
                $tblType = Type::useService()->getTypeById($Meta['Transfer'][$TransferTypeArrive->getId()]['Type']);
            } else {
                $tblType = false;
            }
            if (isset($Meta['Transfer'][$TransferTypeArrive->getId()]['Course'])) {
                $tblCourse = Course::useService()->getCourseById($Meta['Transfer'][$TransferTypeArrive->getId()]['Course']);
            } else {
                $tblCourse = false;
            }

            if ($tblStudentTransferByTypeArrive) {
                (new Data($this->getBinding()))->updateStudentTransfer(
                    $tblStudentTransferByTypeArrive,
                    $tblStudent,
                    $TransferTypeArrive,
                    $tblCompany ? $tblCompany : null,
                    $tblStateCompany ? $tblStateCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    $Meta['Transfer'][$TransferTypeArrive->getId()]['Date'],
                    $Meta['Transfer'][$TransferTypeArrive->getId()]['Remark']
                );
            } else {
                (new Data($this->getBinding()))->createStudentTransfer(
                    $tblStudent,
                    $TransferTypeArrive,
                    $tblCompany ? $tblCompany : null,
                    $tblStateCompany ? $tblStateCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    $Meta['Transfer'][$TransferTypeArrive->getId()]['Date'],
                    $Meta['Transfer'][$TransferTypeArrive->getId()]['Remark']
                );
            }

            $TransferTypeLeave = Student::useService()->getStudentTransferTypeByIdentifier('Leave');
            $tblStudentTransferByTypeLeave = Student::useService()->getStudentTransferByType(
                $tblStudent,
                $TransferTypeLeave
            );
            $tblCompany = Company::useService()->getCompanyById($Meta['Transfer'][$TransferTypeLeave->getId()]['School']);
            $tblStateCompany = false;
            $tblType = Type::useService()->getTypeById($Meta['Transfer'][$TransferTypeLeave->getId()]['Type']);
            $tblCourse = Course::useService()->getCourseById($Meta['Transfer'][$TransferTypeLeave->getId()]['Course']);
            if ($tblStudentTransferByTypeLeave) {
                (new Data($this->getBinding()))->updateStudentTransfer(
                    $tblStudentTransferByTypeLeave,
                    $tblStudent,
                    $TransferTypeLeave,
                    $tblCompany ? $tblCompany : null,
                    $tblStateCompany ? $tblStateCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    $Meta['Transfer'][$TransferTypeLeave->getId()]['Date'],
                    $Meta['Transfer'][$TransferTypeLeave->getId()]['Remark']
                );
            } else {
                (new Data($this->getBinding()))->createStudentTransfer(
                    $tblStudent,
                    $TransferTypeLeave,
                    $tblCompany ? $tblCompany : null,
                    $tblStateCompany ? $tblStateCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    $Meta['Transfer'][$TransferTypeLeave->getId()]['Date'],
                    $Meta['Transfer'][$TransferTypeLeave->getId()]['Remark']
                );
            }

//            $TransferTypeProcess = Student::useService()->getStudentTransferTypeByIdentifier('Process');
//            $tblStudentTransferByTypeProcess = Student::useService()->getStudentTransferByType(
//                $tblStudent,
//                $TransferTypeProcess
//            );
//            $tblCompany = Company::useService()->getCompanyById($Meta['Transfer'][$TransferTypeProcess->getId()]['School']);
//            $tblStateCompany = false;
//            // removed "Aktuelle Schulart"
////            $tblType = Type::useService()->getTypeById($Meta['Transfer'][$TransferTypeProcess->getId()]['Type']);
//            $tblType = false;
//            $tblCourse = Course::useService()->getCourseById($Meta['Transfer'][$TransferTypeProcess->getId()]['Course']);
//            if ($tblStudentTransferByTypeProcess) {
//                (new Data($this->getBinding()))->updateStudentTransfer(
//                    $tblStudentTransferByTypeProcess,
//                    $tblStudent,
//                    $TransferTypeProcess,
//                    $tblCompany ? $tblCompany : null,
//                    $tblStateCompany ? $tblStateCompany : null,
//                    $tblType ? $tblType : null,
//                    $tblCourse ? $tblCourse : null,
//                    '',
//                    $Meta['Transfer'][$TransferTypeProcess->getId()]['Remark']
//                );
//            } else {
//                (new Data($this->getBinding()))->createStudentTransfer(
//                    $tblStudent,
//                    $TransferTypeProcess,
//                    $tblCompany ? $tblCompany : null,
//                    $tblStateCompany ? $tblStateCompany : null,
//                    $tblType ? $tblType : null,
//                    $tblCourse ? $tblCourse : null,
//                    '',
//                    $Meta['Transfer'][$TransferTypeProcess->getId()]['Remark']
//                );
//            }

            return true;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblStudent
     */
    public function updateStudentProcess(TblPerson $tblPerson, $Meta)
    {

        // Student mit Automatischer Schülernummer anlegen falls noch nicht vorhanden
        $tblStudent = $tblPerson->getStudent(true);
        if (!$tblStudent) {
            $tblStudent = $this->createStudentWithOnlyAutoIdentifier($tblPerson);
        }

        if ($tblStudent) {
            $TransferTypeProcess = Student::useService()->getStudentTransferTypeByIdentifier('Process');
            $tblStudentTransferByTypeProcess = Student::useService()->getStudentTransferByType(
                $tblStudent,
                $TransferTypeProcess
            );
            $tblCompany = Company::useService()->getCompanyById($Meta['Transfer'][$TransferTypeProcess->getId()]['School']);
            $tblStateCompany = false;
            // removed "Aktuelle Schulart"
//            $tblType = Type::useService()->getTypeById($Meta['Transfer'][$TransferTypeProcess->getId()]['Type']);
            $tblType = false;
            $tblCourse = Course::useService()->getCourseById($Meta['Transfer'][$TransferTypeProcess->getId()]['Course']);
            if ($tblStudentTransferByTypeProcess) {
                (new Data($this->getBinding()))->updateStudentTransfer(
                    $tblStudentTransferByTypeProcess,
                    $tblStudent,
                    $TransferTypeProcess,
                    $tblCompany ? $tblCompany : null,
                    $tblStateCompany ? $tblStateCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    '',
                    $Meta['Transfer'][$TransferTypeProcess->getId()]['Remark']
                );
            } else {
                (new Data($this->getBinding()))->createStudentTransfer(
                    $tblStudent,
                    $TransferTypeProcess,
                    $tblCompany ? $tblCompany : null,
                    $tblStateCompany ? $tblStateCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    '',
                    $Meta['Transfer'][$TransferTypeProcess->getId()]['Remark']
                );
            }

            return true;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblStudent
     */
    public function updateStudentTransferArrive(TblPerson $tblPerson, $Meta)
    {

        // Student mit Automatischer Schülernummer anlegen falls noch nicht vorhanden
        $tblStudent = $tblPerson->getStudent(true);
        if (!$tblStudent) {
            $tblStudent = $this->createStudentWithOnlyAutoIdentifier($tblPerson);
        }

        if ($tblStudent) {
            $TransferTypeArrive = Student::useService()->getStudentTransferTypeByIdentifier('Arrive');
            $tblStudentTransferByTypeArrive = Student::useService()->getStudentTransferByType(
                $tblStudent,
                $TransferTypeArrive
            );
            $tblCompany = Company::useService()->getCompanyById($Meta['Transfer'][$TransferTypeArrive->getId()]['School']);
            if (isset($Meta['Transfer'][$TransferTypeArrive->getId()]['StateSchool'])) {
                $tblStateCompany = Company::useService()->getCompanyById($Meta['Transfer'][$TransferTypeArrive->getId()]['StateSchool']);
            } else {
                $tblStateCompany = false;
            }

            if (isset($Meta['Transfer'][$TransferTypeArrive->getId()]['Type'])) {
                $tblType = Type::useService()->getTypeById($Meta['Transfer'][$TransferTypeArrive->getId()]['Type']);
            } else {
                $tblType = false;
            }
            if (isset($Meta['Transfer'][$TransferTypeArrive->getId()]['Course'])) {
                $tblCourse = Course::useService()->getCourseById($Meta['Transfer'][$TransferTypeArrive->getId()]['Course']);
            } else {
                $tblCourse = false;
            }

            if ($tblStudentTransferByTypeArrive) {
                (new Data($this->getBinding()))->updateStudentTransfer(
                    $tblStudentTransferByTypeArrive,
                    $tblStudent,
                    $TransferTypeArrive,
                    $tblCompany ? $tblCompany : null,
                    $tblStateCompany ? $tblStateCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    $Meta['Transfer'][$TransferTypeArrive->getId()]['Date'],
                    $Meta['Transfer'][$TransferTypeArrive->getId()]['Remark']
                );
            } else {
                (new Data($this->getBinding()))->createStudentTransfer(
                    $tblStudent,
                    $TransferTypeArrive,
                    $tblCompany ? $tblCompany : null,
                    $tblStateCompany ? $tblStateCompany : null,
                    $tblType ? $tblType : null,
                    $tblCourse ? $tblCourse : null,
                    $Meta['Transfer'][$TransferTypeArrive->getId()]['Date'],
                    $Meta['Transfer'][$TransferTypeArrive->getId()]['Remark']
                );
            }
            return true;
        }
        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblStudent
     */
    public function updateStudentMedicalRecord(TblPerson $tblPerson, $Meta)
    {

        // Student mit Automatischer Schülernummer anlegen falls noch nicht vorhanden
        $tblStudent = $tblPerson->getStudent(true);
        if (!$tblStudent) {
            $tblStudent = $this->createStudentWithOnlyAutoIdentifier($tblPerson);
        }
        // nicht ausgewählt = 0 -> false
        if($Meta['MedicalRecord']['Masern']['DocumentType']){
            $DocumentType = $this->getStudentMasernInfoById($Meta['MedicalRecord']['Masern']['DocumentType']);
        }
        if(!isset($DocumentType) || !$DocumentType){
            $DocumentType = null;
        }
        // nicht ausgewählt = 0 -> false
        if($Meta['MedicalRecord']['Masern']['CreatorType']){
            $CreatorType = $this->getStudentMasernInfoById($Meta['MedicalRecord']['Masern']['CreatorType']);
        }
        if(!isset($CreatorType) || !$CreatorType){
            $CreatorType = null;
        }
        $MasernDate = null;
        if(isset($Meta['MedicalRecord']['Masern']['Date']) && $Meta['MedicalRecord']['Masern']['Date']){
            $MasernDate = new DateTime($Meta['MedicalRecord']['Masern']['Date']);
        }

        if ($tblStudent) {
            if (($tblStudentMedicalRecord = $tblStudent->getTblStudentMedicalRecord())) {
                (new Data($this->getBinding()))->updateStudentMedicalRecord(
                    $tblStudent->getTblStudentMedicalRecord(),
                    $Meta['MedicalRecord']['Disease'],
                    $Meta['MedicalRecord']['Medication'],
                    $Meta['MedicalRecord']['AttendingDoctor'],
                    $Meta['MedicalRecord']['Insurance']['State'],
                    $Meta['MedicalRecord']['Insurance']['Company'],
                    $Meta['MedicalRecord']['Insurance']['Number'],
                    $MasernDate,
                    $DocumentType,
                    $CreatorType
                );
            } else {
                $tblStudentMedicalRecord = (new Data($this->getBinding()))->createStudentMedicalRecord(
                    $Meta['MedicalRecord']['Disease'],
                    $Meta['MedicalRecord']['Medication'],
                    $Meta['MedicalRecord']['AttendingDoctor'],
                    $Meta['MedicalRecord']['Insurance']['State'],
                    $Meta['MedicalRecord']['Insurance']['Company'],
                    $Meta['MedicalRecord']['Insurance']['Number'],
                    $MasernDate,
                    $DocumentType,
                    $CreatorType
                );

                if ($tblStudentMedicalRecord) {
                    (new Data($this->getBinding()))->updateStudentField(
                        $tblStudent,
                        $tblStudentMedicalRecord,
                        $tblStudent->getTblStudentTransport() ? $tblStudent->getTblStudentTransport() : null,
                        $tblStudent->getTblStudentBilling() ? $tblStudent->getTblStudentBilling() : null,
                        $tblStudent->getTblStudentLocker() ? $tblStudent->getTblStudentLocker() : null,
                        $tblStudent->getTblStudentBaptism() ? $tblStudent->getTblStudentBaptism() : null,
                        $tblStudent->getTblStudentSpecialNeeds() ? $tblStudent->getTblStudentSpecialNeeds() : null,
                        $tblStudent->getTblStudentTechnicalSchool() ? $tblStudent->getTblStudentTechnicalSchool() : null
                    );
                } else {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblStudent
     */
    public function updateStudentGeneral(TblPerson $tblPerson, $Meta)
    {

        // Student mit Automatischer Schülernummer anlegen falls noch nicht vorhanden
        $tblStudent = $tblPerson->getStudent(true);
        if (!$tblStudent) {
            $tblStudent = $this->createStudentWithOnlyAutoIdentifier($tblPerson);
        }

        if ($tblStudent) {
            if (($tblStudentLocker = $tblStudent->getTblStudentLocker())) {
                (new Data($this->getBinding()))->updateStudentLocker(
                    $tblStudent->getTblStudentLocker(),
                    $Meta['Additional']['Locker']['Number'],
                    $Meta['Additional']['Locker']['Location'],
                    $Meta['Additional']['Locker']['Key'],
                    $Meta['Additional']['Locker']['CombinationLockNumber']
                );
            } else {
                $tblStudentLocker = (new Data($this->getBinding()))->createStudentLocker(
                    $Meta['Additional']['Locker']['Number'],
                    $Meta['Additional']['Locker']['Location'],
                    $Meta['Additional']['Locker']['Key'],
                    $Meta['Additional']['Locker']['CombinationLockNumber']
                );
            }

            if (($tblStudentBaptism = $tblStudent->getTblStudentBaptism())) {
                (new Data($this->getBinding()))->updateStudentBaptism(
                    $tblStudent->getTblStudentBaptism(),
                    $Meta['Additional']['Baptism']['Date'],
                    $Meta['Additional']['Baptism']['Location']
                );
            } else {
                $tblStudentBaptism = (new Data($this->getBinding()))->createStudentBaptism(
                    $Meta['Additional']['Baptism']['Date'],
                    $Meta['Additional']['Baptism']['Location']
                );
            }

            if (($tblStudentTransport = $tblStudent->getTblStudentTransport())) {
                (new Data($this->getBinding()))->updateStudentTransport(
                    $tblStudent->getTblStudentTransport(),
                    $Meta['Transport']['Route'],
                    $Meta['Transport']['Station']['Entrance'],
                    $Meta['Transport']['Station']['Exit'],
                    $Meta['Transport']['Remark'],
                    isset($Meta['Transport']['IsDriverStudent'])
                );
            } else {
                $tblStudentTransport = (new Data($this->getBinding()))->createStudentTransport(
                    $Meta['Transport']['Route'],
                    $Meta['Transport']['Station']['Entrance'],
                    $Meta['Transport']['Station']['Exit'],
                    $Meta['Transport']['Remark'],
                    isset($Meta['Transport']['IsDriverStudent'])
                );
            }

//            $SiblingRank = Relationship::useService()->getSiblingRankById($Meta['Billing']);
//            if ($tblStudentBilling = $tblStudent->getTblStudentBilling()) {
//                (new Data($this->getBinding()))->updateStudentBilling(
//                    $tblStudentBilling,
//                    $SiblingRank ? $SiblingRank : null
//                );
//            } else {
//                $tblStudentBilling = (new Data($this->getBinding()))->createStudentBilling(
//                    $SiblingRank ? $SiblingRank : null
//                );
//            }

            (new Data($this->getBinding()))->updateStudentField(
                $tblStudent,
                $tblStudent->getTblStudentMedicalRecord() ? $tblStudent->getTblStudentMedicalRecord() : null,
                $tblStudentTransport ? $tblStudentTransport : null,
                null,
//                $tblStudentBilling ? $tblStudentBilling : null,
                $tblStudentLocker ? $tblStudentLocker : null,
                $tblStudentBaptism ? $tblStudentBaptism : null,
                $tblStudent->getTblStudentSpecialNeeds() ? $tblStudent->getTblStudentSpecialNeeds() : null,
                $tblStudent->getTblStudentTechnicalSchool() ? $tblStudent->getTblStudentTechnicalSchool() : null
            );

            /*
             * Liberation
             */
            $tblStudentLiberationAllByStudent = $this->getStudentLiberationAllByStudent($tblStudent);
            if ($tblStudentLiberationAllByStudent) {
                foreach ($tblStudentLiberationAllByStudent as $tblStudentLiberation) {
                    (new Data($this->getBinding()))->removeStudentLiberation($tblStudentLiberation);
                }
            }
            if (isset( $Meta['Liberation'] )) {
                foreach ($Meta['Liberation'] as $Category => $Type) {
                    $tblStudentLiberationCategory = $this->getStudentLiberationTypeById($Category);
                    if ($tblStudentLiberationCategory) {
                        $tblStudentLiberationType = $this->getStudentLiberationTypeById($Type);
                        if ($tblStudentLiberationType) {
                            (new Data($this->getBinding()))->addStudentLiberation($tblStudent,
                                $tblStudentLiberationType);
                        }
                    }
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $Meta
     *
     * @return bool
     */
    public function updateStudentAgreement(TblPerson $tblPerson, $Meta, $isUnlocked = false)
    {
        // Student mit Automatischer Schülernummer anlegen falls noch nicht vorhanden
        $tblStudent = $tblPerson->getStudent(true);
        if (!$tblStudent) {
            $tblStudent = $this->createStudentWithOnlyAutoIdentifier($tblPerson);
        }

        if ($tblStudent) {
            /*
             * Agreement
             */
            $tblStudentAgreementAllByStudent = $this->getStudentAgreementAllByStudent($tblStudent);
            if ($tblStudentAgreementAllByStudent) {
                array_walk($tblStudentAgreementAllByStudent, function (TblStudentAgreement $tblStudentAgreement) use (&$isUnlocked){
                    $tblCategory = false;
                    if(($tblType = $tblStudentAgreement->getTblStudentAgreementType())){
                        $tblCategory = $tblType->getTblStudentAgreementCategory();
                    }
                    if(!$isUnlocked){
                        if (!isset($Meta['Agreement'][$tblCategory->getId()][$tblType->getId()])) {
                            (new Data($this->getBinding()))->removeStudentAgreement($tblStudentAgreement);
                        }
                    } else {
                        if (!isset($Meta['Agreement'][$tblCategory->getId()][$tblType->getId()]) && $tblType->getIsUnlocked()) {
                            (new Data($this->getBinding()))->removeStudentAgreement($tblStudentAgreement);
                        }
                    }
                });
            }
            if (isset($Meta['Agreement'])) {
                foreach ($Meta['Agreement'] as $Category => $Items) {
                    $tblStudentAgreementCategory = $this->getStudentAgreementCategoryById($Category);
                    if ($tblStudentAgreementCategory) {
                        foreach ($Items as $Type => $Value) {
                            $tblStudentAgreementType = $this->getStudentAgreementTypeById($Type);
                            if ($tblStudentAgreementType) {
                                (new Data($this->getBinding()))->addStudentAgreement($tblStudent,
                                    $tblStudentAgreementType);
                            }
                        }
                    }
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblStudent
     */
    public function updateStudentSubject(TblPerson $tblPerson, $Meta)
    {

        // Student mit Automatischer Schülernummer anlegen falls noch nicht vorhanden
        $tblStudent = $tblPerson->getStudent(true);
        if (!$tblStudent) {
            $tblStudent = $this->createStudentWithOnlyAutoIdentifier($tblPerson);
        }

        if ($tblStudent) {
            $tblStudentSubjectAll = $this->getStudentSubjectAllByStudent($tblStudent);
            if ($tblStudentSubjectAll) {
                foreach ($tblStudentSubjectAll as $tblStudentSubject) {
                    if (!Subject::useService()->getSubjectById(
                        $Meta['Subject'][$tblStudentSubject->getTblStudentSubjectType()->getId()]
                        [$tblStudentSubject->getTblStudentSubjectRanking()->getId()])
                    ) {
                        (new Data($this->getBinding()))->removeStudentSubject($tblStudentSubject);
                    }
                }
            }
            if (isset( $Meta['Subject'] )) {
                foreach ($Meta['Subject'] as $Category => $Items) {
                    $tblStudentSubjectType = $this->getStudentSubjectTypeById($Category);
                    if ($tblStudentSubjectType) {
                        foreach ($Items as $Ranking => $Type) {
                            $tblStudentSubjectRanking = $this->getStudentSubjectRankingById($Ranking);
                            $tblSubject = Subject::useService()->getSubjectById($Type);
                            if ($tblSubject) {
                                // From & Till
                                $LevelFrom = null;
                                $LevelTill = null;
                                if (isset( $Meta['SubjectLevelFrom'] ) && isset( $Meta['SubjectLevelFrom'][$Category][$Ranking] )) {
                                    if ($Meta['SubjectLevelFrom'][$Category][$Ranking]) {
                                        $LevelFrom = intval($Meta['SubjectLevelFrom'][$Category][$Ranking]);
                                    }
                                }
                                if (isset( $Meta['SubjectLevelTill'] ) && isset( $Meta['SubjectLevelTill'][$Category][$Ranking] )) {
                                    if ($Meta['SubjectLevelTill'][$Category][$Ranking]) {
                                        $LevelTill = intval($Meta['SubjectLevelTill'][$Category][$Ranking]);
                                    }
                                }

                                $this->addStudentSubject(
                                    $tblStudent,
                                    $tblStudentSubjectType,
                                    $tblStudentSubjectRanking ?: null,
                                    $tblSubject,
                                    $LevelFrom,
                                    $LevelTill
                                );
                            }
                        }
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getStudentMaxIdentifier()
    {

        $tblStudentList = (new Data($this->getBinding()))->getStudentAll();
        $result = 0;
        if($tblStudentList) {
            foreach($tblStudentList as $tblStudent){
                if(is_numeric($tblStudent->getIdentifier()) && $tblStudent->getIdentifier() > $result){
                    $result = $tblStudent->getIdentifier();
                }
            }
        }
        return $result;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentMedicalRecord
     */
    public function getStudentMedicalRecordById($Id)
    {

        return (new Data($this->getBinding()))->getStudentMedicalRecordById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentInsuranceState
     */
    public function getStudentInsuranceStateById($Id)
    {

        return (new Data($this->getBinding()))->getStudentInsuranceStateById($Id);
    }

    /**
     * @param $Name
     *
     * @return false|TblStudentInsuranceState
     */
    public function getStudentInsuranceStateByName($Name)
    {
        return (new Data($this->getBinding()))->getStudentInsuranceStateByName($Name);
    }

    /**
     * @param int $Id
     *
     * @return false|TblStudentMasernInfo
     */
    public function getStudentMasernInfoById($Id)
    {
        return (new Data($this->getBinding()))->getStudentMasernInfoById($Id);
    }

    /**
     * @param string $Type
     * TblStudentMasernInfo::TYPE_DOCUMENT || TblStudentMasernInfo::TYPE_CREATOR
     *
     * @return false|TblStudentMasernInfo[]
     */
    public function getStudentMasernInfoByType($Type = TblStudentMasernInfo::TYPE_DOCUMENT)
    {
        return (new Data($this->getBinding()))->getStudentMasernInfoByType($Type);
    }

    /**
     * @return bool|TblStudentInsuranceState[]
     */
    public function getStudentInsuranceStateAll()
    {

        return (new Data($this->getBinding()))->getStudentInsuranceStateAll();
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentBaptism
     */
    public function getStudentBaptismById($Id)
    {

        return (new Data($this->getBinding()))->getStudentBaptismById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentBilling
     */
    public function getStudentBillingById(
        $Id
    ) {

        return (new Data($this->getBinding()))->getStudentBillingById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentLocker
     */
    public function getStudentLockerById(
        $Id
    ) {

        return (new Data($this->getBinding()))->getStudentLockerById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransport
     */
    public function getStudentTransportById(
        $Id
    ) {

        return (new Data($this->getBinding()))->getStudentTransportById($Id);
    }

    /**
     * @deprecated
     *
     * @param TblPerson $tblPerson
     * @param string $Prefix
     *
     * @return string
     */
    public function getDisplayCurrentDivisionListByPerson(TblPerson $tblPerson, $Prefix = 'Klasse' ): string
    {
        $result = DivisionCourse::useService()->getCurrentMainCoursesByPersonAndDate($tblPerson);
        if (!$Prefix) {
            str_replace('Klasse: ', '', $result);
            str_replace('Stammgruppe: ', '', $result);
        }

        return $result;
    }

    /**
     * @param TblStudentSubject $tblStudentSubject
     */
    public function removeStudentSubject(TblStudentSubject $tblStudentSubject)
    {
        ( new Data($this->getBinding()) )->removeStudentSubject($tblStudentSubject);
    }

    /**
     * @param TblStudent $tblStudent
     * @param TblStudentSubjectType $tblStudentSubjectType
     * @param TblStudentSubjectRanking $tblStudentSubjectRanking
     * @param TblSubject $tblSubject
     * @param int|null $LevelFrom
     * @param int|null $LevelTill
     *
     * @return TblStudentSubject
     */
    public function addStudentSubject(
        TblStudent $tblStudent,
        TblStudentSubjectType $tblStudentSubjectType,
        TblStudentSubjectRanking $tblStudentSubjectRanking,
        TblSubject $tblSubject,
        ?int $LevelFrom = null,
        ?int $LevelTill = null
    ): TblStudentSubject {
        return ( new Data($this->getBinding()) )->addStudentSubject(
            $tblStudent,
            $tblStudentSubjectType,
            $tblStudentSubjectRanking,
            $tblSubject,
            $LevelFrom,
            $LevelTill
        );
    }

    /**
     * @param array $EntityList
     * @param array $ProtocolList
     *
     * @return bool
     */
    public function bulkSaveEntityList($EntityList = array(), $ProtocolList = array())
    {

        if (!empty($EntityList)) {
            return (new Data($this->getBinding()))->bulkSaveEntityList($EntityList, $ProtocolList);
        }

        return false;
    }

    /**
     * @param $IsHeavyMultipleHandicapped
     * @param $IncreaseFactorHeavyMultipleHandicappedSchool
     * @param $IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities
     * @param $RemarkHeavyMultipleHandicapped
     * @param $DegreeOfHandicap
     * @param $Sign
     * @param $ValidTo
     * @param TblStudentSpecialNeedsLevel|null $tblStudentSpecialNeedsLevel
     *
     * @return TblStudentSpecialNeeds
     */
    public function createStudentSpecialNeeds(
        $IsHeavyMultipleHandicapped,
        $IncreaseFactorHeavyMultipleHandicappedSchool,
        $IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities,
        $RemarkHeavyMultipleHandicapped,
        $DegreeOfHandicap,
        $Sign,
        $ValidTo,
        TblStudentSpecialNeedsLevel $tblStudentSpecialNeedsLevel = null
    ) {
        return (new Data($this->getBinding()))->createStudentSpecialNeeds(
            $IsHeavyMultipleHandicapped,
            $IncreaseFactorHeavyMultipleHandicappedSchool,
            $IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities,
            $RemarkHeavyMultipleHandicapped,
            $DegreeOfHandicap,
            $Sign,
            $ValidTo,
            $tblStudentSpecialNeedsLevel
        );
    }

    /**
     * @param string $specialNeedsLevel
     *
     * @return TblStudentSpecialNeeds|null
     */
    public function insertStudentSpecialNeedsLevel(string $specialNeedsLevel = ''): ?TblStudentSpecialNeeds
    {
        switch ($specialNeedsLevel){
            case 'US':
                $specialNeedsLevel = 'Unterstufe';
                break;
            case 'MS':
                $specialNeedsLevel = 'Mittelstufe';
                break;
            case 'OS':
                $specialNeedsLevel = 'Oberstufe';
                break;
            case 'WS':
                $specialNeedsLevel = 'Werkstufe';
                break;
        }
        if(($tblStudentSpecialNeedsLevel = Student::useService()->getStudentSpecialNeedsLevelByName($specialNeedsLevel))){
            return (new Data($this->getBinding()))->insertStudentSpecialNeedsLevel($tblStudentSpecialNeedsLevel);
        }
        return null;
    }

    /**
     * @param TblStudentMedicalRecord $tblStudentMedicalRecord
     * @param $Disease
     * @param $Medication
     * @param $AttendingDoctor
     * @param $InsuranceState
     * @param $Insurance
     * @param $InsuranceNumber
     * @param null $MasernDate
     * @param TblStudentMasernInfo|null $MasernDocumentType
     * @param TblStudentMasernInfo|null $MasernCreatorType
     *
     * @return bool
     */
    public function updateStudentMedicalRecordService(
        TblStudentMedicalRecord $tblStudentMedicalRecord,
        $Disease,
        $Medication,
        $AttendingDoctor,
        $InsuranceState,
        $Insurance,
        $InsuranceNumber,
        $MasernDate = null,
        TblStudentMasernInfo $MasernDocumentType = null,
        TblStudentMasernInfo $MasernCreatorType = null
    ) {
        return (new Data($this->getBinding()))->updateStudentMedicalRecord(
            $tblStudentMedicalRecord,
            $Disease,
            $Medication,
            $AttendingDoctor,
            $InsuranceState,
            $Insurance,
            $InsuranceNumber,
            $MasernDate,
            $MasernDocumentType,
            $MasernCreatorType
        );
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblStudent
     */
    public function updateStudentSpecialNeeds(TblPerson $tblPerson, $Meta)
    {

        // Student mit Automatischer Schülernummer anlegen falls noch nicht vorhanden
        $tblStudent = $tblPerson->getStudent(true);
        if (!$tblStudent) {
            $tblStudent = $this->createStudentWithOnlyAutoIdentifier($tblPerson);
        }

        if ($tblStudent) {
            $tblStudentSpecialNeedsLevel = $this->getStudentSpecialNeedsLevelById($Meta['SpecialNeeds']['TblStudentSpecialNeedsLevel']);

            if (($tblStudentSpecialNeeds = $tblStudent->getTblStudentSpecialNeeds())) {
                (new Data($this->getBinding()))->updateStudentSpecialNeeds(
                    $tblStudentSpecialNeeds,
                    isset($Meta['SpecialNeeds']['IsHeavyMultipleHandicapped']),
                    $Meta['SpecialNeeds']['IncreaseFactorHeavyMultipleHandicappedSchool'],
                    $Meta['SpecialNeeds']['IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities'],
                    $Meta['SpecialNeeds']['RemarkHeavyMultipleHandicapped'],
                    $Meta['SpecialNeeds']['DegreeOfHandicap'],
                    $Meta['SpecialNeeds']['Sign'],
                    $Meta['SpecialNeeds']['ValidTo'],
                    $tblStudentSpecialNeedsLevel ? $tblStudentSpecialNeedsLevel : null
                );
            } else {

                $tblStudentSpecialNeeds = (new Data($this->getBinding()))->createStudentSpecialNeeds(
                    isset($Meta['SpecialNeeds']['IsHeavyMultipleHandicapped']),
                    $Meta['SpecialNeeds']['IncreaseFactorHeavyMultipleHandicappedSchool'],
                    $Meta['SpecialNeeds']['IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities'],
                    $Meta['SpecialNeeds']['RemarkHeavyMultipleHandicapped'],
                    $Meta['SpecialNeeds']['DegreeOfHandicap'],
                    $Meta['SpecialNeeds']['Sign'],
                    $Meta['SpecialNeeds']['ValidTo'],
                    $tblStudentSpecialNeedsLevel ? $tblStudentSpecialNeedsLevel : null
                );

                if ($tblStudentSpecialNeeds) {
                    (new Data($this->getBinding()))->updateStudentField(
                        $tblStudent,
                        $tblStudent->getTblStudentMedicalRecord() ? $tblStudent->getTblStudentMedicalRecord() : null,
                        $tblStudent->getTblStudentTransport() ? $tblStudent->getTblStudentTransport() : null,
                        $tblStudent->getTblStudentBilling() ? $tblStudent->getTblStudentBilling() : null,
                        $tblStudent->getTblStudentLocker() ? $tblStudent->getTblStudentLocker() : null,
                        $tblStudent->getTblStudentBaptism() ? $tblStudent->getTblStudentBaptism() : null,
                        $tblStudentSpecialNeeds,
                        $tblStudent->getTblStudentTechnicalSchool() ? $tblStudent->getTblStudentTechnicalSchool() : null
                    );
                } else {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblStudent
     */
    public function updateStudentTechnicalSchool(TblPerson $tblPerson, $Meta)
    {
        // Student mit Automatischer Schülernummer anlegen falls noch nicht vorhanden
        $tblStudent = $tblPerson->getStudent(true);
        if (!$tblStudent) {
            $tblStudent = $this->createStudentWithOnlyAutoIdentifier($tblPerson);
        }

        if ($tblStudent) {
            $tblTechnicalCourse = Course::useService()->getTechnicalCourseById($Meta['TechnicalSchool']['serviceTblTechnicalCourse']);
            $tblSchoolDiploma = Course::useService()->getSchoolDiplomaById($Meta['TechnicalSchool']['serviceTblSchoolDiploma']);
            $tblSchoolType = Type::useService()->getTypeById($Meta['TechnicalSchool']['serviceTblSchoolType']);
            $tblTechnicalDiploma = Course::useService()->getTechnicalDiplomaById($Meta['TechnicalSchool']['serviceTblTechnicalDiploma']);
            $tblTechnicalType = Type::useService()->getTypeById($Meta['TechnicalSchool']['serviceTblTechnicalType']);

            $tblStudentTenseOfLesson = $this->getStudentTenseOfLessonById($Meta['TechnicalSchool']['tblStudentTenseOfLesson']);
            $tblStudentTrainingStatus = $this->getStudentTrainingStatusById($Meta['TechnicalSchool']['tblStudentTrainingStatus']);

            $tblTechnicalSubjectArea = Course::useService()->getTechnicalSubjectAreaById($Meta['TechnicalSchool']['serviceTblTechnicalSubjectArea']);

            if (($tblStudentTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())) {
                (new Data($this->getBinding()))->updateStudentTechnicalSchool(
                    $tblStudentTechnicalSchool,
                    $Meta['TechnicalSchool']['PraxisLessons'],
                    $Meta['TechnicalSchool']['DurationOfTraining'],
                    $Meta['TechnicalSchool']['Remark'],
                    $tblTechnicalCourse ? $tblTechnicalCourse : null,
                    $tblSchoolDiploma ? $tblSchoolDiploma : null,
                    $tblSchoolType ? $tblSchoolType : null,
                    $tblTechnicalDiploma ? $tblTechnicalDiploma : null,
                    $tblTechnicalType ? $tblTechnicalType : null,
                    $tblStudentTenseOfLesson ? $tblStudentTenseOfLesson : null,
                    $tblStudentTrainingStatus ? $tblStudentTrainingStatus : null,
                    $Meta['TechnicalSchool']['YearOfSchoolDiploma'],
                    $Meta['TechnicalSchool']['YearOfTechnicalDiploma'],
                    $tblTechnicalSubjectArea ? $tblTechnicalSubjectArea : null,
                    isset($Meta['TechnicalSchool']['HasFinancialAid']),
                    $Meta['TechnicalSchool']['FinancialAidApplicationYear'],
                    $Meta['TechnicalSchool']['FinancialAidBureau']
                );
            } else {

                $tblStudentTechnicalSchool = (new Data($this->getBinding()))->createStudentTechnicalSchool(
                    $Meta['TechnicalSchool']['PraxisLessons'],
                    $Meta['TechnicalSchool']['DurationOfTraining'],
                    $Meta['TechnicalSchool']['Remark'],
                    $tblTechnicalCourse ? $tblTechnicalCourse : null,
                    $tblSchoolDiploma ? $tblSchoolDiploma : null,
                    $tblSchoolType ? $tblSchoolType : null,
                    $tblTechnicalDiploma ? $tblTechnicalDiploma : null,
                    $tblTechnicalType ? $tblTechnicalType : null,
                    $tblStudentTenseOfLesson ? $tblStudentTenseOfLesson : null,
                    $tblStudentTrainingStatus ? $tblStudentTrainingStatus : null,
                    $Meta['TechnicalSchool']['YearOfSchoolDiploma'],
                    $Meta['TechnicalSchool']['YearOfTechnicalDiploma'],
                    $tblTechnicalSubjectArea ? $tblTechnicalSubjectArea : null,
                    isset($Meta['TechnicalSchool']['HasFinancialAid']),
                    $Meta['TechnicalSchool']['FinancialAidApplicationYear'],
                    $Meta['TechnicalSchool']['FinancialAidBureau']
                );

                if ($tblStudentTechnicalSchool) {
                    (new Data($this->getBinding()))->updateStudentField(
                        $tblStudent,
                        $tblStudent->getTblStudentMedicalRecord() ? $tblStudent->getTblStudentMedicalRecord() : null,
                        $tblStudent->getTblStudentTransport() ? $tblStudent->getTblStudentTransport() : null,
                        $tblStudent->getTblStudentBilling() ? $tblStudent->getTblStudentBilling() : null,
                        $tblStudent->getTblStudentLocker() ? $tblStudent->getTblStudentLocker() : null,
                        $tblStudent->getTblStudentBaptism() ? $tblStudent->getTblStudentBaptism() : null,
                        $tblStudent->getTblStudentSpecialNeeds() ? $tblStudent->getTblStudentSpecialNeeds() : null,
                        $tblStudentTechnicalSchool
                    );
                } else {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblTechnicalCourse
     */
    public function getTechnicalCourseByPerson(TblPerson $tblPerson)
    {
        if (($tblStudent = $tblPerson->getStudent())
            && ($tblTechnicalSchool = $tblStudent->getTblStudentTechnicalSchool())
        ) {
            return $tblTechnicalSchool->getServiceTblTechnicalCourse();
        }

        return  false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    public function getTechnicalCourseGenderNameByPerson(TblPerson $tblPerson)
    {
        if (($tblTechnicalCourse = $this->getTechnicalCourseByPerson($tblPerson))) {
            $tblCommonGender = $tblPerson->getGender();
            return $tblTechnicalCourse->getDisplayName($tblCommonGender ? $tblCommonGender : null);
        }

        return '';
    }

    /**
     * @param TblStudent $tblStudent
     * @param TblStudentLiberationType $tblStudentLiberationType
     *
     * @return Service\Entity\TblStudentLiberation
     */
    public function addStudentLiberation(
        TblStudent $tblStudent,
        TblStudentLiberationType $tblStudentLiberationType
    ) {
        return (new Data($this->getBinding()))->addStudentLiberation($tblStudent, $tblStudentLiberationType);
    }

    /**
     * @param $praxisLessons
     * @param $durationOfTraining
     * @param $remark
     * @param TblTechnicalCourse|null $tblTechnicalCourse
     * @param TblSchoolDiploma|null $tblSchoolDiploma
     * @param TblType|null $tblSchoolType
     * @param TblTechnicalDiploma|null $tblTechnicalDiploma
     * @param TblType|null $tblTechnicalType
     * @param TblStudentTenseOfLesson|null $tblStudentTenseOfLesson
     * @param TblStudentTrainingStatus|null $tblStudentTrainingStatus
     * @param string $yearOfSchoolDiploma
     * @param string $yearOfTechnicalDiploma
     * @param TblTechnicalSubjectArea|null $tblTechnicalSubjectArea
     * @param bool $hasFinancialAid
     * @param string $financialAidApplicationYear
     * @param string $financialAidBureau
     *
     * @return TblStudentTechnicalSchool
     */
    public function insertStudentTechnicalSchool(
        $praxisLessons,
        $durationOfTraining,
        $remark,
        TblTechnicalCourse $tblTechnicalCourse = null,
        TblSchoolDiploma $tblSchoolDiploma = null,
        TblType $tblSchoolType = null,
        TblTechnicalDiploma $tblTechnicalDiploma = null,
        TblType $tblTechnicalType = null,
        TblStudentTenseOfLesson $tblStudentTenseOfLesson = null,
        TblStudentTrainingStatus $tblStudentTrainingStatus = null,
        $yearOfSchoolDiploma = '',
        $yearOfTechnicalDiploma = '',
        TblTechnicalSubjectArea $tblTechnicalSubjectArea = null,
        $hasFinancialAid = false,
        $financialAidApplicationYear = '',
        $financialAidBureau = ''
    ) {
        return (new Data($this->getBinding()))->createStudentTechnicalSchool(
            $praxisLessons,
            $durationOfTraining,
            $remark,
            $tblTechnicalCourse,
            $tblSchoolDiploma,
            $tblSchoolType,
            $tblTechnicalDiploma,
            $tblTechnicalType,
            $tblStudentTenseOfLesson,
            $tblStudentTrainingStatus,
            $yearOfSchoolDiploma,
            $yearOfTechnicalDiploma,
            $tblTechnicalSubjectArea,
            $hasFinancialAid,
            $financialAidApplicationYear,
            $financialAidBureau
        );
    }

    /**
     * @param TblStudent $tblStudent
     * @param TblStudentMedicalRecord|null $tblStudentMedicalRecord
     * @param TblStudentTransport|null $tblStudentTransport
     * @param TblStudentBilling|null $tblStudentBilling
     * @param TblStudentLocker|null $tblStudentLocker
     * @param TblStudentBaptism|null $tblStudentBaptism
     * @param TblStudentSpecialNeeds|null $tblStudentSpecialNeeds
     * @param TblStudentTechnicalSchool|null $tblStudentTechnicalSchool
     *
     * @return bool
     */
    public function updateStudentField(
        TblStudent $tblStudent,
        TblStudentMedicalRecord $tblStudentMedicalRecord = null,
        TblStudentTransport $tblStudentTransport = null,
        TblStudentBilling $tblStudentBilling = null,
        TblStudentLocker $tblStudentLocker = null,
        TblStudentBaptism $tblStudentBaptism = null,
        TblStudentSpecialNeeds $tblStudentSpecialNeeds = null,
        TblStudentTechnicalSchool $tblStudentTechnicalSchool = null
    ) : bool {
        return (new Data($this->getBinding()))->updateStudentField(
            $tblStudent,
            $tblStudentMedicalRecord,
            $tblStudentTransport,
            $tblStudentBilling,
            $tblStudentLocker,
            $tblStudentBaptism,
            $tblStudentSpecialNeeds,
            $tblStudentTechnicalSchool
        );
    }
}
