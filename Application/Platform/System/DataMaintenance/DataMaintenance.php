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
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Protocol',
                __NAMESPACE__.'/Frontend::frontendProtocol'
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
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Yearly',
                __NAMESPACE__.'/Frontend::frontendYearly'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/DivisionCourse',
                __NAMESPACE__.'/Frontend::frontendDivisionCourse'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/DocumentStorage/FileSize',
                __NAMESPACE__.'/Frontend::frontendFileSize'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/DocumentStorage/AllConsumers',
                __NAMESPACE__.'/Frontend::frontendAllConsumers'
            )
        );
        // ToDO nach dem Indiware test wieder entfernen
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/IndiwareLog',
                __NAMESPACE__.'/Frontend::frontendIndiwareLog'
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