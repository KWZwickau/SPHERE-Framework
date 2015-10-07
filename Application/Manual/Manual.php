<?php
namespace SPHERE\Application\Manual;

use SPHERE\Application\IClusterInterface;
use SPHERE\Application\Manual\Kreda\Kreda;
use SPHERE\Application\Manual\StyleBook\StyleBook;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Manual
 *
 * @package SPHERE\Application\Manual
 */
class Manual implements IClusterInterface
{

    public static function registerCluster()
    {

        Kreda::registerApplication();
        StyleBook::registerApplication();

        Main::getDisplay()->addServiceNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Hilfe'))
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

        $Stage = new Stage('Hilfe', 'Tips & Tricks');

        return $Stage;
    }
}
