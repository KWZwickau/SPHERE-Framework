<?php
namespace SPHERE\Application\Reporting\CustomEKBO;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Reporting\CustomEKBO\BerlinZentrum\Person\Person as BerlinZentrumPerson;

/**
 * Class CustomEKBO
 *
 * @package SPHERE\Application\Reporting\CustomEKBO
 */
class CustomEKBO implements IApplicationInterface
{

    public static function registerApplication()
    {
        $tblConsumer = Consumer::useService()->getConsumerBySession();
        if ($tblConsumer && $tblConsumer->getType() == TblConsumer::TYPE_BERLIN) {
            $consumerAcronym = $tblConsumer->getAcronym();
            // Berlin Zentrum
            if ($consumerAcronym === 'ESBZ') {
                BerlinZentrumPerson::registerModule();
            }
        }

//        Main::getDisplay()->addApplicationNavigation(
//            new Link(new Link\Route('SPHERE\Application\Reporting\Custom'), new Link\Name('Individual'))
//        );
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            'SPHERE\Application\Reporting\Custom\SuSList', __CLASS__.'::frontendDashboard'
//        ));
    }
}
