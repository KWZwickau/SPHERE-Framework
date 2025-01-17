<?php
namespace SPHERE\Application\Document\Standard\EnrollmentDocument;

use SPHERE\Application\Document\Generator\Generator;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Extension\Extension;

/**
 * Class EnrollmentDocument
 *
 * @package SPHERE\Application\Document\Standard\EnrollmentDocument
 */
class EnrollmentDocument extends Extension implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Schulbescheinigung'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendEnrollmentDocument'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Fill', __NAMESPACE__ . '\Frontend::frontendFillEnrollmentDocument'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Division', __NAMESPACE__ . '\Frontend::frontendSelectDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Division\Input', __NAMESPACE__ . '\Frontend::frontendDivisionInput'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Archive', __NAMESPACE__ . '\Frontend::frontendStudentArchiv'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService(): IServiceInterface
    {
        return Generator::useService();
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend(): Frontend
    {
        return new Frontend();
    }
}