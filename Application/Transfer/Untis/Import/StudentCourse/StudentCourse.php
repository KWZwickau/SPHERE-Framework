<?php

namespace SPHERE\Application\Transfer\Untis\Import\StudentCourse;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;

class StudentCourse implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Prepare', __NAMESPACE__ . '\Frontend::frontendUpload'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Show', __NAMESPACE__ . '\Frontend::frontendShow'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Destroy', __NAMESPACE__ . '\Frontend::frontendStudentCourseDestroy'
        ));
    }

    /**
     * @return Service
     */
    public static function useService(): Service
    {
        return new Service();
    }

    /**
     * @return Frontend
     */
    public static function useFrontend(): Frontend
    {
        return (new Frontend());
    }
}