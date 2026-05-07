<?php

namespace SPHERE\Application\App\Education;


use SPHERE\Application\App\AppException;
use SPHERE\Application\App\ApplicationInterface;
use SPHERE\Application\App\Education\ClassRegister\ClassRegister;

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
        ClassRegister::registerModule();
    }
}
