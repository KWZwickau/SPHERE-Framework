<?php
namespace SPHERE\Application\Api\Education;

use SPHERE\Application\Api\Education\Certificate\Certificate;
use SPHERE\Application\IApplicationInterface;

class Education implements IApplicationInterface
{

    public static function registerApplication()
    {

        Certificate::registerModule();
    }
}
