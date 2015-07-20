<?php
namespace SPHERE\Application\System\Gatekeeper;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\System\Gatekeeper\Account\Account;

/**
 * Class Gatekeeper
 *
 * @package SPHERE\Application\System\Gatekeeper
 */
class Gatekeeper implements IApplicationInterface
{

    public static function registerApplication()
    {

        /**
         * Register Module
         */
        Account::registerModule();
    }
}
