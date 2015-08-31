<?php
namespace SPHERE\Application\Corporation;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\Application\Corporation\Search\Search;
use SPHERE\Application\IClusterInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
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

        $tblCompanyAll = Company::useService()->getCompanyAll();
        Main::getDispatcher()->registerWidget('Firmen',
            new Panel('Anzahl an Firmen', 'Insgesamt: '.count($tblCompanyAll)));

        $tblGroupAll = Group::useService()->getGroupAll();
        if ($tblGroupAll) {
            /** @var TblGroup $tblGroup */
            foreach ((array)$tblGroupAll as $Index => $tblGroup) {
                $tblGroupAll[$tblGroup->getName()] = $tblGroup->getName().': '.Group::useService()->countCompanyAllByGroup($tblGroup);
                $tblGroupAll[$Index] = false;
            }
            $tblGroupAll = array_filter($tblGroupAll);
            Main::getDispatcher()->registerWidget('Firmen', new Panel('Firmen in Gruppen', $tblGroupAll), 2, 2);
        }
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
