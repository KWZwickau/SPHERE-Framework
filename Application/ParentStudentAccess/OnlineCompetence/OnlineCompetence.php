<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineCompetence;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Book;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Extension\Extension;

class OnlineCompetence extends Extension implements IApplicationInterface, IModuleInterface
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
        // nur registrieren, wenn über die Mandanteneinstellung freigeschaltet ist und Personen angezeigt würden
        // oder wenn System-Account fürs Sperren der Routen
        if (self::useService()->getIsModuleRegistered()) {
            Main::getDisplay()->addApplicationNavigation(
                new Link(new Link\Route(__NAMESPACE__), new Link\Name('Kompetenzübersicht'), new Link\Icon(new Book()))
            );

            Main::getDispatcher()->registerRoute(
                Main::getDispatcher()->createRoute(__NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendOnlineCompetence')
            );
        }
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
        return new Frontend();
    }
}