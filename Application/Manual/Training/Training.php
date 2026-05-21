<?php

namespace SPHERE\Application\Manual\Training;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

class Training implements IApplicationInterface, IModuleInterface
{
    /**
     * @return void
     */
    public static function registerApplication(): void
    {
        self::registerModule();
    }

    /**
     * @return void
     */
    public static function registerModule(): void
    {
        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Veranstaltungen'), new Link\Icon(new Calendar()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Frontend::frontendTraining'
        ));
    }

    public static function useService()
    {

    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend(): IFrontendInterface
    {
        return new Frontend();
    }
}