<?php
namespace SPHERE\Application\Education\ClassRegister\Timetable;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

/**
 * Class Timetable
 *
 * @package SPHERE\Application\Education\ClassRegister\Timetable
 */
class Timetable extends Extension implements IModuleInterface
{
    const BASIC_ROUTE = 'SPHERE\Application\Education\ClassRegister\Digital\Timetable';

    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(self::BASIC_ROUTE), new Link\Name('Stundenplan'),
                new Link\Icon(new Calendar()))
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(self::BASIC_ROUTE, __NAMESPACE__ . '\Frontend::frontendTimetable')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(self::BASIC_ROUTE . '\Select', __NAMESPACE__ . '\Frontend::frontendSelectDivisionCourse')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(self::BASIC_ROUTE . '\Show', __NAMESPACE__ . '\Frontend::frontendShowTimetable')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(self::BASIC_ROUTE . '\Edit', __NAMESPACE__ . '\Frontend::frontendEditTimetable')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(self::BASIC_ROUTE . '\Week', __NAMESPACE__ . '\Frontend::frontendTimetableWeek')
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {
        return new Frontend();
    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service(
            new Identifier('Education', 'Application', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Stundenplan', 'Dashboard');
        return $Stage;
    }
}
