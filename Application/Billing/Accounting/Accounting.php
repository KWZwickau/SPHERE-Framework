<?php

namespace SPHERE\Application\Billing\Accounting;

use SPHERE\Application\Billing\Accounting\Causer\Causer;
use SPHERE\Application\Billing\Accounting\Creditor\Creditor;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
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
        Creditor::registerModule();
        Causer::registerModule();
        Debtor::registerModule();
    }
}
