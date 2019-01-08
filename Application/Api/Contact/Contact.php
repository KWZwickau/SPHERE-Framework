<?php

namespace SPHERE\Application\Api\Contact;

use SPHERE\Application\IApplicationInterface;

/**
 * Class Contact
 *
 * @package SPHERE\Application\Api\Contact
 */
class Contact implements IApplicationInterface
{

    public static function registerApplication()
    {
        ApiContactAddress::registerApi();
        ApiAddressToPerson::registerApi();
        ApiAddressToCompany::registerApi();
        ApiPhoneToPerson::registerApi();
        ApiPhoneToCompany::registerApi();
    }
}