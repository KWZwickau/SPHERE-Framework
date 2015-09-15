<?php
namespace SPHERE\Application\Corporation;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\Application\Corporation\Search\Search;
use SPHERE\Application\IClusterInterface;
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
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Firmen'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));

        $tblGroupAll = Group::useService()->getGroupAll();
        if ($tblGroupAll) {
            /** @var TblGroup $tblGroup */
            foreach ((array)$tblGroupAll as $Index => $tblGroup) {
                $tblGroupAll[$tblGroup->getName()] =
                    new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn(
                                $tblGroup->getName()
                                .new Muted(new Small('<br/>'.$tblGroup->getDescription()))
                                , array(9, 0, 7)),
                            new LayoutColumn(
                                new Muted(new Small(Group::useService()->countCompanyAllByGroup($tblGroup).'&nbsp;Mitglieder'))
                                , 2, array(LayoutColumn::GRID_OPTION_HIDDEN_SM, LayoutColumn::GRID_OPTION_HIDDEN_XS)),
                            new LayoutColumn(
                                new PullRight(
                                    new Standard('', '/Corporation/Search/Group',
                                        new \SPHERE\Common\Frontend\Icon\Repository\Group(),
                                        array('Id' => $tblGroup->getId()),
                                        'zur Gruppe')
                                ), array(3, 0, 3))
                        )
                    )));
                $tblGroupAll[$Index] = false;
            }
            $tblGroupAll = array_filter($tblGroupAll);
            Main::getDispatcher()->registerWidget('Firmen', new Panel('Firmen in Gruppen', $tblGroupAll), 4, 6);
        }

        $tblCompanyAll = Company::useService()->getCompanyAll();
        Main::getDispatcher()->registerWidget('Firmen',
            new Panel('Anzahl an Firmen', 'Insgesamt: '.count($tblCompanyAll)));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Firmen');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Firmen'));

        return $Stage;
    }
}
