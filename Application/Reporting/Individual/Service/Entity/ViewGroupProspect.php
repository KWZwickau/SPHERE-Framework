<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectReservation;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewGroupProspect")
 * @Cache(usage="READ_ONLY")
 */
class ViewGroupProspect extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';
    const TBL_PROSPECT_APPOINTMENT_RESERVATION_DATE = 'TblProspectAppointment_ReservationDate';
    const TBL_PROSPECT_APPOINTMENT_INTERVIEW_DATE = 'TblProspectAppointment_InterviewDate';
    const TBL_PROSPECT_APPOINTMENT_TRIAL_DATE = 'TblProspectAppointment_TrialDate';
    const TBL_PROSPECT_RESERVATION_RESERVATION_YEAR = 'TblProspectReservation_ReservationYear';
    const TBL_PROSPECT_RESERVATION_RESERVATION_DIVISION = 'TblProspectReservation_ReservationDivision';
    const TBL_TYPE_NAME_A = 'TblType_NameA';
    const TBL_TYPE_NAME_B = 'TblType_NameB';
    const TBL_COMPANY_NAME = 'TblCompany_Name';
    const TBL_COMPANY_NAME_EXTENDED_NAME = 'TblCompany_Name_ExtendedName';
    const TBL_PROSPECT_REMARK = 'TblProspect_Remark';

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
    protected $TblProspectAppointment_ReservationDate;
    /**
     * @Column(type="string")
     */
    protected $TblProspectAppointment_InterviewDate;
    /**
     * @Column(type="string")
     */
    protected $TblProspectAppointment_TrialDate;
    /**
     * @Column(type="string")
     */
    protected $TblProspectReservation_ReservationYear;
    /**
     * @Column(type="string")
     */
    protected $TblProspectReservation_ReservationDivision;
    /**
     * @Column(type="string")
     */
    protected $TblType_NameA;
    /**
     * @Column(type="string")
     */
    protected $TblType_NameB;
    /**
     * @Column(type="string")
     */
    protected $TblCompany_Name;
    /**
     * @Column(type="string")
     */
    protected $TblCompany_Name_ExtendedName;
    /**
     * @Column(type="string")
     */
    protected $TblProspect_Remark;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        //NameDefinition
        $this->setNameDefinition(self::TBL_PROSPECT_APPOINTMENT_RESERVATION_DATE, 'Termin: Eingangsdatum');
        $this->setNameDefinition(self::TBL_PROSPECT_APPOINTMENT_INTERVIEW_DATE, 'Termin: Aufnahmegespräche');
        $this->setNameDefinition(self::TBL_PROSPECT_APPOINTMENT_TRIAL_DATE, 'Termin: Schnuppertag');
        $this->setNameDefinition(self::TBL_PROSPECT_RESERVATION_RESERVATION_YEAR, 'Voranmeldung: Schuljahr');
        $this->setNameDefinition(self::TBL_PROSPECT_RESERVATION_RESERVATION_DIVISION, 'Voranmeldung: Klassenstufe');
        $this->setNameDefinition(self::TBL_TYPE_NAME_A, 'Schulart: Option 1');
        $this->setNameDefinition(self::TBL_TYPE_NAME_B, 'Schulart: Option 2');
        $this->setNameDefinition(self::TBL_COMPANY_NAME, 'Voranmeldung: Schule');
        $this->setNameDefinition(self::TBL_COMPANY_NAME_EXTENDED_NAME, 'Voranmeldung: Schule und Zusatz');
        $this->setNameDefinition(self::TBL_PROSPECT_REMARK, 'Interessent: Bemerkung');

        //GroupDefinition
        $this->setGroupDefinition('Termine', array(
            self::TBL_PROSPECT_APPOINTMENT_RESERVATION_DATE,
            self::TBL_PROSPECT_APPOINTMENT_INTERVIEW_DATE,
            self::TBL_PROSPECT_APPOINTMENT_TRIAL_DATE,
        ));
        $this->setGroupDefinition('Voranmeldung', array(
            self::TBL_PROSPECT_RESERVATION_RESERVATION_YEAR,
            self::TBL_PROSPECT_RESERVATION_RESERVATION_DIVISION,
            self::TBL_TYPE_NAME_A,
            self::TBL_TYPE_NAME_B,
            self::TBL_COMPANY_NAME,
            self::TBL_COMPANY_NAME_EXTENDED_NAME,
        ));
        $this->setGroupDefinition('Sonstiges', array(
            self::TBL_PROSPECT_REMARK,
        ));

        // Flag um Filter zu deaktivieren (nur Anzeige von Informationen)
//        $this->setDisableDefinition(self::TBL_SALUTATION_SALUTATION_S1);
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
            case self::TBL_TYPE_NAME_A:
            case self::TBL_TYPE_NAME_B:
                $Data = Type::useService()->getPropertyList(new TblType(), TblType::ATTR_NAME);
                $Field = $this->getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount);
                break;
//            case self::TBL_TYPE_NAME_B:
//                $Data = Type::useService()->getPropertyList(new TblType(), TblType::ATTR_NAME);
//                $Field = $this->getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount);
//                break;
            case self::TBL_PROSPECT_RESERVATION_RESERVATION_YEAR:
                $Data = Prospect::useService()->getPropertyList(new TblProspectReservation(), TblProspectReservation::ATTR_RESERVATION_YEAR);
                $Field = $this->getFormFieldAutoCompleter($Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount);
                break;
            case self::TBL_COMPANY_NAME:
                $Data = array();
                if(($tblSchoolList = School::useService()->getSchoolAll())){
                    foreach($tblSchoolList as $tblSchool){
                        if(($tblCompany = $tblSchool->getServiceTblCompany())){
                            $Data[] = $tblCompany->getName();
                        }
                    }
                }
                if(!empty($Data)){
                    $Field = $this->getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount);
                } else {
                    $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                }
                break;
            case self::TBL_COMPANY_NAME_EXTENDED_NAME:
                $Data = array();
                if(($tblSchoolList = School::useService()->getSchoolAll())){
                    foreach($tblSchoolList as $tblSchool){
                        if(($tblCompany = $tblSchool->getServiceTblCompany())){
                            $Data[] = $tblCompany->getDisplayName();
                        }
                    }
                }
                if(!empty($Data)){
                    $Field = $this->getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount);
                } else {
                    $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                }
                break;
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
