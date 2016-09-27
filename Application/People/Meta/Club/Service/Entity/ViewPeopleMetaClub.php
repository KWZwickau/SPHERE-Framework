<?php
namespace SPHERE\Application\People\Meta\Club\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewPeopleMetaClub")
 * @Cache(usage="READ_ONLY")
 */
class ViewPeopleMetaClub extends AbstractView
{

    const TBL_CLUB_ID = 'TblClub_Id';
    const TBL_CLUB_SERVICE_TBL_PERSON = 'TblClub_serviceTblPerson';
    const TBL_CLUB_REMARK = 'TblClub_Remark';
    const TBL_CLUB_IDENTIFIER = 'TblClub_Identifier';
    const TBL_CLUB_ENTRY_DATE = 'TblClub_EntryDate';
    const TBL_CLUB_EXIT_DATE = 'TblClub_ExitDate';

    /**
     * @Column(type="string")
     */
    protected $TblClub_Id;
    /**
     * @Column(type="string")
     */
    protected $TblClub_serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $TblClub_Remark;
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
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string View-Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Vereinsmitglieder';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_CLUB_IDENTIFIER, 'Verein: Mitgliedsnummer');
        $this->setNameDefinition(self::TBL_CLUB_ENTRY_DATE, 'Verein: Eintrittsdatum');
        $this->setNameDefinition(self::TBL_CLUB_EXIT_DATE, 'Verein: Austrittsdatum');
        $this->setNameDefinition(self::TBL_CLUB_REMARK, 'Verein: Bemerkungen');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_CLUB_SERVICE_TBL_PERSON, new ViewPerson(), ViewPerson::TBL_PERSON_ID);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Club::useService();
    }
}
