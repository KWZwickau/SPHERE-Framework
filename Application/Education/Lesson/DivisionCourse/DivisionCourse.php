<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Blackboard;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

class DivisionCourse implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Kurs'), new Link\Icon(new Blackboard()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route('SPHERE\Application\Education\Lesson\TeacherLectureship'), new Link\Name('Lehrauftrag'), new Link\Icon(new Education()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route('SPHERE\Application\Education\Lesson\YearChange'), new Link\Name('Schuljahreswechsel'), new Link\Icon(new Calendar()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route('SPHERE\Application\Education\Lesson\StudentSubjectTable'), new Link\Name('Stundentafel'), new Link\Icon(new Education()))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'\Frontend::frontendDivisionCourse'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Show', __NAMESPACE__.'\Frontend::frontendDivisionCourseShow'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Student', __NAMESPACE__.'\Frontend::frontendDivisionCourseStudent'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/DivisionTeacher', __NAMESPACE__.'\Frontend::frontendDivisionCourseDivisionTeacher'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Representative', __NAMESPACE__.'\Frontend::frontendDivisionCourseRepresentative'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Custody', __NAMESPACE__.'\Frontend::frontendDivisionCourseCustody'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Member/Sort', __NAMESPACE__.'\Frontend::frontendMemberSort'
        ));

        /*
         * Lehrauftrag
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            'SPHERE/Application/Education/Lesson/TeacherLectureship', __NAMESPACE__.'\Frontend::frontendTeacherLectureship'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            'SPHERE/Application/Education/Lesson/TeacherLectureship/Edit', __NAMESPACE__.'\Frontend::frontendEditTeacherLectureship'
        ));

        /*
         * Schuljahreswechsel
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            'SPHERE/Application/Education/Lesson/YearChange', __NAMESPACE__.'\Frontend::frontendYearChange'
        ));

        /*
         * Stundentafel
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            'SPHERE/Application/Education/Lesson/StudentSubjectTable', __NAMESPACE__.'\Frontend::frontendSubjectTable'
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