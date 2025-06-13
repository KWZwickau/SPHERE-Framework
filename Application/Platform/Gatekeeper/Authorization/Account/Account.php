<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Account;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service;
use SPHERE\Application\Platform\System\Database\Database;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Account
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Account
 */
class Account implements IModuleInterface
{

    public static function registerModule()
    {

        Database::registerService(__CLASS__);
//        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__),
//            new Link\Name('Benutzerkonten')),
//            new Link\Route('/Platform/Gatekeeper/Authorization')
//        );
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__, 'Frontend::frontendAccount'
//        ));
    }

    /**
     * @return void
     */
    public static function useFrontend()
    {
        // Implement useFrontend() method if necessary.
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Platform', 'Gatekeeper', 'Authorization', 'Account'),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }
}
