<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewStudent")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudent extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';
    const TBL_STUDENT_ID = 'TblStudent_Id';
    const TBL_STUDENT_IDENTIFIER = 'TblStudent_Identifier';
    const TBL_STUDENT_SCHOOL_ATTENDANCE_START_DATE = 'TblStudent_SchoolAttendanceStartDate';
    const SIBLINGS_COUNT = 'Sibling_Count';
    // Krankenakte
    const TBL_STUDENT_MEDICAL_RECORD_DISEASE = 'TblStudentMedicalRecord_Disease';
    const TBL_STUDENT_MEDICAL_RECORD_MEDICATION = 'TblStudentMedicalRecord_Medication';
    const TBL_STUDENT_MEDICAL_RECORD_INSURANCE_STATE = 'TblStudentMedicalRecord_InsuranceState';
    const TBL_STUDENT_MEDICAL_RECORD_INSURANCE = 'TblStudentMedicalRecord_Insurance';
    // Taufe
    const TBL_STUDENT_BAPTISM_LOCATION = 'TblStudentBaptism_Location';
    // Schulbeförderung
    const TBL_STUDENT_TRANSPORT_ROUTE = 'TblStudentTransport_Route';
    const TBL_STUDENT_TRANSPORT_STATION_ENTRANCE = 'TblStudentTransport_StationEntrance';
    const TBL_STUDENT_TRANSPORT_STATION_EXIT = 'TblStudentTransport_StationExit';
    const TBL_STUDENT_TRANSPORT_REMARK = 'TblStudentTransport_Remark';
    // Unterrichtsbefreiung
    const TBL_STUDENT_LIBERATION_TYPE_NAME = 'TblStudentLiberationType_Name';
    const TBL_STUDENT_LIBERATION_TYPE_DESCRIPTION = 'TblStudentLiberationType_Description';
    const TBL_STUDENT_LIBERATION_CATEGORY_NAME = 'TblStudentLiberationCategory_Name';
    const TBL_STUDENT_LIBERATION_CATEGORY_DESCRIPTION = 'TblStudentLiberationCategory_Description';
    // Schließfach
    const TBL_STUDENT_LOCKER_LOCKER_NUMBER = 'TblStudentLocker_LockerNumber';
    const TBL_STUDENT_LOCKER_LOCKER_LOCATION = 'TblStudentLocker_LockerLocation';
    const TBL_STUDENT_LOCKER_KEY_NUMBER = 'TblStudentLocker_KeyNumber';
    // Aktuelle Schule
    const TBL_COMPANY_PROCESS_NAME = 'TblCompanyProcess_Name';
    const TBL_COMPANY_PROCESS_EXTENDED_NAME = 'TblCompanyProcess_ExtendedName';
    const TBL_COMPANY_PROCESS_DESCRIPTION = 'TblCompanyProcess_Description';
    const TBL_STUDENT_TRANSFER_PROCESS_COURSE = 'TblStudentTransferProcess_Course';
    // Ersteinschulung
    const TBL_COMPANY_ENROLLMENT_NAME = 'TblCompanyEnrollment_Name';
    const TBL_COMPANY_ENROLLMENT_EXTENDED_NAME = 'TblCompanyEnrollment_ExtendedName';
    const TBL_COMPANY_ENROLLMENT_DESCRIPTION = 'TblCompanyEnrollment_Description';
    const TBL_STUDENT_TRANSFER_ENROLLMENT_TYPE = 'TblStudentTransferEnrollment_Type';
    const TBL_STUDENT_TRANSFER_ENROLLMENT_COURSE = 'TblStudentTransferEnrollment_Course';
    const TBL_STUDENT_TRANSFER_ENROLLMENT_TRANSFER_DATE = 'TblStudentTransferEnrollment_TransferDate';
    // Schüler Aufnahme
    const TBL_COMPANY_ARRIVE_NAME = 'TblCompanyArrive_Name';
    const TBL_COMPANY_ARRIVE_EXTENDED_NAME = 'TblCompanyArrive_ExtendedName';
    const TBL_COMPANY_ARRIVE_DESCRIPTION = 'TblCompanyArrive_Description';
    const TBL_STUDENT_TRANSFER_ARRIVE_TYPE = 'TblStudentTransferArrive_Type';
    const TBL_STUDENT_TRANSFER_ARRIVE_COURSE = 'TblStudentTransferArrive_Course';
    const TBL_STUDENT_TRANSFER_ARRIVE_TRANSFER_DATE = 'TblStudentTransferArrive_TransferDate';
    // Schüler Abgabe
    const TBL_COMPANY_LEAVE_NAME = 'TblCompanyLeave_Name';
    const TBL_COMPANY_LEAVE_EXTENDED_NAME = 'TblCompanyLeave_ExtendedName';
    const TBL_COMPANY_LEAVE_DESCRIPTION = 'TblCompanyLeave_Description';
    const TBL_STUDENT_TRANSFER_LEAVE_TYPE = 'TblStudentTransferLeave_Type';
    const TBL_STUDENT_TRANSFER_LEAVE_COURSE = 'TblStudentTransferLeave_Course';
    const TBL_STUDENT_TRANSFER_LEAVE_TRANSFER_DATE = 'TblStudentTransferLeave_TransferDate';
    // Integration
    const TBL_STUDENT_FOCUS_TYPE_NAME_LIST = 'TblStudentFocusType_NameList';
    const TBL_STUDENT_DISORDER_TYPE_NAME_LIST = 'TblStudentDisorderType_NameList';
    const TBL_STUDENT_INTEGRATION_COACHING_REQUEST_DATE = 'TblStudentIntegration_CoachingRequestDate';
    const TBL_STUDENT_INTEGRATION_COACHING_COUNSEL_DATE = 'TblStudentIntegration_CoachingCounselDate';
    const TBL_STUDENT_INTEGRATION_COACHING_DECISION_DATE = 'TblStudentIntegration_CoachingDecisionDate';
    const TBL_STUDENT_INTEGRATION_COACHING_REQUIRED = 'TblStudentIntegration_CoachingRequired';
    const TBL_STUDENT_INTEGRATION_COACHING_TIME = 'TblStudentIntegration_CoachingTime';
    const TBL_STUDENT_INTEGRATION_COACHING_REMARK = 'TblStudentIntegration_CoachingRemark';
    const TBL_SALUTATION_SALUTATION_COACH = 'TblSalutation_Salutation_Coach';
    const TBL_PERSON_FIRST_NAME_COACH = 'TblPerson_FirstName_Coach';
    const TBL_PERSON_LAST_NAME_COACH = 'TblPerson_LastName_Coach';

    /**
     * @return array
     */
    static function getConstants()
    {
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }

    /**
     * @Column(type="string")
     */
    protected $TblPerson_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_Identifier;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_SchoolAttendanceStartDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentBaptism_Location;
    /**
     * @Column(type="string")
     */
    protected $TblStudentFocusType_NameList;
    /**
     * @Column(type="string")
     */
    protected $TblStudentDisorderType_NameList;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_CoachingRequestDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_CoachingCounselDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_CoachingDecisionDate;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_CoachingRequired;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_CoachingTime;
    /**
     * @Column(type="string")
     */
    protected $TblStudentIntegration_CoachingRemark;
    /**
     * @Column(type="string")
     */
    protected $TblSalutation_Salutation_Coach;
    /**
     * @Column(type="string")
     */
    protected $TblSalutation_TblPerson_FirstName_Coach;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_LastName_Coach;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLiberationType_Name;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLiberationType_Description;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLiberationCategory_Name;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLiberationCategory_Description;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLocker_LockerNumber;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLocker_LockerLocation;
    /**
     * @Column(type="string")
     */
    protected $TblStudentLocker_KeyNumber;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_Disease;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_Medication;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_InsuranceState;
    /**
     * @Column(type="string")
     */
    protected $TblStudentMedicalRecord_Insurance;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransport_Route;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransport_StationEntrance;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransport_StationExit;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransport_Remark;
    /**
     * @Column(type="string")
     */
    protected $TblCompanyEnrollment_Name;
    /**
     * @Column(type="string")
     */
    protected $TblCompanyEnrollment_ExtendedName;
    /**
     * @Column(type="string")
     */
    protected $TblCompanyEnrollment_Description;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferEnrollment_Type;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferEnrollment_Course;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferEnrollment_TransferDate;
    /**
     * @Column(type="string")
     */
    protected $TblCompanyArrive_Name;
    /**
     * @Column(type="string")
     */
    protected $TblCompanyArrive_ExtendedName;
    /**
     * @Column(type="string")
     */
    protected $TblCompanyArrive_Description;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferArrive_Type;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferArrive_Course;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferArrive_TransferDate;
    /**
     * @Column(type="string")
     */
    protected $TblCompanyLeave_Name;
    /**
     * @Column(type="string")
     */
    protected $TblCompanyLeave_ExtendedName;
    /**
     * @Column(type="string")
     */
    protected $TblCompanyLeave_Description;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferLeave_Type;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferLeave_Course;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferLeave_TransferDate;
    /**
     * @Column(type="string")
     */
    protected $TblCompanyProcess_Name;
    /**
     * @Column(type="string")
     */
    protected $TblCompanyProcess_ExtendedName;
    /**
     * @Column(type="string")
     */
    protected $TblCompanyProcess_Description;
    /**
     * @Column(type="string")
     */
    protected $TblStudentTransferProcess_Course;
    /**
     * @Column(type="string")
     */
    protected $Sibling_Count;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

