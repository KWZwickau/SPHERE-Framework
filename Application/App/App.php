<?php

namespace SPHERE\Application\App;

use SPHERE\Application\App\Authentication\Authentication;

/**
 *
 */
class App implements ClusterInterface
{
    public static function registerCluster(): void
    {
        Authentication::registerApplication();
    }
}
