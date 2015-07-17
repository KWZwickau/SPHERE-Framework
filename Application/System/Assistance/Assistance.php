<?php
namespace SPHERE\Application\System\Assistance;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\System\Assistance\Error\Error;
use SPHERE\Application\System\Assistance\Support\Support;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Assistance
 *
 * @package SPHERE\Application\System\Assistance
 */
class Assistance implements IApplicationInterface
{

    public static function registerApplication()
    {

        /**
         * Register Module
         */
        Error::registerModule();
        Support::registerModule();
        /**
         * Register Navigation
         */
        Main::getDisplay()->addServiceNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Hilfe' ) )
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__,
                'Assistance::frontendAssistance'
            )
        );
    }

    public function frontendAssistance()
    {

    }
}
