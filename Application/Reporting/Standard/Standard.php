<?php
namespace SPHERE\Application\Reporting\Standard;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Reporting\Standard\Company\Company;
use SPHERE\Application\Reporting\Standard\Person\Person;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Standard
 *
 * @package SPHERE\Application\Reporting\Standard
 */
class Standard implements IApplicationInterface
{

    public static function registerApplication()
    {

        Person::registerModule();
        Company::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Standard'))
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

        $Stage = new Stage('Standard', 'Dashboard');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Auswertung'));

        return $Stage;
    }
}
