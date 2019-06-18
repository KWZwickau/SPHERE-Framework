<?php
namespace SPHERE\Application\Transfer\Export;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Export
 *
 * @package SPHERE\Application\Transfer\Export
 */
class Export implements IApplicationInterface
{

    public static function registerApplication()
    {

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Daten exportieren'))
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

        $Stage = new Stage('Dashboard', 'Export');

        return $Stage;
    }
}
