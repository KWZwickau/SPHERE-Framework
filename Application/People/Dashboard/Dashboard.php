<?php

namespace SPHERE\Application\People\Dashboard;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\People\ContactDetails\Frontend;
use SPHERE\Common\Frontend\Icon\Repository\ClipBoard;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Extension\Extension;

class Dashboard extends Extension implements IApplicationInterface, IModuleInterface
{
    public static function registerApplication()
    {
        self::registerModule();
    }

    public static function registerModule()
    {
        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Dashboard'), new Link\Icon(new ClipBoard()))
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendDashboard')
        );
    }

    public static function useService()
    {

    }

    /**
     * @return Frontend
     */
    public static function useFrontend(): Frontend
    {
        return new Frontend();
    }
}