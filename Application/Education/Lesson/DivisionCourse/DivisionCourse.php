<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Blackboard;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

class DivisionCourse implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Kurs')
                , new Link\Icon(new Blackboard())
            ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'\Frontend::frontendDivisionCourse'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Show', __NAMESPACE__.'\Frontend::frontendDivisionCourseShow'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/DivisionTeacher', __NAMESPACE__.'\Frontend::frontendDivisionCourseDivisionTeacher'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Representative', __NAMESPACE__.'\Frontend::frontendDivisionCourseRepresentative'
        ));
    }

    /**
     * @return Service
     */
    public static function useService(): Service
    {
        return new Service(new Identifier('Education', 'Application', null, null,
            Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/Service/Entity', __NAMESPACE__ . '\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend(): Frontend
    {
        return new Frontend();
    }
}