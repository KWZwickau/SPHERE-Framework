<?php
namespace SPHERE\Application\Platform\System\DataMaintenance;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Extension\Extension;

/**
 * Class DataMaintenance
 * @package SPHERE\Application\Platform\System\DataMaintenance
 */
class DataMaintenance extends Extension implements IModuleInterface
{
    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Datenpflege'))
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __NAMESPACE__.'/Frontend::frontendDataMaintenance'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/OverView',
                __NAMESPACE__.'/Frontend::frontendUserAccount'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Destroy',
                __NAMESPACE__.'/Frontend::frontendDestroyAccount'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Restore/Person',
                __NAMESPACE__.'/Frontend::frontendPersonRestore'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Restore\Person\Selected',
                __NAMESPACE__ . '/Frontend::frontendPersonRestoreSelected'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Grade',
                __NAMESPACE__ . '/Frontend::frontendGrade'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\GradeUnreachable',
                __NAMESPACE__ . '/Frontend::frontendGradeUnreachable'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Yearly',
                __NAMESPACE__.'/Frontend::frontendYearly'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/DivisionCourse',
                __NAMESPACE__.'/Frontend::frontendDivisionCourse'
            )
        );
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {

    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {
        return new Frontend();
    }
}