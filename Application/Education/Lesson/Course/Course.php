<?php
namespace SPHERE\Application\Education\Lesson\Course;

use SPHERE\Application\Education\School\Course\Course as CourseModul;
use SPHERE\Application\Education\School\Course\Service;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Briefcase;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Course
 *
 * @package SPHERE\Application\Education\School\Course
 */
class Course implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route('SPHERE\Application\Education\Lesson\Course'), new Link\Name('Ausbildung'), new Link\Icon(new Briefcase()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'\Frontend::frontendDashboard'
        ));
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
        return CourseModul::useService();
    }

}
