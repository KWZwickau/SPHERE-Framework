<?php
namespace SPHERE\Application\Education\Graduation;

use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Graduation
 *
 * @package SPHERE\Application\Education\Graduation
 */
class Graduation implements IApplicationInterface
{

    public static function registerApplication()
    {

        Gradebook::registerModule();
//        Certificate::registerModule();
//        ScoreType::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Zensuren'))
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

        $Stage = new Stage('Dashboard', 'Zensuren');

        return $Stage;
    }
}
