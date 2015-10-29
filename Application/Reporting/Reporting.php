<?php
namespace SPHERE\Application\Reporting;

use SPHERE\Application\IClusterInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Reporting\Custom\Custom;
use SPHERE\Application\Reporting\Standard\Standard;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Reporting
 *
 * @package SPHERE\Application\Reporting
 */
class Reporting implements IClusterInterface
{

    public static function registerCluster()
    {

        if (Consumer::useService()->getConsumerBySession()->getAcronym() == 'ESZC') {
            Custom::registerApplication();
        } else {
            Standard::registerApplication();
        }

        Main::getDisplay()->addClusterNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Auswertung'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Auswertung');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Auswertung'));

        return $Stage;
    }
}
