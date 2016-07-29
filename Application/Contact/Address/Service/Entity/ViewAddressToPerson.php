<?php
namespace SPHERE\Application\Contact\Address\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity(readOnly=true)
 * @Table(name="viewAddressToPerson")
 * @Cache(usage="READ_ONLY")
 */
class ViewAddressToPerson extends AbstractView
{

    /**
     * @Column(type="string")
     */
    protected $TblToPerson_Id;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $TblToPerson_Remark;

    /**
     * @Column(type="string")
     */
    protected $TblType_Id;
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
    protected $TblAddress_Id;
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
    protected $TblAddress_PostOfficeBox;
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
    protected $TblCity_Id;
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
    protected $TblState_Id;
    /**
     * @Column(type="string")
     */
    protected $TblState_Name;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string View-Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Adressdaten (Person)';
    }


    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition('TblToPerson_Remark', 'Adresse: Bemerkungen');
        $this->setNameDefinition('TblType_Name', 'Adresse: Adresstyp');
        $this->setNameDefinition('TblType_Description', 'Adresse: Adresstyp-Bemerkung');
        $this->setNameDefinition('TblAddress_StreetName', 'Adresse: Strasse');
        $this->setNameDefinition('TblAddress_StreetNumber', 'Adresse: Hausnummer');
        $this->setNameDefinition('TblAddress_PostOfficeBox', 'Adresse: Postfach');
        $this->setNameDefinition('TblAddress_County', 'Adresse: Kreis');
        $this->setNameDefinition('TblAddress_Nation', 'Adresse: Land');
        $this->setNameDefinition('TblCity_Code', 'Adresse: PLZ');
        $this->setNameDefinition('TblCity_Name', 'Adresse: Stadt');
        $this->setNameDefinition('TblCity_District', 'Adresse: Ortsteil');
        $this->setNameDefinition('TblState_Name', 'Adresse: Bundesland');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView('TblToPerson_serviceTblPersonTo', new ViewPerson(), 'TblPerson_Id');
    }
}
