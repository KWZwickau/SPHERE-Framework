<?php

namespace SPHERE\Application\App;

use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\App\Education\Education;
use SPHERE\Application\App\Menu\Menu;

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
        Menu::registerApplication();
    }
}
