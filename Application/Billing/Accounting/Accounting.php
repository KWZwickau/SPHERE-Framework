<?php

namespace SPHERE\Application\Billing\Accounting;

use SPHERE\Application\Billing\Accounting\Causer\Causer;
use SPHERE\Application\Billing\Accounting\Creditor\Creditor;
use SPHERE\Application\IApplicationInterface;

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
//        Account::registerModule();
//        Banking::registerModule();
        Creditor::registerModule();
        Causer::registerModule();
    }
}
