<?php
namespace SPHERE\Application\Api\People;

use SPHERE\Application\Api\People\Meta\MedicalRecord\MedicalRecordReadOnly;
use SPHERE\Application\Api\People\Meta\Student\ApiStudent;
use SPHERE\Application\Api\People\Meta\Support\ApiSupport;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\IApplicationInterface;

/**
 * Class Person
 *
 * @package SPHERE\Application\Api\People
 */
class Person implements IApplicationInterface
{

    public static function registerApplication()
    {
        ApiPerson::registerApi();
        ApiStudent::registerApi();
        ApiSupport::registerApi();
        ApiSupportReadOnly::registerApi();
        MedicalRecordReadOnly::registerApi();
    }
}