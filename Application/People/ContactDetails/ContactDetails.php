<?php

namespace SPHERE\Application\People\ContactDetails;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Extension\Extension;

class ContactDetails extends Extension implements IApplicationInterface, IModuleInterface
{
    public static function registerApplication()
    {
        self::registerModule();
    }

    public static function registerModule()
    {
        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Kontakt-Daten'), new Link\Icon(new Conversation()))
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendContactDetails')
        );
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