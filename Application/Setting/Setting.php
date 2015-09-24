<?php
namespace SPHERE\Application\Setting;

use SPHERE\Application\IClusterInterface;
use SPHERE\Application\Setting\Authorization\Authorization;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\MyAccount\MyAccount;
use SPHERE\Common\Frontend\Icon\Repository\Cog;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Setting
 *
 * @package SPHERE\Application\Setting
 */
class Setting implements IClusterInterface
{

    public static function registerCluster()
    {

        MyAccount::registerApplication();
        Authorization::registerApplication();
        Consumer::registerApplication();

        Main::getDisplay()->addServiceNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Einstellungen'), new Link\Icon(new Cog()))
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__, __CLASS__.'::frontendDashboard')
        );
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Einstellungen');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Setting'));

        return $Stage;
    }
}
