<?php
namespace SPHERE\Application\Education\Lesson\Term\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionTeacher;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Database\Binding\AbstractView;

/**
 * @Entity(readOnly=true)
 * @Table(name="viewYear")
 * @Cache(usage="READ_ONLY")
 */
class ViewYear extends AbstractView
{

    const TBL_YEAR_ID = 'TblYear_Id';
    const TBL_YEAR_YEAR = 'TblYear_Year';
    const TBL_YEAR_DESCRIPTION = 'TblYear_Description';
//    const TBL_YEAR_NAME = 'TblYear_Name';

    /**
     * @Column(type="string")
     */
    protected $TblYear_Id;
    /**
     * @Column(type="string")
     */
    protected $TblYear_Year;
    /**
     * @Column(type="string")
     */
    protected $TblYear_Description;
//    /**
//     * @Column(type="string")
//     */
//    protected $TblYear_Name;

    /**
     * Overwrite this method to return View-ObjectName as View-DisplayName
     *
     * @return string Gui-Name of Class
     */
    public function getViewGuiName()
    {

        return 'Jahrgang';
    }

    /**
     * Use this method to set PropertyName to DisplayName conversions with "setNameDefinition()"
     *
     * @return void
     */
    public function loadNameDefinition()
    {

        $this->setNameDefinition(self::TBL_YEAR_YEAR, 'Jahr: Jahr');
        $this->setNameDefinition(self::TBL_YEAR_DESCRIPTION, 'Jahr: Beschreibung');
    }

    /**
     * Use this method to add ForeignViews to Graph with "addForeignView()"
     *
     * @return void
     */
    public function loadViewGraph()
    {

        $this->addForeignView(self::TBL_YEAR_ID, new ViewDivision(), ViewDivision::TBL_DIVISION_TBL_YEAR);
        $this->addForeignView(self::TBL_YEAR_ID, new ViewDivisionStudent(), ViewDivisionStudent::TBL_DIVISION_TBL_YEAR);
        $this->addForeignView(self::TBL_YEAR_ID, new ViewDivisionTeacher(), ViewDivisionTeacher::TBL_DIVISION_TBL_YEAR);
//        $this->addForeignView(self::TBL_YEAR_ID, new ViewSubjectTeacher(), ViewSubjectTeacher::TBL_DIVISION_TBL_YEAR);
    }

    /**
     * @return AbstractService
     */
    public function getViewService()
    {
        return Term::useService();
    }

}