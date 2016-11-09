<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 28.09.2016
 * Time: 13:03
 */

namespace SPHERE\Application\Education\Certificate\GradeInformation;


use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

class GradeInformation implements IModuleInterface
{

    public static function registerModule()
    {

        /*
         * Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Noteninformation'))
        );

        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendSelectDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Create', __NAMESPACE__ . '\Frontend::frontendGradeInformation')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Edit' , __NAMESPACE__ . '\Frontend::frontendEditGradeInformation')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Setting', __NAMESPACE__ . '\Frontend::frontendSetting')
        );

        /*
         * AppointedDateTask
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Setting\AppointedDateTask', __NAMESPACE__ . '\Frontend::frontendAppointedDateTask')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Setting\AppointedDateTask\Select', __NAMESPACE__ . '\Frontend::frontendSelectAppointedDateTask')
        );

        /*
         *  BehaviorTask
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Setting\BehaviorTask', __NAMESPACE__ . '\Frontend::frontendBehaviorTask')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Setting\BehaviorTask\Select', __NAMESPACE__ . '\Frontend::frontendSelectBehaviorTask')
        );

        /*
         * Template
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Setting\Template\Show', __NAMESPACE__ . '\Frontend::frontendShowTemplate')
        );

        /*
         * Test
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Setting\Wizard\Behavior', __NAMESPACE__ . '\Frontend::frontendWizardBehavior')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Setting\Wizard\Remark', __NAMESPACE__ . '\Frontend::frontendWizardRemark')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Setting\Wizard\Preview', __NAMESPACE__ . '\Frontend::frontendWizardPreview')
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Setting\Preview', __NAMESPACE__ . '\Frontend::frontendPreview')
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service();
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}