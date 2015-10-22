<?php
namespace SPHERE\Application\Api\Reporting;

use SPHERE\Application\Api\Reporting\Custom\Custom;
use SPHERE\Application\IApplicationInterface;

/**
 * Class Reporting
 *
 * @package SPHERE\Application\Api\Reporting
 */
class Reporting implements IApplicationInterface
{

    public static function registerApplication()
    {

        Custom::registerModule();
    }
}
