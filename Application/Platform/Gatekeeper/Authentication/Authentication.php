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

        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/Student'),
            new Link\Name('Schüler'), new Link\Icon(new Lock())),
            new Link\Route('/Platform/Gatekeeper/Authentication')
        );
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/Teacher'),
            new Link\Name('Lehrer'), new Link\Icon(new Lock())),
            new Link\Route('/Platform/Gatekeeper/Authentication')
        );
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/Management'),
            new Link\Name('Verwaltung'), new Link\Icon(new Lock())),
            new Link\Route('/Platform/Gatekeeper/Authentication')
        );
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/System'),
            new Link\Name('System'), new Link\Icon(new Lock())),
            new Link\Route('/Platform/Gatekeeper/Authentication')
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Frontend::frontendIdentification'
        )
            ->setParameterDefault('CredentialName', null)
            ->setParameterDefault('CredentialLock', null)
            ->setParameterDefault('CredentialKey', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Offline', __NAMESPACE__.'\Frontend::frontendDestroySession'
        )
            ->setParameterDefault('CredentialName', null)
            ->setParameterDefault('CredentialLock', null)
            ->setParameterDefault('CredentialKey', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Student', __NAMESPACE__.'\Frontend::frontendCreateSessionStudent'
        )
            ->setParameterDefault('CredentialName', null)
            ->setParameterDefault('CredentialLock', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Teacher', __NAMESPACE__.'\Frontend::frontendCreateSessionTeacher'
        )
            ->setParameterDefault('CredentialName', null)
            ->setParameterDefault('CredentialLock', null)
            ->setParameterDefault('CredentialKey', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Management', __NAMESPACE__.'\Frontend::frontendCreateSessionManagement'
        )
            ->setParameterDefault('CredentialName', null)
            ->setParameterDefault('CredentialLock', null)
            ->setParameterDefault('CredentialKey', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/System', __NAMESPACE__.'\Frontend::frontendCreateSessionSystem'
        )
            ->setParameterDefault('CredentialName', null)
            ->setParameterDefault('CredentialLock', null)
            ->setParameterDefault('CredentialKey', null)
        );

        if (Account::useService()->getAccountBySession()) {
            Main::getDispatcher()->registerRoute(
                Main::getDispatcher()->createRoute('', __NAMESPACE__.'\Frontend::frontendWelcome')
            );
        } else {
            Main::getDispatcher()->registerRoute(
                Main::getDispatcher()->createRoute('', __NAMESPACE__.'\Frontend::frontendIdentification')
            );
        }
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
