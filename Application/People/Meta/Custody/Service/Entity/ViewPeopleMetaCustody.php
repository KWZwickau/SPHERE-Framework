<?php
namespace SPHERE\Application\People\Meta\Custody\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewPeopleMetaCustody")
 * @Cache(usage="READ_ONLY")
 */
class ViewPeopleMetaCustody extends AbstractView
{

    const TBL_CUSTODY_ID = 'TblCustody_Id';
    const TBL_CUSTODY_SERVICE_TBL_PERSON = 'TblCustody_serviceTblPerson';
    const TBL_CUSTODY_OCCUPATION = 'TblCustody_Occupation';
    const TBL_CUSTODY_EMPLOYMENT = 'TblCustody_Employment';
    const TBL_CUSTODY_REMARK = 'TblCustody_Remark';

    /**
     * @Column(type="string")
     */
    protected $TblCustody_Id;
    /**
     * @Column(type="string")
     */
    protected $TblCustody_serviceTblPerson;
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
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string View-Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Sorgeberechtigte';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_CUSTODY_OCCUPATION, 'Sorgeberechtigte: Beruf');
        $this->setNameDefinition(self::TBL_CUSTODY_EMPLOYMENT, 'Sorgeberechtigte: Arbeitsstelle');
        $this->setNameDefinition(self::TBL_CUSTODY_REMARK, 'Sorgeberechtigte: Bemerkungen');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_CUSTODY_SERVICE_TBL_PERSON, new ViewPerson(), 'TblPerson_Id');
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Custody::useService();
    }
}
