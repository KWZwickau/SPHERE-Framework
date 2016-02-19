<?php

namespace SPHERE\Application\Billing\Accounting;

use SPHERE\Application\Billing\Accounting\Account\Account;
use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Accounting\Basket\Basket;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Accounting
 * @package SPHERE\Application\Billing\Accounting
 */
class Accounting implements IApplicationInterface
{

    public static function registerApplication()
    {

        /**
         * Register Module
         */
        Account::registerModule();
        Banking::registerModule();
        Basket::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Buchhaltung'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Account'), new Link\Name('FIBU-Konten'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Banking'), new Link\Name('Debitoren'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/BankAccount'), new Link\Name('Kontodaten'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/BankReference'), new Link\Name('Referenzen'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Basket'), new Link\Name('Warenkorb'))
        );

    }
}
