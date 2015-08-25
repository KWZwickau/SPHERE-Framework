<?php
namespace SPHERE\Application\Contact;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\IClusterInterface;

/**
 * Class Contact
 *
 * @package SPHERE\Application\Contact
 */
class Contact implements IClusterInterface
{

    public static function registerCluster()
    {

        Address::registerApplication();
        Phone::registerApplication();
        Mail::registerApplication();
    }

}
