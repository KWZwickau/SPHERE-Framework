<?php
namespace SPHERE\Application\Api\Education;

use SPHERE\Application\Api\Education\Graduation\Graduation;
use SPHERE\Application\IApplicationInterface;

class Education implements IApplicationInterface
{

    public static function registerApplication()
    {

        Graduation::registerModule();
    }
}
