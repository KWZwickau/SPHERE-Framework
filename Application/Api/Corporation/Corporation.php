<?php
namespace SPHERE\Application\Api\Corporation;

use SPHERE\Application\Api\Corporation\Company\ApiCompanyEdit;
use SPHERE\Application\Api\Corporation\Company\ApiCompanyReadOnly;
use SPHERE\Application\IApplicationInterface;

/**
 * Class Corporation
 *
 * @package SPHERE\Application\Api\Corporation
 */
class Corporation implements IApplicationInterface
{

    public static function registerApplication()
    {
        ContactPerson::registerApi();
        ApiCompanyEdit::registerApi();
        ApiCompanyReadOnly::registerApi();
    }
}