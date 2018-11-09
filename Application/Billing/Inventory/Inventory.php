<?php

namespace SPHERE\Application\Billing\Inventory;

use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Inventory
 * @package SPHERE\Application\Billing\Inventory
 */
class Inventory implements IApplicationInterface
{

    public static function registerApplication()
    {

        /**
         * Register Module
         */
//        Setting::registerModule();
        Item::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Inventar'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Inventory', __CLASS__.'::frontendWelcome'
        ));

    }

}
