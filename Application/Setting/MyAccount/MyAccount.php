<?php
namespace SPHERE\Application\Setting\MyAccount;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class MyAccount
 *
 * @package SPHERE\Application\Setting\MyAccount
 */
class MyAccount implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();
    }

    public static function registerModule()
    {

        if (Account::useService()->getAccountBySession()) {
            Main::getDisplay()->addApplicationNavigation(new Link(new Link\Route(__NAMESPACE__),
                new Link\Name('Mein Benutzerkonto'), new Link\Icon(new Person())
            ));
        }

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Frontend::frontendMyAccount'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Password', __NAMESPACE__.'\Frontend::frontendChangePassword'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Consumer', __NAMESPACE__.'\Frontend::frontendChangeConsumer'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Configuration', __NAMESPACE__.'\Frontend::frontendConfiguration'
        ));
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Platform', 'Gatekeeper', 'Authorization', 'Account'),
            __DIR__.'/../../Platform/Gatekeeper/Authorization/Account/Service/Entity',
            '\SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}
