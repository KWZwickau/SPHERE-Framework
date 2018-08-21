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
 * @Table(name="viewGroupClub")
 * @Cache(usage="READ_ONLY")
 */
class ViewGroupClub extends AbstractView
{

    // Sortierung beeinflusst die Gruppenreihenfolge im Frontend
    const TBL_PERSON_ID = 'TblPerson_Id';

    const TBL_CLUB_IDENTIFIER = 'TblClub_Identifier';
    const TBL_CLUB_ENTRY_DATE = 'TblClub_EntryDate';
    const TBL_CLUB_EXIT_DATE = 'TblClub_ExitDate';
    const TBL_CLUB_REMARK = 'TblClub_Remark';

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
    protected $TblClub_Identifier;
    /**
     * @Column(type="string")
     */
    protected $TblClub_EntryDate;
    /**
     * @Column(type="string")
     */
    protected $TblClub_ExitDate;
    /**
     * @Column(type="string")
     */
    protected $TblClub_Remark;

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        //NameDefinition
        $this->setNameDefinition(self::TBL_CLUB_IDENTIFIER, 'Verein: Mitgleidsnummer');
        $this->setNameDefinition(self::TBL_CLUB_ENTRY_DATE, 'Verein: Eintrittsdatum');
        $this->setNameDefinition(self::TBL_CLUB_EXIT_DATE, 'Verein: Austrittsdatum');
        $this->setNameDefinition(self::TBL_CLUB_REMARK, 'Verein: Bemerkung');

        //GroupDefinition
        $this->setGroupDefinition('Vereinsdaten', array(
            self::TBL_CLUB_IDENTIFIER,
            self::TBL_CLUB_ENTRY_DATE,
            self::TBL_CLUB_EXIT_DATE,
            self::TBL_CLUB_REMARK,
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
//            case self::TBL_SALUTATION_SALUTATION:
//                $Data = Person::useService()->getPropertyList( new TblSalutation(''), TblSalutation::ATTR_SALUTATION );
//                $Field = $this->getFormFieldSelectBox( $Data, $PropertyName, $Label, $Icon, $doResetCount );
//                break;
            default:
                $Field = parent::getFormField( $PropertyName, $Placeholder, $Label, ($Icon?$Icon:new Pencil()), $doResetCount );
                break;
        }
        return $Field;
    }

}
