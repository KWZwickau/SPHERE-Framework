<?php
namespace SPHERE\Application\Api;

use SPHERE\Application\Api\Reporting\Reporting;
use SPHERE\Application\Api\Test\Test;
use SPHERE\Application\IClusterInterface;

/**
 * Class Api
 *
 * @package SPHERE\Application\Api
 */
class Api implements IClusterInterface
{

    public static function registerCluster()
    {

        Test::registerApplication();
        Reporting::registerApplication();
    }
}
