<?php
namespace SPHERE\Application\App;

use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\IClusterInterface;

class App implements IClusterInterface
{
    public static function registerCluster(): void
    {
        Authentication::registerApplication();
    }
}
