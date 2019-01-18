<?php
namespace SPHERE\Application\Reporting\Custom;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Reporting\Custom\Chemnitz\Person\Person as ChemnitzPerson;
use SPHERE\Application\Reporting\Custom\Hormersdorf\Person\Person as HormersdorfPerson;
use SPHERE\Application\Reporting\Custom\Herrnhut\Person\Person as HerrnhutPerson;
use SPHERE\Application\Reporting\Custom\Coswig\Person\Person as CoswigPerson;
use SPHERE\Application\Reporting\Custom\Muldental\Person\Person as MuldentalPerson;
use SPHERE\Application\Reporting\Custom\Schneeberg\Person\Person as SchneebergPerson;
use SPHERE\Application\Reporting\Custom\Radebeul\Person\Person as RadebeulPerson;
use SPHERE\Application\Reporting\Custom\BadDueben\Person\Person as BadDuebenPerson;
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

        $consumerAcronym = ( Consumer::useService()->getConsumerBySession() ? Consumer::useService()->getConsumerBySession()->getAcronym() : '' );
        // Chemitz
        if ($consumerAcronym === 'ESZC' || $consumerAcronym === 'DEMO') {
            ChemnitzPerson::registerModule();
        }
        // Coswig
        if ($consumerAcronym === 'EVSC' || $consumerAcronym === 'DEMO') {
            CoswigPerson::registerModule();
        }
        // Herrnhut
        if ($consumerAcronym === 'EZSH' || $consumerAcronym === 'DEMO') {
            HerrnhutPerson::registerModule();
        }
        // Hormersdorf
        if ($consumerAcronym === 'FEGH' || $consumerAcronym === 'FESH' || $consumerAcronym === 'DEMO') {
            HormersdorfPerson::registerModule();
        }
        // Muldental
        if ($consumerAcronym === 'EVAMTL' || $consumerAcronym === 'DEMO') {
            MuldentalPerson::registerModule();
        }
        // Radebeul
        if ($consumerAcronym === 'EVSR' || $consumerAcronym === 'DEMO') {
            RadebeulPerson::registerModule();
        }
        // Schneeberg
        if ($consumerAcronym === 'ESS' || $consumerAcronym === 'DEMO') {
            SchneebergPerson::registerModule();
        }
        // Bad Düben
        if ($consumerAcronym === 'ESBD' || $consumerAcronym === 'DEMO') {
            BadDuebenPerson::registerModule();
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
