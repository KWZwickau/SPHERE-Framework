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
 * @Table(name="viewGroup")
 * @Cache(usage="READ_ONLY")
 */
class ViewGroup extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';

    const TBL_GROUP_ID = 'TblGroup_Id';
    const TBL_GROUP_NAME = 'TblGroup_Name';
    const TBL_GROUP_DESCRIPTION = 'TblGroup_Description';
    const TBL_GROUP_REMARK = 'TblGroup_Remark';
    const TBL_GROUP_META_TABLE = 'TblGroup_MetaTable';

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
    protected $TblGroup_Id;
    /**
     * @Column(type="string")
     */
    protected $TblGroup_Name;
    /**
     * @Column(type="string")
     */
    protected $TblGroup_Description;
    /**
     * @Column(type="string")
     */
    protected $TblGroup_Remark;
    /**
     * @Column(type="string")
     */
    protected $TblGroup_MetaTable;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        //NameDefinition
        $this->setNameDefinition(self::TBL_GROUP_ID, 'Gruppe: Name');
//        $this->setNameDefinition(self::TBL_GROUP_NAME, 'Gruppe: Name');
        $this->setNameDefinition(self::TBL_GROUP_DESCRIPTION, 'Gruppe: Beschreibung');
        $this->setNameDefinition(self::TBL_GROUP_REMARK, 'Gruppe: Bemerkung');


        //GroupDefinition
        $this->setGroupDefinition('Gruppeninformation', array(
            self::TBL_GROUP_ID,
//            self::TBL_GROUP_NAME,
            self::TBL_GROUP_DESCRIPTION,
            self::TBL_GROUP_REMARK,
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
            case self::TBL_GROUP_ID:
                // Test Address By Student
                $Data = array();
                $tblGroupList = Group::useService()->getGroupAll();
                if($tblGroupList){
                    foreach($tblGroupList as $tblGroup){
                        if($tblGroup->getName() == 'Alle'){
                            // Extend Name
                            $Data[$tblGroup->getId()] = $tblGroup->getName().' (Gruppe)';
                        } else {
                            $Data[$tblGroup->getId()] = $tblGroup->getName();
                        }
                    }
                }
//                // all group from TblGroup
//                $Data = Group::useService()->getPropertyList( new TblGroup(''), TblGroup::ATTR_NAME );
                $Field = $this->getFormFieldSelectBox( $Data, $PropertyName, $Label, $Icon, $doResetCount, false);
                break;
//            case self::TBL_GROUP_NAME:
//                $Data = Group::useService()->getPropertyList(new TblGroup(''), TblGroup::ATTR_NAME);
//                $Field = $this->getFormFieldSelectBox($Data, $PropertyName, $Label, $Icon, $doResetCount, true);
//                break;
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
