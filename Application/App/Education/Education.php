<?php

namespace SPHERE\Application\App\Education;


use SPHERE\Application\App\AppException;
use SPHERE\Application\App\ApplicationInterface;
use SPHERE\Application\App\Education\Absence\Absence;
use SPHERE\Application\App\Education\ClassRegister\ClassRegister;
use SPHERE\Application\App\Education\Grade\Grade;

/**
 *
 */
class Education implements ApplicationInterface
{
    /**
     * @throws AppException
     */
    public static function registerApplication()
    {
        Absence::registerModule();
        ClassRegister::registerModule();
        Grade::registerModule();
    }
}
