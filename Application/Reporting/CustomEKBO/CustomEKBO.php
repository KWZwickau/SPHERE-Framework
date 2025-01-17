<?php
namespace SPHERE\Application\Reporting\CustomEKBO;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Reporting\CustomEKBO\BerlinZentrum\Person\Person as BerlinZentrumPerson;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

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
//            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Individual'))
//        );
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__, __CLASS__.'::frontendDashboard'
//        ));
    }

//    /**
//     * @return Stage
//     */
//    public function frontendDashboard()
//    {
//
//        $Stage = new Stage('Individual', 'Dashboard');
//
//        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Auswertung'));
//
//        return $Stage;
//    }
}
