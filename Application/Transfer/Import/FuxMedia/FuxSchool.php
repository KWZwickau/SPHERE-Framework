<?php
namespace SPHERE\Application\Transfer\Import\FuxMedia;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class FuxSchool
 *
 * @package SPHERE\Application\Transfer\Import\FuxMedia
 */
class FuxSchool implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Student', __NAMESPACE__.'\Frontend::frontendStudent'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Student/Import', __NAMESPACE__.'\Frontend::frontendStudentImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Teacher', __NAMESPACE__.'\Frontend::frontendTeacherImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Division', __NAMESPACE__.'\Frontend::frontendDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Division/Import', __NAMESPACE__.'\Frontend::frontendDivisionImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Company', __NAMESPACE__.'\Frontend::frontendCompanyImport'
        ));
    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service();
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {
        return new Frontend();
    }

}