//        //NameDefinition
        $this->setNameDefinition(self::TBL_STUDENT_IDENTIFIER, 'Grunddaten: Schülernummer');
        $this->setNameDefinition(self::TBL_STUDENT_SCHOOL_ATTENDANCE_START_DATE, 'Grunddaten: Schulpflicht beginn');
//        $this->setNameDefinition(self::TBL_STUDENT_BAPTISM_LOCATION, 'Schüler: Ort der Taufe');
        $this->setNameDefinition(self::TBL_STUDENT_FOCUS_TYPE_NAME_LIST, 'Integration: Schwerpunkte');
        $this->setNameDefinition(self::TBL_STUDENT_DISORDER_TYPE_NAME_LIST, 'Integration: Teilleistungsstörungen');

        $this->setNameDefinition(self::TBL_STUDENT_INTEGRATION_COACHING_REQUEST_DATE, 'Integration: Datum der Beratung');
        $this->setNameDefinition(self::TBL_STUDENT_INTEGRATION_COACHING_COUNSEL_DATE, 'Integration: Datum der Beantragung');
        $this->setNameDefinition(self::TBL_STUDENT_INTEGRATION_COACHING_DECISION_DATE, 'Integration: SBA Bescheid Datum');

        $this->setNameDefinition(self::TBL_STUDENT_INTEGRATION_COACHING_REQUIRED, 'Integration: Förderbedarf');
        $this->setNameDefinition(self::TBL_STUDENT_INTEGRATION_COACHING_TIME, 'Integration: Stundenbedarf pro Woche');
        $this->setNameDefinition(self::TBL_STUDENT_INTEGRATION_COACHING_REMARK, 'Integration: Bemerkung');
        $this->setNameDefinition(self::TBL_SALUTATION_SALUTATION_COACH, 'Integration: Anrede Schulbegleitung');
        $this->setNameDefinition(self::TBL_PERSON_FIRST_NAME_COACH, 'Integration: Vorname Schulbegleitung');
        $this->setNameDefinition(self::TBL_PERSON_LAST_NAME_COACH, 'Integration: NachName Schulbegleitung');

        $this->setNameDefinition(self::TBL_STUDENT_LIBERATION_TYPE_NAME, 'Allgemeines: Unterrichtsbefreiung');
        // TBL_STUDENT_LIBERATION_TYPE_DESCRIPTION
        $this->setNameDefinition(self::TBL_STUDENT_LIBERATION_CATEGORY_NAME, 'Allgemeines: Unterrichtskategorie');
        // TBL_STUDENT_LIBERATION_CATEGORY_DESCRIPTION

        $this->setNameDefinition(self::TBL_STUDENT_LOCKER_LOCKER_NUMBER, 'Allgemeines: Schließfachnummer');
        $this->setNameDefinition(self::TBL_STUDENT_LOCKER_LOCKER_LOCATION, 'Allgemeines: Schließfach Standort');
        $this->setNameDefinition(self::TBL_STUDENT_LOCKER_KEY_NUMBER, 'Allgemeines: Schließfach Schlüssel Nummer');

        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_DISEASE, 'Allgemeines: Krankheiten / Allergien');
        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_MEDICATION, 'Allgemeines: Medikamente');
        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_INSURANCE_STATE, 'Allgemeines: Versicherungsstatus');
        $this->setNameDefinition(self::TBL_STUDENT_MEDICAL_RECORD_INSURANCE, 'Allgemeines: Krankenkasse');

        $this->setNameDefinition(self::TBL_STUDENT_TRANSPORT_ROUTE, 'Allgemeines: Buslinie');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSPORT_STATION_ENTRANCE, 'Allgemeines: Einstiegshaltestelle');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSPORT_STATION_EXIT, 'Allgemeines: Ausstiegshaltestelle');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSPORT_REMARK, 'Allgemeines: Bemerkung');

        $this->setNameDefinition(self::TBL_COMPANY_PROCESS_NAME, 'Aktuell: Schule');
        $this->setNameDefinition(self::TBL_COMPANY_PROCESS_EXTENDED_NAME, 'Aktuell: Schule Zusatz');
        $this->setNameDefinition(self::TBL_COMPANY_PROCESS_DESCRIPTION, 'Aktuell: Schule Bemerkung');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_PROCESS_COURSE, 'Aktuell: Bildungsgang');

        $this->setNameDefinition(self::TBL_COMPANY_ENROLLMENT_NAME, 'Einschulung: Schule');
        $this->setNameDefinition(self::TBL_COMPANY_ENROLLMENT_EXTENDED_NAME, 'Einschulung: Schule Zusatz');
        $this->setNameDefinition(self::TBL_COMPANY_ENROLLMENT_DESCRIPTION, 'Einschulung: Schule Bemerkung');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_ENROLLMENT_TYPE, 'Einschulung: Schulart');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_ENROLLMENT_COURSE, 'Einschulung: Bildungsgang');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_ENROLLMENT_TRANSFER_DATE, 'Einschulung: Datum');

        $this->setNameDefinition(self::TBL_COMPANY_ARRIVE_NAME, 'Aufnahme: Schule');
        $this->setNameDefinition(self::TBL_COMPANY_ARRIVE_EXTENDED_NAME, 'Aufnahme: Schule Zusatz');
        $this->setNameDefinition(self::TBL_COMPANY_ARRIVE_DESCRIPTION, 'Aufnahme: Schule Bemerkung');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_ARRIVE_TYPE, 'Aufnahme: Schulart');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_ARRIVE_COURSE, 'Aufnahme: Bildungsgang');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_ARRIVE_TRANSFER_DATE, 'Aufnahme: Datum');

        $this->setNameDefinition(self::TBL_COMPANY_LEAVE_NAME, 'Abgabe: Schule');
        $this->setNameDefinition(self::TBL_COMPANY_LEAVE_EXTENDED_NAME, 'Abgabe: Schule Zusatz');
        $this->setNameDefinition(self::TBL_COMPANY_LEAVE_DESCRIPTION, 'Abgabe: Schule Bemerkung');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_LEAVE_TYPE, 'Abgabe: Schulart');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_LEAVE_COURSE, 'Abgabe: Bildungsgang');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_LEAVE_TRANSFER_DATE, 'Abgabe: Datum');

        $this->setNameDefinition(self::SIBLINGS_COUNT, 'Schüler: Anzahl Geschwister');

