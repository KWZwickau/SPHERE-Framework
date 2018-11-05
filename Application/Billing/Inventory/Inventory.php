<?php

namespace SPHERE\Application\Billing\Inventory;

use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Frontend\Icon\Repository\CommodityItem;
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
        Item::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Einstellungen'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Item'), new Link\Name('Beitragsarten'),
                new Link\Icon(new CommodityItem()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Price'), new Link\Name('Beitragspreise'),
                new Link\Icon(new CommodityItem()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Attribute'), new Link\Name('Merkmale'),
                new Link\Icon(new CommodityItem()))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Inventory', __CLASS__.'::frontendWelcome'
        ));

    }

}
