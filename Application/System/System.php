<?php
namespace SPHERE\Application\System;

use SPHERE\Application\System\Assistance\Assistance;

/**
 * Class System
 *
 * @package SPHERE\Application\System
 */
class System
{

    public static function registerCluster()
    {

        Assistance::registerApplication();
    }
}