//        //GroupDefinition
        $this->setGroupDefinition('Grunddaten', array(
            self::TBL_STUDENT_IDENTIFIER,
            self::TBL_STUDENT_SCHOOL_ATTENDANCE_START_DATE,
            self::SIBLINGS_COUNT
        ));

        $this->setGroupDefinition('Allgemeines', array(
            self::TBL_STUDENT_LIBERATION_TYPE_NAME,
            self::TBL_STUDENT_LIBERATION_CATEGORY_NAME,
            self::TBL_STUDENT_LOCKER_LOCKER_NUMBER,
            self::TBL_STUDENT_LOCKER_LOCKER_LOCATION,
            self::TBL_STUDENT_LOCKER_KEY_NUMBER,
            self::TBL_STUDENT_MEDICAL_RECORD_DISEASE,
            self::TBL_STUDENT_MEDICAL_RECORD_MEDICATION,
            self::TBL_STUDENT_MEDICAL_RECORD_INSURANCE_STATE,
            self::TBL_STUDENT_MEDICAL_RECORD_INSURANCE,
            self::TBL_STUDENT_TRANSPORT_ROUTE,
            self::TBL_STUDENT_TRANSPORT_STATION_ENTRANCE,
            self::TBL_STUDENT_TRANSPORT_STATION_EXIT,
            self::TBL_STUDENT_TRANSPORT_REMARK
        ));

        $this->setGroupDefinition('Transfer', array(
            self::TBL_COMPANY_ENROLLMENT_NAME,
            self::TBL_COMPANY_ENROLLMENT_EXTENDED_NAME,
            self::TBL_COMPANY_ENROLLMENT_DESCRIPTION,
            self::TBL_STUDENT_TRANSFER_ENROLLMENT_TYPE,
            self::TBL_STUDENT_TRANSFER_ENROLLMENT_COURSE,
            self::TBL_STUDENT_TRANSFER_ENROLLMENT_TRANSFER_DATE,
            self::TBL_COMPANY_ARRIVE_NAME,
            self::TBL_COMPANY_ARRIVE_EXTENDED_NAME,
            self::TBL_COMPANY_ARRIVE_DESCRIPTION,
            self::TBL_STUDENT_TRANSFER_ARRIVE_TYPE,
            self::TBL_STUDENT_TRANSFER_ARRIVE_COURSE,
            self::TBL_STUDENT_TRANSFER_ARRIVE_TRANSFER_DATE,
            self::TBL_COMPANY_LEAVE_NAME,
            self::TBL_COMPANY_LEAVE_EXTENDED_NAME,
            self::TBL_COMPANY_LEAVE_DESCRIPTION,
            self::TBL_STUDENT_TRANSFER_LEAVE_TYPE,
            self::TBL_STUDENT_TRANSFER_LEAVE_COURSE,
            self::TBL_STUDENT_TRANSFER_LEAVE_TRANSFER_DATE,
            self::TBL_COMPANY_PROCESS_NAME,
            self::TBL_COMPANY_PROCESS_EXTENDED_NAME,
            self::TBL_COMPANY_PROCESS_DESCRIPTION,
            self::TBL_STUDENT_TRANSFER_PROCESS_COURSE
        ));

        $this->setGroupDefinition('Integration', array(
            self::TBL_STUDENT_FOCUS_TYPE_NAME_LIST,
            self::TBL_STUDENT_DISORDER_TYPE_NAME_LIST,
            self::TBL_STUDENT_INTEGRATION_COACHING_REQUEST_DATE,
            self::TBL_STUDENT_INTEGRATION_COACHING_COUNSEL_DATE,
            self::TBL_STUDENT_INTEGRATION_COACHING_DECISION_DATE,
            self::TBL_STUDENT_INTEGRATION_COACHING_REQUIRED,
            self::TBL_STUDENT_INTEGRATION_COACHING_TIME,
            self::TBL_STUDENT_INTEGRATION_COACHING_REMARK,
            self::TBL_SALUTATION_SALUTATION_COACH,
            self::TBL_PERSON_FIRST_NAME_COACH,
            self::TBL_PERSON_LAST_NAME_COACH
        ));
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {
        // TODO: Implement loadViewGraph() method.
    }

    /**
     * @return void|AbstractService
     */
    public function getViewService()
    {
        // TODO: Implement getViewService() method.
    }

    /**
     * Define Property Field-Type and additional Data
     *
     * @param string $PropertyName __CLASS__::{CONSTANT_}
     * @param null|string $Placeholder
     * @param null|string $Label
     * @param IIconInterface|null $Icon
     * @param bool $doResetCount Reset ALL FieldName calculations e.g. FieldName[23] -> FieldName[1]
     * @return AbstractField
     */
    public function getFormField( $PropertyName, $Placeholder = null, $Label = null, IIconInterface $Icon = null, $doResetCount = false )
    {

        switch ($PropertyName) {
            case self::SIBLINGS_COUNT:
                $PropertyCount = $this->calculateFormFieldCount( $PropertyName, $doResetCount );
                $Field = new NumberField( $PropertyName.'['.$PropertyCount.']',
                    $Placeholder, $Label, $Icon
                );
                break;
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
