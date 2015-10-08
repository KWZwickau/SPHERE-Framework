<?php
namespace SPHERE\Application\Education\School;

use SPHERE\Application\Education\School\Building\Building;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class School
 *
 * @package SPHERE\Application\Education\School
 */
class School implements IApplicationInterface
{

    public static function registerApplication()
    {

        Type::registerModule();
        Course::registerModule();
        Building::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Schule'))
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

        $Stage = new Stage('Dashboard', 'Schule');

        return $Stage;
    }
}
