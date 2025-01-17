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
 * @Table(name="viewPersonSearch")
 * @Cache(usage="READ_ONLY")
 */
class ViewPersonSearch extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';
    const TBL_PERSON_LAST_FIRST_NAME = 'TblPerson_LastFirstName';
//    const TBL_GROUP_ID = 'TblGroup_Id';
    const TBL_STUDENT_IDENTIFIER = 'TblStudent_Identifier';
    const TBL_PROSPECT_RESERVATION_RESERVATION_YEAR = 'TblProspectReservation_ReservationYear';
    const TBL_PROSPECT_RESERVATION_RESERVATION_DIVISION = 'TblProspectReservation_ReservationDivision';
    const TBL_COMPANY_NAME = 'TblCompany_Name';
    const TBL_COMPANY_NAME_EXTENDED_NAME = 'TblCompany_Name_ExtendedName';
    const TBL_TYPE_NAMEA = 'TblType_NameA';
    const TBL_TYPE_NAMEB = 'TblType_NameB';
    const TBL_TYPE_NAME = 'TblType_Name';
    const TBL_TYPE_DESCRIPTION = 'TblType_Description';
    const TBL_ADDRESS_STREET_NAME = 'TblAddress_StreetName';
    const TBL_ADDRESS_STREET_NUMBER = 'TblAddress_StreetNumber';
    const TBL_CITY_CODE = 'TblCity_Code';
    const TBL_CITY_NAME = 'TblCity_Name';
    const TBL_CITY_DISTRICT = 'TblCity_District';
    const TBL_COMMON_REMARK = 'TblCommon_Remark';

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
    protected $TblPerson_LastFirstName;
//    /**
//     * @Column(type="string")
//     */
//    protected $TblGroup_Id;
    /**
     * @Column(type="string")
     */
    protected $TblStudent_Identifier;
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
    protected $TblCompany_Name;
    /**
     * @Column(type="string")
     */
    protected $TblCompany_Name_ExtendedName;
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
    protected $TblType_Name;
    /**
     * @Column(type="string")
     */
    protected $TblType_Description;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetName;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetNumber;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Code;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Name;
    /**
     * @Column(type="string")
     */
    protected $TblCity_District;
    /**
     * @Column(type="string")
     */
    protected $TblCommon_Remark;


    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        //NameDefinition
        // not necessary
//        $this->setNameDefinition(self::TBL_PERSON_LAST_FIRST_NAME, 'Person: Nachname Vorname');

        //GroupDefinition
        $this->setGroupDefinition('Grunddaten', array(
            self::TBL_PERSON_LAST_FIRST_NAME,
//            self::TBL_PERSON_BIRTH_NAME,
        ));

        // Flag um Filter zu deaktivieren (nur Anzeige von Informationen)
//        $this->setDisableDefinition(self::TBL_PERSON_LAST_FIRST_NAME);
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
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }


    // only necessary if need in twig (SelectBox etc.)
//    /** @return string */
//    public function getTblPerson_Id(){return $this->TblPerson_Id;}
//    public function getTblCommonGender_Name(){return $this->TblCommonGender_Name;}
//    public function getTblSalutation_Salutation(){return $this->TblSalutation_Salutation;}
//    public function getTblPerson_Title(){return $this->TblPerson_Title;}
//    public function TblPerson_FirstName(){return $this->TblPerson_FirstName;}
//    public function TblPerson_SecondName(){return $this->TblPerson_SecondName;}
//    public function TblPerson_CallName(){return $this->TblPerson_CallName;}
//    public function getTblPerson_LastName(){return $this->TblPerson_LastName;}
//    public function getTblPerson_BirthName(){return $this->TblPerson_BirthName;}
//    public function getTblPerson_FirstLastName(){return $this->TblPerson_FirstLastName;}
//    public function getTblPerson_LastFirstName(){return $this->TblPerson_LastFirstName;}
//    public function getTblGroup_GroupList(){return $this->TblGroup_GroupList;}
//    public function getTblCommonInformation_Denomination(){return $this->TblCommonInformation_Denomination;}
//    public function getTblCommonInformation_Nationality(){return $this->TblCommonInformation_Nationality;}
//    public function getTblCommonInformation_IsAssistance(){return $this->TblCommonInformation_IsAssistance;}
//    public function getTblCommonInformation_AssistanceActivity(){return $this->TblCommonInformation_AssistanceActivity;}
//    public function getTblCommon_Remark(){return $this->TblCommon_Remark;}
//    public function getTblCommonBirthDates_Birthday(){return $this->TblCommonBirthDates_Birthday;}
//    public function getTblCommonBirthDates_Birthplace(){return $this->TblCommonBirthDates_Birthplace;}
}
