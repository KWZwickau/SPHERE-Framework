<?php
namespace SPHERE\Application\System\Assistance;

use SPHERE\Application\System\Assistance\Error\Error;

/**
 * Class Assistance
 *
 * @package SPHERE\Application\System\Assistance
 */
class Assistance
{

    public static function registerApplication()
    {

        Error::registerModule();
    }
}
