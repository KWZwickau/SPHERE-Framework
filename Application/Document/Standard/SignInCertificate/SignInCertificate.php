<?php

namespace SPHERE\Application\Document\Standard\SignInCertificate;

use SPHERE\Application\Document\Generator\Generator;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Extension\Extension;

class SignInCertificate extends Extension implements IModuleInterface
{
    public static function registerModule(): void
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Anmeldebescheinigung'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendSignInCertificate'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Fill', __NAMESPACE__ . '\Frontend::frontendFillSignInCertificate'
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