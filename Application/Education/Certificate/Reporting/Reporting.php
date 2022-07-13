<?php

namespace SPHERE\Application\Education\Certificate\Reporting;

use SPHERE\Application\Education\Certificate\Approve\Frontend;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Reporting
 *
 * @package SPHERE\Application\Education\Certificate\Reporting
 */
class Reporting implements IModuleInterface
{

    public static function registerModule()
    {

        /*
         * Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Zeugnisse auswerten'))
        );

        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ , __NAMESPACE__ . '\Frontend::frontendSelect')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Diploma', __NAMESPACE__ . '\Frontend::frontendDiploma')
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