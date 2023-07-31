<?php
namespace SPHERE\Application\Corporation\Company\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity
 * @Table(name="viewCompany")
 * @Cache(usage="READ_ONLY")
 */
class ViewCompany extends AbstractView
{

    const TBL_COMPANY_ID = 'TblCompany_Id';
    const TBL_COMPANY_NAME = 'TblCompany_Name';
    const TBL_COMPANY_EXTENDED_NAME = 'TblCompany_ExtendedName';
    const TBL_COMPANY_DESCRIPTION = 'TblCompany_Description';

    /**
     * @Column(type="string")
     */
    protected $TblCompany_Id;
    /**
     * @Column(type="string")
     */
    protected $TblCompany_Name;
    /**
     * @Column(type="string")
     */
    protected $TblCompany_ExtendedName;
    /**
     * @Column(type="string")
     */
    protected $TblCompany_Description;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Institutionendaten';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_COMPANY_NAME, 'Institution: Name');
        $this->setNameDefinition(self::TBL_COMPANY_EXTENDED_NAME, 'Institution: Zusatz');
        $this->setNameDefinition(self::TBL_COMPANY_DESCRIPTION, 'Institution: Beschreibung');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Company::useService();
    }
}
