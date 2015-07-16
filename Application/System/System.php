<?php
namespace SPHERE\Application\System;

use SPHERE\Application\System\Assistance\Assistance;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class System
 *
 * @package SPHERE\Application\System
 */
class System
{

    public static function registerCluster()
    {

        /**
         * Register Application
         */
        Assistance::registerApplication();
    }
}
