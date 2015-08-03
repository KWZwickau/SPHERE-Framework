<?php
namespace SPHERE\Application\Platform\System;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Platform\System\Cache\Cache;
use SPHERE\Application\Platform\System\Database\Database;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Platform\System\Test\Test;
use SPHERE\Common\Frontend\Icon\Repository\Cog;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class System
 *
 * @package SPHERE\Application\Platform\System
 */
class System implements IApplicationInterface
{

    public static function registerApplication()
    {

        /**
         * Register Module
         */
        Protocol::registerModule();
        Database::registerModule();
        Cache::registerModule();
        Test::registerModule();
        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'System' ), new Link\Icon( new Cog() ) )
        );
    }
}
