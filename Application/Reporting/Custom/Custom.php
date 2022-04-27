<?php
namespace SPHERE\Application\Reporting\Custom;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Reporting\Custom\Chemnitz\Person\Person as ChemnitzPerson;
use SPHERE\Application\Reporting\Custom\Hormersdorf\Person\Person as HormersdorfPerson;
use SPHERE\Application\Reporting\Custom\Herrnhut\Person\Person as HerrnhutPerson;
use SPHERE\Application\Reporting\Custom\Coswig\Person\Person as CoswigPerson;
use SPHERE\Application\Reporting\Custom\Muldental\Person\Person as MuldentalPerson;
use SPHERE\Application\Reporting\Custom\Schneeberg\Person\Person as SchneebergPerson;
use SPHERE\Application\Reporting\Custom\Radebeul\Person\Person as RadebeulPerson;
use SPHERE\Application\Reporting\Custom\BadDueben\Person\Person as BadDuebenPerson;
use SPHERE\Application\Reporting\Custom\Annaberg\Person\Person as AnnabergPerson;
use SPHERE\Application\Reporting\Custom\Gersdorf\Person\Person as GersdorfPerson;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Custom
 *
 * @package SPHERE\Application\Reporting\Custom
 */
class Custom implements IApplicationInterface
{

    public static function registerApplication()
    {
        $tblConsumer = Consumer::useService()->getConsumerBySession();
        if ($tblConsumer && $tblConsumer->getType() == TblConsumer::TYPE_SACHSEN) {
            $consumerAcronym = $tblConsumer->getAcronym();

            // Chemitz
            if ($consumerAcronym === 'ESZC') {
                ChemnitzPerson::registerModule();
            }
            // Coswig
            if ($consumerAcronym === 'EVSC') {
                CoswigPerson::registerModule();
            }
            // Herrnhut
            if ($consumerAcronym === 'EZSH') {
                HerrnhutPerson::registerModule();
            }
            // Hormersdorf
            if ($consumerAcronym === 'FEGH' || $consumerAcronym === 'FESH') {
                HormersdorfPerson::registerModule();
            }
            // Muldental
            if ($consumerAcronym === 'EVAMTL') {
                MuldentalPerson::registerModule();
            }
            // Radebeul
            if ($consumerAcronym === 'EVSR') {
                RadebeulPerson::registerModule();
            }
            // Schneeberg
            if ($consumerAcronym === 'ESS') {
                SchneebergPerson::registerModule();
            }
            // Bad Düben
            if ($consumerAcronym === 'ESBD') {
                BadDuebenPerson::registerModule();
            }
            // Annaberg
            if ($consumerAcronym === 'EGE') {
                AnnabergPerson::registerModule();
            }
            // Gersdorf
            if ($consumerAcronym === 'EVOSG') {
                GersdorfPerson::registerModule();
            }
        }

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Individual'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Individual', 'Dashboard');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Auswertung'));

        return $Stage;
    }
}
