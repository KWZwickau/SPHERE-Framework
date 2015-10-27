<?php
namespace SPHERE\Application\Reporting\Custom;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Reporting\Custom\Chemnitz\Person\Person;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

class Custom implements IApplicationInterface
{

    public static function registerApplication()
    {

        Person::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__.'\Chemnitz\Person'), new Link\Name('Personen'))
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

        $Stage = new Stage('Dashboard', 'Auswertung');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Auswertung'));

        return $Stage;
    }
}
