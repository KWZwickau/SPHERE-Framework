<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineAbsence;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Extern;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Extension\Extension;

class OnlineAbsence extends Extension implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication(): void
    {
        self::registerModule();
    }

    public static function registerModule(): void
    {
        // nur registrieren, wenn über die Mandanteneinstellung freigeschaltet ist und Personen angezeigt würden
        // oder wenn System-Account fürs Sperren der Routen
        if (OnlineAbsence::useService()->getIsModuleRegistered()) {
            Main::getDisplay()->addApplicationNavigation(
                new Link(new Link\Route(__NAMESPACE__), new Link\Name('Fehlzeiten'), new Link\Icon(new Extern()))
            );

            Main::getDispatcher()->registerRoute(
                Main::getDispatcher()->createRoute(__NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendOnlineAbsence')
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