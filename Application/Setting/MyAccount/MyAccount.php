<?php
namespace SPHERE\Application\Setting\MyAccount;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

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
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}
