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
 * @Table(name="viewGroupProspectTransfer")
 * @Cache(usage="READ_ONLY")
 */
class ViewGroupProspectTransfer extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';

    // Schüler Aufnahme
    const TBL_COMPANY_ARRIVE_NAME = 'TblCompanyArrive_Name';
    const TBL_STATE_COMPANY_ARRIVE_NAME = 'TblStateCompanyArrive_Name';
    const TBL_STUDENT_TRANSFER_ARRIVE_TYPE = 'TblStudentTransferArrive_Type';
    const TBL_STUDENT_TRANSFER_ARRIVE_COURSE = 'TblStudentTransferArrive_Course';
    const TBL_STUDENT_TRANSFER_ARRIVE_TRANSFER_DATE = 'TblStudentTransferArrive_TransferDate';
    const TBL_STUDENT_TRANSFER_ARRIVE_REMARK = 'TblStudentTransferArrive_Remark';

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
    protected $TblCompanyArrive_Name;
    /**
     * @Column(type="string")
     */
    protected $TblStateCompanyArrive_Name;
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
    protected $TblStudentTransferArrive_Remark;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        //NameDefinition
        $this->setNameDefinition(self::TBL_COMPANY_ARRIVE_NAME, 'Aufnahme: Abgebende Schule / Kita');
        $this->setNameDefinition(self::TBL_STATE_COMPANY_ARRIVE_NAME, 'Aufnahme: Staatliche Stammschule');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_ARRIVE_TYPE, 'Aufnahme: Letzte Schulart');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_ARRIVE_COURSE, 'Aufnahme: Letzter Bildungsgang');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_ARRIVE_TRANSFER_DATE, 'Aufnahme: Datum');
        $this->setNameDefinition(self::TBL_STUDENT_TRANSFER_ARRIVE_REMARK, 'Aufnahme: Bemerkungen');

        $this->setGroupDefinition('Schüler – Aufnahme', array(
            self::TBL_COMPANY_ARRIVE_NAME,
            self::TBL_STATE_COMPANY_ARRIVE_NAME,
            self::TBL_STUDENT_TRANSFER_ARRIVE_TYPE,
            self::TBL_STUDENT_TRANSFER_ARRIVE_COURSE,
            self::TBL_STUDENT_TRANSFER_ARRIVE_TRANSFER_DATE,
            self::TBL_STUDENT_TRANSFER_ARRIVE_REMARK
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

//        switch ($PropertyName) {
//            case self::TBL_STUDENT_SCHOOL_ENROLLMENT_TYPE_NAME:
//                $Data = Student::useService()->getPropertyList( new TblStudentSchoolEnrollmentType(), TblStudentSchoolEnrollmentType::ATTR_NAME );
//                $Field = $this->getFormFieldSelectBox( $Data, $PropertyName, $Label, $Icon, $doResetCount );
//                break;
//            default:
//                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
//                break;
//        }

        $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );

        return $Field;
    }

}
