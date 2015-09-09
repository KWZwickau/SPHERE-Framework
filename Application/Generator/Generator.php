<?php
namespace SPHERE\Application\Generator;

use SPHERE\Application\Generator\Report\Report;
use SPHERE\Application\IClusterInterface;

/**
 * Class Generator
 *
 * @package SPHERE\Application\Generator
 */
class Generator implements IClusterInterface
{

    public static function registerCluster()
    {

        Report::registerApplication();
    }

}
