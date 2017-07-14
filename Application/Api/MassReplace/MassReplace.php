<?php
namespace SPHERE\Application\Api\MassReplace;

use SPHERE\Application\IApplicationInterface;

/**
 * Class MassReplace
 *
 * @package SPHERE\Application\Api\MassReplace
 */
class MassReplace implements IApplicationInterface
{

    public static function registerApplication()
    {
        ApiMassReplace::registerApi();
    }
}