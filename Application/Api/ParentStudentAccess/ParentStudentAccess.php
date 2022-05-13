<?php

namespace SPHERE\Application\Api\ParentStudentAccess;

use SPHERE\Application\IApplicationInterface;

class ParentStudentAccess implements IApplicationInterface
{
    public static function registerApplication()
    {
        ApiOnlineAbsence::registerApi();
        ApiOnlineContactDetails::registerApi();
    }
}