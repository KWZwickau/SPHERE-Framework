<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewPersonContact")
 * @Cache(usage="READ_ONLY")
 */
class ViewPersonContact extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';

    const TBL_ADDRESS_STREET_NAME = 'TblAddress_StreetName';
    const TBL_ADDRESS_STREET_NUMBER = 'TblAddress_StreetNumber';
    const TBL_CITY_CODE = 'TblCity_Code';
    const TBL_CITY_CITY = 'TblCity_City';
    const TBL_CITY_DISTRICT = 'TblCity_District';
    const TBL_ADDRESS_COUNTY = 'TblAddress_County';
    const TBL_ADDRESS_STATE = 'TblState_Name';
    const TBL_ADDRESS_NATION = 'TblAddress_Nation';

    const TBL_PHONE_NUMBER = 'TblPhone_Number';
    const TBL_MAIL_ADDRESS = 'TblMail_Address';

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
    protected $TblCity_City;
    /**
     * @Column(type="string")
     */
    protected $TblCity_District;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_County;
    /**
     * @Column(type="string")
     */
    protected $TblAddress_Nation;
    /**
     * @Column(type="string")
     */
    protected $TblState_Name;
    /**
     * @Column(type="string")
     */
    protected $TblPhone_Number;
    /**
     * @Column(type="string")
     */
    protected $TblMail_Address;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        //NameDefinition
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NAME, 'Person: Straße');
        $this->setNameDefinition(self::TBL_ADDRESS_STREET_NUMBER, 'Person: Hausnummer');
        $this->setNameDefinition(self::TBL_CITY_CODE, 'Person: Postleitzahl');
        $this->setNameDefinition(self::TBL_CITY_CITY, 'Person: Ort');
        $this->setNameDefinition(self::TBL_CITY_DISTRICT, 'Person: Ortsteil');
        $this->setNameDefinition(self::TBL_ADDRESS_COUNTY, 'Person: Landkreis');
        $this->setNameDefinition(self::TBL_ADDRESS_STATE, 'Person: Bundesland');
        $this->setNameDefinition(self::TBL_ADDRESS_NATION, 'Person: Land');

        $this->setNameDefinition(self::TBL_PHONE_NUMBER, 'Person: Telefonnummer');
        $this->setNameDefinition(self::TBL_MAIL_ADDRESS, 'Person: E-Mail');

        $this->setGroupDefinition('Adressdaten', array(
            self::TBL_ADDRESS_STREET_NAME,
            self::TBL_ADDRESS_STREET_NUMBER,
            self::TBL_CITY_CODE,
            self::TBL_CITY_CITY,
            self::TBL_CITY_DISTRICT,
            self::TBL_ADDRESS_COUNTY,
            self::TBL_ADDRESS_STATE,
            self::TBL_ADDRESS_NATION,
        ));
        $this->setGroupDefinition('Kontaktdaten', array(
            self::TBL_PHONE_NUMBER,
            self::TBL_MAIL_ADDRESS,
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
            case self::TBL_CITY_CITY:
                // Test Address By Student
                $Data = array();
                $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
                $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
                if ($tblPersonList) {
                    foreach ($tblPersonList as $tblPerson) {
                        $tblAddress = $tblPerson->fetchMainAddress();
                        if ($tblAddress) {
                            $tblCity = $tblAddress->getTblCity();
                            if ($tblCity) {
                                if (!isset($Data[$tblCity->getName()])) {
                                    $Data[$tblCity->getName()] = $tblCity->getName();
                                }
                            }
                        }
                    }
                }
//                // old version: all name from City
//                $Data = Address::useService()->getPropertyList( new TblCity(), TblCity::ATTR_NAME );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
