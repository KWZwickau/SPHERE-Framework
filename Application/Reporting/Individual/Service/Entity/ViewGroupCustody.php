<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Custody\Service\Entity\TblCustody;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewGroupCustody")
 * @Cache(usage="READ_ONLY")
 */
class ViewGroupCustody extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';
    const TBL_CUSTODY_OCCUPATION = 'TblCustody_Occupation';
    const TBL_CUSTODY_EMPLOYMENT = 'TblCustody_Employment';
    const TBL_CUSTODY_REMARK = 'TblCustody_Remark';

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
    protected $TblCustody_Occupation;
    /**
     * @Column(type="string")
     */
    protected $TblCustody_Employment;
    /**
     * @Column(type="string")
     */
    protected $TblCustody_Remark;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

//        //NameDefinition
        $this->setNameDefinition(self::TBL_CUSTODY_OCCUPATION, 'Sorgeberechtigte: Beruf');
        $this->setNameDefinition(self::TBL_CUSTODY_EMPLOYMENT, 'Sorgeberechtigte: Arbeitsstelle');
        $this->setNameDefinition(self::TBL_CUSTODY_REMARK, 'Sorgeberechtigte: Bemerkung');

//        //GroupDefinition
        $this->setGroupDefinition('&nbsp;', array(
            self::TBL_CUSTODY_OCCUPATION,
            self::TBL_CUSTODY_EMPLOYMENT,
            self::TBL_CUSTODY_REMARK,
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
            case self::TBL_CUSTODY_OCCUPATION:
                // old version: all name from City
                $Data = Custody::useService()->getPropertyList( new TblCustody(), TblCustody::ATTR_OCCUPATION );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            case self::TBL_CUSTODY_EMPLOYMENT:
                // old version: all name from City
                $Data = Custody::useService()->getPropertyList( new TblCustody(), TblCustody::ATTR_EMPLOYMENT );
                $Field = $this->getFormFieldAutoCompleter( $Data, $PropertyName, $Placeholder, $Label, $Icon, $doResetCount );
                break;
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
