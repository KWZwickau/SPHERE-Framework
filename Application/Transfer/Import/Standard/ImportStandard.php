<?php
namespace SPHERE\Application\Transfer\Import\Standard;

use SPHERE\Application\Education\Certificate\Generator\Repository\Element\Ruler;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\Child;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Envelope;
use SPHERE\Common\Frontend\Icon\Repository\Group;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Main;

class ImportStandard implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Student', __NAMESPACE__.'\Frontend::frontendStudentImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Interested', __NAMESPACE__.'\Frontend::frontendInterestedImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Stuff', __NAMESPACE__.'\Frontend::frontendStuffImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Company', __NAMESPACE__.'\Frontend::frontendCompanyImport'
        ));
    }

    /**
     * @return LayoutGroup
     */
    public static function getStandardLink()
    {
        $ColumnList = array();
        $ColumnList[] = new LayoutColumn(
            new Center(new Link(new Panel(new Upload().'&nbsp;&nbsp;&nbsp;'.new Group().'&nbsp;&nbsp;&nbsp;Schüler',
                '', Panel::PANEL_TYPE_PRIMARY), __NAMESPACE__.'/Student', null, array(), false, null, Link::TYPE_WHITE_LINK))
            , 2);
        $ColumnList[] = new LayoutColumn(
            new Center(new Link(new Panel(new Upload().'&nbsp;'.new Child().'&nbsp;&nbsp;Interessenten', '',
                Panel::PANEL_TYPE_PRIMARY), __NAMESPACE__.'/Interested', null, array(), false, null, Link::TYPE_WHITE_LINK))
            , 2);
        $ColumnList[] = new LayoutColumn(
            new Center(new Link(new Panel(new Upload().'&nbsp;&nbsp;'.new Nameplate().'&nbsp;&nbsp;&nbsp;Mitarbeiter/Lehrer',
                '', Panel::PANEL_TYPE_PRIMARY), __NAMESPACE__.'/Stuff', null, array(), false, null, Link::TYPE_WHITE_LINK))
            , 2);
        $ColumnList[] = new LayoutColumn(
            new Center(new Link(new Panel(new Upload().'&nbsp;&nbsp;'.new Envelope().'&nbsp;&nbsp;&nbsp;Emailadressen',
                '', Panel::PANEL_TYPE_PRIMARY), __NAMESPACE__.'/Mail/Address', null, array(), false, null, Link::TYPE_WHITE_LINK))
            , 2);
        $ColumnList[] = new LayoutColumn(
            new Center(new Link(new Panel(new Upload().'&nbsp;&nbsp;'.new Building().'&nbsp;&nbsp;&nbsp;Institutionen',
                '', Panel::PANEL_TYPE_PRIMARY), __NAMESPACE__.'/Company', null, array(), false, null, Link::TYPE_WHITE_LINK))
            , 2);

        return new LayoutGroup(
            new LayoutRow(
                $ColumnList
            )
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service();
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}