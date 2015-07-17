<?php
namespace SPHERE\Application\System\Information\Cache;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Cache
 *
 * @package SPHERE\Application\System\Information\Cache
 */
class Cache implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Cache' ) )
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__,
                'Cache::frontendCache'
            )
        );
    }

    /**
     * @return Stage
     */
    public function frontendCache()
    {

        $Stage = new Stage( 'Cache', '' );

        return $Stage;
    }
}
