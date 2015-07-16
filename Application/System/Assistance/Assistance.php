<?php
namespace SPHERE\Application\System\Assistance;

use SPHERE\Application\System\Assistance\Error\Error;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Assistance
 *
 * @package SPHERE\Application\System\Assistance
 */
class Assistance
{

    public static function registerApplication()
    {

        /**
         * Register Module
         */
        Error::registerModule();
        /**
         * Register Navigation
         */
        Main::getDisplay()->addServiceNavigation(
            new Link( new Link\Route( '/System/Assistance' ), new Link\Name( 'Hilfe' ) )
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__,
                '\SPHERE\Application\System\Assistance\Assistance::navigationAssistance'
            )
        );
    }

    public static function navigationAssistance()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( '/System/Assistance' ), new Link\Name( 'Hilfe' ) )
        );
    }
}
