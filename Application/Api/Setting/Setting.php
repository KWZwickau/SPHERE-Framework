<?php
namespace SPHERE\Application\Api\Setting;

use SPHERE\Application\Api\Setting\ApiMyAccount\ApiMyAccount;
use SPHERE\Application\Api\Setting\ItsLearning\ApiItsLearning;
use SPHERE\Application\Api\Setting\Univention\ApiUnivention;
use SPHERE\Application\Api\Setting\Authorization\ApiAccount;
use SPHERE\Application\Api\Setting\Authorization\ApiGroupRole;
use SPHERE\Application\Api\Setting\Univention\ApiWorkGroup;
use SPHERE\Application\Api\Setting\UserAccount\AccountUserExcel;
use SPHERE\Application\Api\Setting\UserAccount\ApiUserAccount;
use SPHERE\Application\Api\Setting\UserAccount\ApiUserDelete;
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
        ApiMyAccount::registerApi();
        ApiUserAccount::registerApi();
        AccountUserExcel::registerModule();
        ApiUnivention::registerApi();
        ApiWorkGroup::registerApi();
        ApiItsLearning::registerApi();
        ApiGroupRole::registerApi();
        ApiAccount::registerApi();
        ApiUserDelete::registerApi();
    }
}