<?php
namespace SPHERE\Application\Platform\System\Anonymous;


use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

class Anonymous extends Extension implements IModuleInterface
{
    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Anonymisieren'))
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __NAMESPACE__.'/Frontend::frontendAnonymous'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/UpdatePerson',
                __NAMESPACE__.'/Frontend::frontendUpdatePerson'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/UpdateAddress',
                __NAMESPACE__.'/Frontend::frontendUpdateAddress'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/MySQLScript',
                __NAMESPACE__.'/Frontend::frontendMySQLScript'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Yearly',
                __NAMESPACE__.'/Frontend::frontendYearly'
            )
        );
    }

    public static function useService()
    {

        return new Service(new Identifier('Setting', 'Consumer', null, null,
            Consumer::useService()->getConsumerBySession()), '', '');
    }

    public static function useFrontend()
    {
        return new Frontend();
    }
}