<?php

namespace SPHERE\Application\Billing\Bookkeeping;

use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

class Bookkeeping implements IApplicationInterface
{

    public static function registerApplication()
    {

        /**
         * Register Module
         */
        Invoice::registerModule();
        Balance::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Buchungen'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Invoice'), new Link\Name('Rechnungen'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Balance'), new Link\Name('Offene Posten'))
        );

    }
}
