<?php
namespace SPHERE\Application\System;

use SPHERE\Application\IClusterInterface;
use SPHERE\Application\System\Assistance\Assistance;
use SPHERE\Application\System\Gatekeeper\Gatekeeper;
use SPHERE\Application\System\Platform\Platform;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class System
 *
 * @package SPHERE\Application\System
 */
class System implements IClusterInterface
{

    public static function registerCluster()
    {

        /**
         * Register Application
         */
        Gatekeeper::registerApplication();
        Assistance::registerApplication();
        Platform::registerApplication();
    }
}
