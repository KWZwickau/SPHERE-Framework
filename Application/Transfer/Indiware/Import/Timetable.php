<?php
namespace SPHERE\Application\Transfer\Indiware\Import;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;

class Timetable implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Timetable', __NAMESPACE__.'/TimetableFrontend::frontendTimetableDashboard'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Timetable/Prepare', __NAMESPACE__.'/TimetableFrontend::frontendTimetableImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Timetable/Import', __NAMESPACE__.'/TimetableFrontend::frontendImportTimetable'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Timetable/Edit', __NAMESPACE__.'/TimetableFrontend::frontendEditTimetable'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Timetable/Remove', __NAMESPACE__.'/TimetableFrontend::frontendRemoveTimetable'
        ));
    }

    /**
     * @return TimetableFrontend
     */
    public static function useFrontend()
    {
        return new TimetableFrontend();
    }

    /**
     * @return TimetableService
     */
    public static function useService()
    {
        return new TimetableService();
    }

}