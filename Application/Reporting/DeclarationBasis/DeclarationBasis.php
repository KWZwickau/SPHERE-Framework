<?php
namespace SPHERE\Application\Reporting\DeclarationBasis;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Reporting\AbstractModule;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

class DeclarationBasis extends AbstractModule implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        Main::getDisplay()->addApplicationNavigation(new Link(new Link\Route(__NAMESPACE__), new Link\Name('Stichtagsmeldung Integrationsschüler')));
        self::registerModule();
    }

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__, __NAMESPACE__.'\Frontend::frontendDeclarationBasis'));
    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service;
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

}