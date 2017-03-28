<?php
namespace SPHERE\Application\Api\Setting;

use SPHERE\Application\Api\Setting\ApiMyAccount\ApiMyAccount;
use SPHERE\Application\Api\Setting\UserAccount\UserAccount;
use SPHERE\Application\IApplicationInterface;

/**
 * Class Reporting
 *
 * @package SPHERE\Application\Api\Setting
 */
class Setting implements IApplicationInterface
{

    public static function registerApplication()
    {

        UserAccount::registerModule();
        ApiMyAccount::registerApi();
    }
}