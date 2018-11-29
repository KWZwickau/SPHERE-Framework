<?php
namespace SPHERE\Application\Api;

use SPHERE\Application\Api\Billing\Billing;
use SPHERE\Application\Api\Contact\Contact;
use SPHERE\Application\Api\Corporation\Corporation;
use SPHERE\Application\Api\Document\Document;
use SPHERE\Application\Api\Education\Education;
use SPHERE\Application\Api\MassReplace\MassReplace;
use SPHERE\Application\Api\People\Person;
use SPHERE\Application\Api\Platform\Platform;
use SPHERE\Application\Api\Reporting\Reporting;
use SPHERE\Application\Api\Setting\Setting;
use SPHERE\Application\Api\Test\Test;
use SPHERE\Application\Api\Transfer\Transfer;
use SPHERE\Application\IClusterInterface;

/**
 * Class Api
 *
 * @package SPHERE\Application\Api
 */
class Api implements IClusterInterface
{

    public static function registerCluster()
    {

//        Billing::registerApplication();
        Contact::registerApplication();
        Corporation::registerApplication();
        Document::registerApplication();
        Education::registerApplication();
        MassReplace::registerApplication();
        Person::registerApplication();
        Platform::registerApplication();
        Reporting::registerApplication();
        Setting::registerApplication();
        Test::registerApplication();
        Transfer::registerApplication();
    }
}
