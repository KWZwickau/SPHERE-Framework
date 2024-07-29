<?php
namespace SPHERE\Application\Api\Platform;

use SPHERE\Application\Api\Platform\Database\Database;
use SPHERE\Application\Api\Platform\DataMaintenance\ApiDocumentStorage;
use SPHERE\Application\Api\Platform\DataMaintenance\ApiMigrateDivision;
use SPHERE\Application\Api\Platform\Gatekeeper\ApiAuthenticatorApp;
use SPHERE\Application\Api\Platform\Gatekeeper\ApiUserGroup;
use SPHERE\Application\Api\Platform\Gatekeeper\Gatekeeper;
use SPHERE\Application\Api\Platform\ReloadReceiver\ApiReloadReceiver;
use SPHERE\Application\Api\Platform\Test\ApiSystemTest;
use SPHERE\Application\Api\Platform\View\View;
use SPHERE\Application\IApplicationInterface;

/**
 * Class Platform
 *
 * @package SPHERE\Application\Api\Platform
 */
class Platform implements IApplicationInterface
{

    public static function registerApplication()
    {

        Database::registerModule();
        View::registerModule();
        Gatekeeper::registerModule();
//        ApiUserGroup::registerApi();
        ApiSystemTest::registerApi();
        ApiAuthenticatorApp::registerApi();
        ApiMigrateDivision::registerApi();
        ApiDocumentStorage::registerApi();
        ApiReloadReceiver::registerApi();
    }
}
