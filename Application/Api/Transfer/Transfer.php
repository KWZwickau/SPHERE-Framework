<?php

namespace SPHERE\Application\Api\Transfer;

use SPHERE\Application\Api\Transfer\Indiware\AppointmentGrade\AppointmentGrade;
use SPHERE\Application\IApplicationInterface;

/**
 * Class Transfer
 * @package SPHERE\Application\Api\Transfer
 */
class Transfer implements IApplicationInterface
{

    public static function registerApplication()
    {

        AppointmentGrade::registerModule();
    }
}