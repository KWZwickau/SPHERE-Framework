<?php

namespace SPHERE\Application\Billing\Bookkeeping;

use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Bookkeeping
 * @package SPHERE\Application\Billing\Bookkeeping
 */
class Bookkeeping implements IApplicationInterface
{

    public static function registerApplication()
    {

        /**
         * Register Navigation
         */
        // Skip Dashboard
        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Beitragsfakturierung'))
        );

        /**
         * Register Module
         */
        Basket::registerModule();
        Invoice::registerModule();
        Balance::registerModule();

    }
}
