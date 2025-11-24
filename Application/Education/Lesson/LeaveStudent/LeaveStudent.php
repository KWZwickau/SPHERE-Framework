<?php

namespace SPHERE\Application\Education\Lesson\LeaveStudent;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Transfer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

class LeaveStudent implements IModuleInterface
{
    public static function registerModule(): void
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Schulabgänger'), new Link\Icon(new Transfer()))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'\Frontend::frontendLeaveStudent'
        ));
    }

    public static function useService()
    {

    }

    public static function useFrontend(): Frontend
    {
        return new Frontend();
    }
}