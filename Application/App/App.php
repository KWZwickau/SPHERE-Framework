<?php

namespace SPHERE\Application\App;

use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\App\Education\Education;

/**
 *
 */
class App implements ClusterInterface
{
    /**
     * @throws AppException
     */
    public static function registerCluster(): void
    {
        Authentication::registerApplication();

        Education::registerApplication();
    }
}
