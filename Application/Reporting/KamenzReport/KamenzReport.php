<?php

namespace SPHERE\Application\Reporting\KamenzReport;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Reporting\AbstractModule;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class KamenzReport
 *
 * @package SPHERE\Application\Reporting\KamenzReport
 */
class KamenzReport extends AbstractModule implements IApplicationInterface, IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Validate/SecondarySchool', __NAMESPACE__ . '\Frontend::frontendValidateSecondarySchool'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Validate/PrimarySchool', __NAMESPACE__ . '\Frontend::frontendValidatePrimarySchool'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Validate/GrammarSchool', __NAMESPACE__ . '\Frontend::frontendValidateGrammarSchool'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {

    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

    public static function registerApplication()
    {

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Kamenz-Statistik'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendShowKamenz'
        ));

        self::registerModule();
    }
}