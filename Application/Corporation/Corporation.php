<?php
namespace SPHERE\Application\Corporation;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\Application\Corporation\Search\Search;
use SPHERE\Application\IClusterInterface;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Corporation
 *
 * @package SPHERE\Application\Corporation
 */
class Corporation implements IClusterInterface
{

    public static function registerCluster()
    {

        Search::registerApplication();
        Company::registerApplication();
        Group::registerApplication();

        Main::getDisplay()->addClusterNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Institutionen'), new Link\Icon(new Building()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));

//        Main::getDispatcher()->registerWidget('Institutionen', array(__CLASS__, 'widgetCorporationGroupList'), 4, 6);
    }

    /**
     * @return Layout
     */
    public static function widgetCorporationGroupList()
    {

        $tblGroupAll = Group::useService()->getGroupAll();
        $tblGroupLockedList = array();
        $tblGroupCustomList = array();
        if ($tblGroupAll) {
            /** @var TblGroup $tblGroup */
            foreach ((array)$tblGroupAll as $Index => $tblGroup) {

                $countContent = new Muted(new Small(Group::useService()->countMemberByGroup($tblGroup) . '&nbsp;Institutionen'));
                $content =
                    new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn(
                                $tblGroup->getName()
                                . new Muted(new Small('<br/>' . $tblGroup->getDescription()))
                                , 5),
                            new LayoutColumn(
                                $countContent
                                , 6),
                            new LayoutColumn(
                                new PullRight(
                                    new Standard('', '/Corporation/Search/Group',
                                        new \SPHERE\Common\Frontend\Icon\Repository\Group(),
                                        array('Id' => $tblGroup->getId()))
                                ), 1)
                        )
                    )));

                if ($tblGroup->isLocked()) {
                    $tblGroupLockedList[] = $content;
                } else {
                    $tblGroupCustomList[] = $content;
                }
            }
        }

        return new Layout(new LayoutGroup(new LayoutRow(array(
            new LayoutColumn(
                new Panel('Institutionen in festen Gruppen', $tblGroupLockedList), 6
            ),
            !empty($tblGroupCustomList) ?
                new LayoutColumn(
                    new Panel('Institutionen in individuellen Gruppen', $tblGroupCustomList), 6) : null
        ))));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Institutionen');

//        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Institutionen'));
        $Stage->setContent(self::widgetCorporationGroupList());

        return $Stage;
    }
}
