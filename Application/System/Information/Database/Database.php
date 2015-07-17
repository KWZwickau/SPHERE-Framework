<?php
namespace SPHERE\Application\System\Information\Database;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Database
 *
 * @package SPHERE\Application\System\Information\Database
 */
class Database implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Datenbank' ) )
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__,
                'Database::frontendDatabase'
            )
        );
    }

    /**
     * @return Stage
     */
    public function frontendDatabase()
    {

        $Stage = new Stage( 'Database', '' );

        return $Stage;
    }
}
