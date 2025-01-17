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
 * @Table(name="viewStudentAuthorized")
 * @Cache(usage="READ_ONLY")
 */
class ViewStudentAuthorized extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';

    // S1
    const TBL_PERSON_BEV_ID = 'TblPerson_Authorized_Id';
    const TBL_SALUTATION_SALUTATION_BEV = 'TblSalutation_Salutation_Bev';
    const TBL_PERSON_TITLE_BEV = 'TblPerson_Title_Bev';
    const TBL_PERSON_FIRST_NAME_BEV = 'TblPerson_FirstName_Bev';
    const TBL_PERSON_SECOND_NAME_BEV = 'TblPerson_SecondName_Bev';
    const TBL_PERSON_CALL_NAME_BEV = 'TblPerson_CallName_Bev';
    const TBL_PERSON_LAST_NAME_BEV = 'TblPerson_LastName_Bev';
    const TBL_PERSON_BIRTH_NAME_BEV = 'TblPerson_BirthName_Bev';
    const TBL_PERSON_BIRTH_DAY_BEV = 'TblPerson_Birthday_Bev';
    const TBL_ADDRESS_STREET_NAME_BEV = 'TblAddress_StreetName_Bev';
    const TBL_ADDRESS_STREET_NUMBER_BEV = 'TblAddress_StreetNumber_Bev';
    const TBL_CITY_CODE_BEV = 'TblCity_Code_Bev';
    const TBL_CITY_NAME_BEV = 'TblCity_Name_Bev';
    const TBL_CITY_DISTRICT_BEV = 'TblCity_District_Bev';
    const TBL_ADDRESS_COUNTY_BEV = 'TblAddress_County_Bev';
    const TBL_ADDRESS_STATE_BEV = 'TblState_Name_Bev';
    const TBL_ADDRESS_NATION_BEV = 'TblAddress_Nation_Bev';
    const TBL_PHONE_NUMBER_BEV = 'TblPhone_Number_Bev';
    const TBL_PHONE_NUMBER_PF_BEV = 'TblPhone_Number_PF_Bev';
    const TBL_PHONE_NUMBER_PM_BEV = 'TblPhone_Number_PM_Bev';
    const TBL_PHONE_NUMBER_GF_BEV = 'TblPhone_Number_GF_Bev';
    const TBL_PHONE_NUMBER_GM_BEV = 'TblPhone_Number_GM_Bev';
    const TBL_PHONE_NUMBER_NF_BEV = 'TblPhone_Number_NF_Bev';
    const TBL_PHONE_NUMBER_NM_BEV = 'TblPhone_Number_NM_Bev';
    const TBL_PHONE_NUMBER_FP_BEV = 'TblPhone_Number_FP_Bev';
    const TBL_PHONE_NUMBER_FG_BEV = 'TblPhone_Number_FG_Bev';
    const TBL_MAIL_ADDRESS_BEV = 'TblMail_Address_Bev';
    const TBL_MAIL_ADDRESS_PRIVATE_BEV = 'TblMail_AddressPrivate_Bev';
    const TBL_MAIL_ADDRESS_COMPANY_BEV = 'TblMail_AddressCompany_Bev';
    const TBL_TO_PERSON_IS_SINGLE_PARENT_BEV = 'TblToPerson_IsSingleParent_Bev';

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
    protected $TblPerson_Authorized_Id;
    /**
     * @Column(type="string")
     */
    protected $TblSalutation_Salutation_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Title_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_FirstName_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_SecondName_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_CallName_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_LastName_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_BirthName_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblPerson_Birthday_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetName_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_StreetNumber_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Code_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblCity_Name_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblCity_District_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_County_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblState_Name_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_Nation_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number_PF_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number_PM_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number_GF_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number_GM_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number_NF_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number_NM_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number_FP_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number_FG_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblMail_Address_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblMail_AddressPrivate_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblMail_AddressCompany_Bev;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_IsSingleParent_Bev;
    
    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        // S1
        $this->setNameDefinition(self::TBL_PERSON_BEV_ID, 'Bev: Id');
        $this->setNameDefinition(self::TBL_SALUTATION_SALUTATION_BEV, 'Bev: Anrede');
        $this->setNameDefinition(self::TBL_PERSON_TITLE_BEV, 'Bev: Titel');
        $this->setNameDefinition(self::TBL_PERSON_FIRST_NAME_BEV, 'Bev: Vorname');
        $this->setNameDefinition(self::TBL_PERSON_SECOND_NAME_BEV, 'Bev: Zweiter Vorname');
        $this->setNameDefinition(self::TBL_PERSON_CALL_NAME_BEV, 'Bev: Rufname');
        $this->setNameDefinition(self::TBL_PERSON_LAST_NAME_BEV, 'Bev: Nachname');
        $this->setNameDefinition(self::TBL_PERSON_BIRTH_NAME_BEV, 'Bev: Geburtsname');
        $this->setNameDefinition(self::TBL_PERSON_BIRTH_DAY_BEV, 'Bev: Geburtsdatum');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NAME_BEV, 'Bev: Straße');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NUMBER_BEV, 'Bev: Hausnummer');
        $this->setNameDefinition(self::TBL_CITY_CODE_BEV, 'Bev: PLZ');
        $this->setNameDefinition(self::TBL_CITY_NAME_BEV, 'Bev: Ort');
        $this->setNameDefinition(self::TBL_CITY_DISTRICT_BEV, 'Bev: Ortsteil');
        $this->setNameDefinition(self::TBL_ADDRESS_COUNTY_BEV, 'Bev: Landkreis');
        $this->setNameDefinition(self::TBL_ADDRESS_STATE_BEV, 'Bev: Bundesland');
        $this->setNameDefinition(self::TBL_ADDRESS_NATION_BEV, 'Bev: Land');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER_BEV, 'Bev: Telefon');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER_PF_BEV, 'Bev: Telefon Privat Festnetz');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER_PM_BEV, 'Bev: Telefon Privat Mobil');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER_GF_BEV, 'Bev: Telefon Geschäftlich Festnetz');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER_GM_BEV, 'Bev: Telefon Geschäftlich Mobil');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER_NF_BEV, 'Bev: Telefon Notfall Festnetz');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER_NM_BEV, 'Bev: Telefon Notfall Mobil');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER_FP_BEV, 'Bev: Telefon Fax Privat');
        $this->setNameDefinition(self::TBL_PHONE_NUMBER_FG_BEV, 'Bev: Telefon Fax Geschäftlich');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_BEV, 'Bev: E-Mail');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_PRIVATE_BEV, 'Bev: E-Mail Privat');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS_COMPANY_BEV, 'Bev: E-Mail Geschäftlich');
        $this->setNameDefinition(self::TBL_TO_PERSON_IS_SINGLE_PARENT_BEV, 'Bev: Alleinerziehend');

        //GroupDefinition

        $this->setGroupDefinition('Bevollmächtigter (Zusatzinfo)', array(
            self::TBL_PERSON_BEV_ID,
            self::TBL_SALUTATION_SALUTATION_BEV,
            self::TBL_PERSON_TITLE_BEV,
            self::TBL_PERSON_FIRST_NAME_BEV,
            self::TBL_PERSON_SECOND_NAME_BEV,
            self::TBL_PERSON_CALL_NAME_BEV,
            self::TBL_PERSON_LAST_NAME_BEV,
            self::TBL_PERSON_BIRTH_NAME_BEV,
            self::TBL_PERSON_BIRTH_DAY_BEV,
            self::TBL_ADDRESS_STREET_NAME_BEV,
            self::TBL_ADDRESS_STREET_NUMBER_BEV,
            self::TBL_CITY_CODE_BEV,
            self::TBL_CITY_NAME_BEV,
            self::TBL_CITY_DISTRICT_BEV,
            self::TBL_ADDRESS_COUNTY_BEV,
            self::TBL_ADDRESS_STATE_BEV,
            self::TBL_ADDRESS_NATION_BEV,
            self::TBL_PHONE_NUMBER_BEV,
            self::TBL_PHONE_NUMBER_PF_BEV,
            self::TBL_PHONE_NUMBER_PM_BEV,
            self::TBL_PHONE_NUMBER_GF_BEV,
            self::TBL_PHONE_NUMBER_GM_BEV,
            self::TBL_PHONE_NUMBER_NF_BEV,
            self::TBL_PHONE_NUMBER_NM_BEV,
            self::TBL_PHONE_NUMBER_FP_BEV,
            self::TBL_PHONE_NUMBER_FG_BEV,
            self::TBL_MAIL_ADDRESS_BEV,
            self::TBL_MAIL_ADDRESS_PRIVATE_BEV,
            self::TBL_MAIL_ADDRESS_COMPANY_BEV,
            self::TBL_TO_PERSON_IS_SINGLE_PARENT_BEV
        ));

        // Flag um Filter zu deaktivieren (nur Anzeige von Informationen)
        $this->setDisableDefinition(self::TBL_PERSON_BEV_ID);
        $this->setDisableDefinition(self::TBL_SALUTATION_SALUTATION_BEV);
        $this->setDisableDefinition(self::TBL_PERSON_TITLE_BEV);
        $this->setDisableDefinition(self::TBL_PERSON_FIRST_NAME_BEV);
        $this->setDisableDefinition(self::TBL_PERSON_SECOND_NAME_BEV);
        $this->setDisableDefinition(self::TBL_PERSON_CALL_NAME_BEV);
        $this->setDisableDefinition(self::TBL_PERSON_LAST_NAME_BEV);
        $this->setDisableDefinition(self::TBL_PERSON_BIRTH_NAME_BEV);
        $this->setDisableDefinition(self::TBL_PERSON_BIRTH_DAY_BEV);
        $this->setDisableDefinition(self::TBL_ADDRESS_STREET_NAME_BEV);
        $this->setDisableDefinition(self::TBL_ADDRESS_STREET_NUMBER_BEV);
        $this->setDisableDefinition(self::TBL_CITY_CODE_BEV);
        $this->setDisableDefinition(self::TBL_CITY_NAME_BEV);
        $this->setDisableDefinition(self::TBL_CITY_DISTRICT_BEV);
        $this->setDisableDefinition(self::TBL_ADDRESS_COUNTY_BEV);
        $this->setDisableDefinition(self::TBL_ADDRESS_STATE_BEV);
        $this->setDisableDefinition(self::TBL_ADDRESS_NATION_BEV);
        $this->setDisableDefinition(self::TBL_PHONE_NUMBER_BEV);
        $this->setDisableDefinition(self::TBL_PHONE_NUMBER_PF_BEV,);
        $this->setDisableDefinition(self::TBL_PHONE_NUMBER_PM_BEV,);
        $this->setDisableDefinition(self::TBL_PHONE_NUMBER_GF_BEV,);
        $this->setDisableDefinition(self::TBL_PHONE_NUMBER_GM_BEV,);
        $this->setDisableDefinition(self::TBL_PHONE_NUMBER_NF_BEV,);
        $this->setDisableDefinition(self::TBL_PHONE_NUMBER_NM_BEV,);
        $this->setDisableDefinition(self::TBL_PHONE_NUMBER_FP_BEV,);
        $this->setDisableDefinition(self::TBL_PHONE_NUMBER_FG_BEV,);
        $this->setDisableDefinition(self::TBL_MAIL_ADDRESS_BEV);
        $this->setDisableDefinition(self::TBL_MAIL_ADDRESS_PRIVATE_BEV);
        $this->setDisableDefinition(self::TBL_MAIL_ADDRESS_COMPANY_BEV);
        $this->setDisableDefinition(self::TBL_TO_PERSON_IS_SINGLE_PARENT_BEV);
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
