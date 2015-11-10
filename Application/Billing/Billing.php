<?php
namespace SPHERE\Application\Billing;

use SPHERE\Application\Billing\Accounting\Accounting;
use SPHERE\Application\Billing\Bookkeeping\Bookkeeping;
use SPHERE\Application\Billing\Inventory\Inventory;
use SPHERE\Application\IClusterInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Billing
 *
 * @package SPHERE\Application\Billing
 */
class Billing implements IClusterInterface
{

    public static function registerCluster()
    {

        /**
         * Register Application
         */
        Inventory::registerApplication();
        Accounting::registerApplication();
        Bookkeeping::registerApplication();

        /**
         * Register Navigation
         */
        Main::getDisplay()->addClusterNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Inventory'), new Link\Name('Fakturierung'))
        );

        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Inventory', __CLASS__.'::frontendInventory'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Bookkeeping', __CLASS__.'::frontendBookkeeping'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Accounting', __CLASS__.'::frontendAccounting'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendInventory()
    {

        $Stage = new Stage('Dashbord', ' Inventar');
        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendBookkeeping()
    {

        $Stage = new Stage('Dashbord', ' Buchungen');
        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendAccounting()
    {

        $Stage = new Stage('Dashbord', ' Rechnungen');
        return $Stage;
    }

}
