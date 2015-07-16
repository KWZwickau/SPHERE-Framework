<?php
namespace SPHERE\Application\System\Assistance\Error;

use SPHERE\Common\Main;

/**
 * Class Error
 *
 * @package SPHERE\Application\System\Assistance\Error
 */
class Error
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__.'/Authenticator',
                '\SPHERE\Application\System\Assistance\Error\Authenticator\Module::frontendAssistance'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__.'/Shutdown',
                '\SPHERE\Application\System\Assistance\Error\Shutdown\Module::frontendAssistance'
            )
        );
    }
}
