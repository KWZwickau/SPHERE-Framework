<?php
namespace SPHERE\Application\Api\People;

use SPHERE\Application\Api\People\Meta\ApiTransfer;
use SPHERE\Application\Api\People\Meta\Upload;
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
//        ApiMassAllocation::registerApi();
        ApiTransfer::registerApi();
        Upload::registerApi();
    }
}