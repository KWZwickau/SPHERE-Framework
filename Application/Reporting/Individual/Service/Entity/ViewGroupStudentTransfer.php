<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewGroupStudentTransfer")
 * @Cache(usage="READ_ONLY")
 */
class ViewGroupStudentTransfer extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';
    const TBL_STUDENT_ID = 'TblStudent_Id';
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

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

//        //NameDefinition
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

        $this->setGroupDefinition('Aktive Schule', array(
            self::TBL_COMPANY_PROCESS_NAME,
            self::TBL_COMPANY_PROCESS_EXTENDED_NAME,
            self::TBL_COMPANY_PROCESS_DESCRIPTION,
            self::TBL_STUDENT_TRANSFER_PROCESS_COURSE
        ));

        $this->setGroupDefinition('Einschulung', array(
            self::TBL_COMPANY_ENROLLMENT_NAME,
            self::TBL_COMPANY_ENROLLMENT_EXTENDED_NAME,
            self::TBL_COMPANY_ENROLLMENT_DESCRIPTION,
            self::TBL_STUDENT_TRANSFER_ENROLLMENT_TYPE,
            self::TBL_STUDENT_TRANSFER_ENROLLMENT_COURSE,
            self::TBL_STUDENT_TRANSFER_ENROLLMENT_TRANSFER_DATE
        ));

        $this->setGroupDefinition('Abgebende Schule', array(

            self::TBL_COMPANY_ARRIVE_NAME,
            self::TBL_COMPANY_ARRIVE_EXTENDED_NAME,
            self::TBL_COMPANY_ARRIVE_DESCRIPTION,
            self::TBL_STUDENT_TRANSFER_ARRIVE_TYPE,
            self::TBL_STUDENT_TRANSFER_ARRIVE_COURSE,
            self::TBL_STUDENT_TRANSFER_ARRIVE_TRANSFER_DATE
        ));

        $this->setGroupDefinition('Aufnehmende Schule', array(
            self::TBL_COMPANY_LEAVE_NAME,
            self::TBL_COMPANY_LEAVE_EXTENDED_NAME,
            self::TBL_COMPANY_LEAVE_DESCRIPTION,
            self::TBL_STUDENT_TRANSFER_LEAVE_TYPE,
            self::TBL_STUDENT_TRANSFER_LEAVE_COURSE,
            self::TBL_STUDENT_TRANSFER_LEAVE_TRANSFER_DATE
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
//            case self::SIBLINGS_COUNT:
//                $PropertyCount = $this->calculateFormFieldCount( $PropertyName, $doResetCount );
//                $Field = new NumberField( $PropertyName.'['.$PropertyCount.']',
//                    $Placeholder, $Label, $Icon
//                );
//                break;
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
