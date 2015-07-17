<?php
namespace SPHERE\Application\System\Assistance\Error;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Error
 *
 * @package SPHERE\Application\System\Assistance\Error
 */
class Error implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Fehlermeldungen' ) )
        );
        Main::getDisplay()->addModuleNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Authenticator' ), new Link\Name( 'Authentifikator' ) )
        );
        Main::getDisplay()->addModuleNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Shutdown' ), new Link\Name( 'Betriebsstörung' ) )
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__.'/Authenticator',
                'Frontend::frontendAuthenticator'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__.'/Shutdown',
                'Frontend::frontendShutdown'
            )
        );
    }
}
