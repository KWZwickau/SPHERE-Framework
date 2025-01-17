<?php
namespace SPHERE\Application\Api\People;

use SPHERE\Application\Api\People\Meta\Agreement\ApiAgreement;
use SPHERE\Application\Api\People\Meta\Agreement\ApiPersonAgreementStructure;
use SPHERE\Application\Api\People\Meta\Agreement\ApiStudentAgreementStructure;
use SPHERE\Application\Api\People\Meta\MedicalRecord\MedicalRecordReadOnly;
use SPHERE\Application\Api\People\Meta\Student\ApiStudent;
use SPHERE\Application\Api\People\Meta\Support\ApiSupport;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Api\People\Person\ApiFamilyEdit;
use SPHERE\Application\Api\People\Person\ApiPersonEdit;
use SPHERE\Application\Api\People\Person\ApiPersonReadOnly;
use SPHERE\Application\Api\People\Search\ApiPersonSearch;
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
        ApiStudent::registerApi();
        ApiSupport::registerApi();
        ApiSupportReadOnly::registerApi();
        MedicalRecordReadOnly::registerApi();
        ApiAgreement::registerApi();
        ApiStudentAgreementStructure::registerApi();
        ApiPersonAgreementStructure::registerApi();
        ApiPersonEdit::registerApi();
        ApiPersonReadOnly::registerApi();
        ApiFamilyEdit::registerApi();
        ApiPersonSearch::registerApi();
    }
}