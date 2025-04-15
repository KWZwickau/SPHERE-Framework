<?php

namespace SPHERE\Application\Api\Transfer;

use SPHERE\Application\Api\Transfer\Indiware\AppointmentGrade\ApiAppointmentGrade;
use SPHERE\Application\Api\Transfer\Indiware\AppointmentGrade\AppointmentGrade;
use SPHERE\Application\Api\Transfer\Indiware\IndiwareLog\ApiIndiware;
use SPHERE\Application\Api\Transfer\Indiware\IndiwareLog\IndiwareLog;
use SPHERE\Application\Api\Transfer\Indiware\Meta\Meta;
use SPHERE\Application\Api\Transfer\ItsLearning\ItsLearning;
use SPHERE\Application\Api\Transfer\Standard\Import;
use SPHERE\Application\IApplicationInterface;

/**
 * Class Transfer
 * @package SPHERE\Application\Api\Transfer
 */
class Transfer implements IApplicationInterface
{

    public static function registerApplication()
    {

        ApiAppointmentGrade::registerApi();
        AppointmentGrade::registerModule();
        IndiwareLog::registerModule(); // ToDO nach dem Indiware test wieder entfernen
        ApiIndiware::registerApi(); // ToDO nach dem Indiware test wieder entfernen
        Meta::registerModule();
        \SPHERE\Application\Api\Transfer\Untis\Meta\Meta::registerModule();
        ItsLearning::registerModule();
        Import::registerModule();
    }
}