<?php

namespace SPHERE\Application\Document\Standard\SignOutCertificate;

use SPHERE\Application\Document\Generator\Generator;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Extension\Extension;

class SignOutCertificate extends Extension implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Abmeldebescheinigung'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendSignOutCertificate'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Fill', __NAMESPACE__ . '\Frontend::frontendFillSignOutCertificate'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Division', __NAMESPACE__ . '\Frontend::frontendSelectDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Division\Input', __NAMESPACE__ . '\Frontend::frontendDivisionInput'
        ));
    }

    public static function useService(): IServiceInterface
    {
        return Generator::useService();
    }

    public static function useFrontend(): IFrontendInterface
    {
        return new Frontend();
    }
}