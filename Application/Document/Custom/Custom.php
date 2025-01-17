<?php
namespace SPHERE\Application\Document\Custom;

use SPHERE\Application\Document\Custom\Gersdorf\Gersdorf;
use SPHERE\Application\Document\Custom\Hoga\Hoga;
use SPHERE\Application\Document\Custom\Lebenswelt\Lebenswelt;
use SPHERE\Application\Document\Custom\Limbach\Limbach;
use SPHERE\Application\Document\Custom\Radebeul\Radebeul;
use SPHERE\Application\Document\Custom\Zwickau\Zwickau;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Custom
 *
 * @package SPHERE\Application\Document\Custom
 */
class Custom implements IApplicationInterface
{

    public static function registerApplication()
    {

        $tblConsumer = Consumer::useService()->getConsumerBySession();
        if ($tblConsumer && $tblConsumer->getType() == TblConsumer::TYPE_SACHSEN) {
            $consumerAcronym = $tblConsumer->getAcronym();
            // Lebenswelt
            if ($consumerAcronym === 'LWSZ') {
                Lebenswelt::registerModule();
            }
            if ($consumerAcronym === 'EVSR') {
                Radebeul::registerModule();
            }
            if ($consumerAcronym === 'CMS') {
                Zwickau::registerModule();
            }
            if ($consumerAcronym === 'FELS') { // local test  || $consumerAcronym === 'REF'
                Limbach::registerModule();
            }
            if ($consumerAcronym === 'HOGA') {
                Hoga::registerModule();
            }
            // Gersdorf
            if ($consumerAcronym === 'EVOSG') {
                Gersdorf::registerModule();
            }
        }

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Individual'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__ . '::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Standard', 'Dashboard');

        return $Stage;
    }
}