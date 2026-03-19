<?php
namespace SPHERE\Application\Education\Integration;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Integration
 *
 * @package SPHERE\Application\Education\Diary
 */
class Integration implements IApplicationInterface, IModuleInterface
{
    const LOCATION = 'SPHERE\Application\Education\Integration';

    public static function registerApplication()
    {
        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Inklusion'))
        );

        self::registerModule();
    }

    public static function registerModule()
    {
        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ , self::LOCATION . '\Frontend::frontendSelectPerson')
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Selected', self::LOCATION . '\Frontend::frontendIntegration')
        );
    }


    public static function useService()
    {
        return new Service();
    }

    public static function useFrontend()
    {
        return new Frontend();
    }
}