<?php
namespace SPHERE\Application\Api\Reporting;

use SPHERE\Application\Api\Reporting\Custom\Custom;
use SPHERE\Application\Api\Reporting\Standard\Standard;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;

/**
 * Class Reporting
 *
 * @package SPHERE\Application\Api\Reporting
 */
class Reporting implements IApplicationInterface
{

    public static function registerApplication()
    {

        if (Consumer::useService()->getConsumerBySession()->getAcronym() == 'ESZC') {
            Custom::registerModule();
        }
        Standard::registerModule();
    }
}
