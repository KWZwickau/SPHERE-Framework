<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authentication;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\Off;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Authentication
 *
 * @package SPHERE\Application\System\Gatekeeper\Authentication
 */
class Authentication implements IModuleInterface
{

    public static function registerModule()
    {

        if (Account::useService()->getAccountBySession()) {
            Main::getDisplay()->addServiceNavigation(new Link(new Link\Route(__NAMESPACE__.'/Offline'),
                new Link\Name('Abmelden'), new Link\Icon(new Off())
            ));
        } else {
            Main::getDisplay()->addServiceNavigation(new Link(new Link\Route(__NAMESPACE__),
                new Link\Name('Anmelden'), new Link\Icon(new Lock())
            ));
        }

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Offline', __NAMESPACE__.'\Frontend::frontendDestroySession'
        ));

        if (Account::useService()->getAccountBySession()) {
            Main::getDispatcher()->registerRoute(
                Main::getDispatcher()->createRoute('', __NAMESPACE__.'\Frontend::frontendWelcome')
            );
        } else {
            Main::getDispatcher()->registerRoute(
                Main::getDispatcher()->createRoute('', __NAMESPACE__.'\Frontend::frontendIdentificationCredential')
            );

            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__, __NAMESPACE__.'\Frontend::frontendIdentificationCredential'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Token', __NAMESPACE__.'\Frontend::frontendIdentificationToken'
            ));
            Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Agb', __NAMESPACE__.'\Frontend::frontendIdentificationAgb'
            ));
        }
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {

    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}
