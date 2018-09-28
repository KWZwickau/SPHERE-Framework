<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 26.07.2016
 * Time: 13:32
 */

namespace SPHERE\Application\Education\Certificate\PrintCertificate;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class PrintCertificate
 *
 * @package SPHERE\Application\Education\Certificate\PrintCertificate
 */
class PrintCertificate implements IModuleInterface
{

    public static function registerModule()
    {

        /*
       * Navigation
       */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Zeugnisse drucken'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route('SPHERE\Application\Education\Certificate\DivisionTeacherPrintCertificate'),
                new Link\Name('Zeugnisse drucken (Klassenlehrer)'))
        );

        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendPrintCertificate')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            'SPHERE\Application\Education\Certificate\DivisionTeacherPrintCertificate',
            __NAMESPACE__ . '\Frontend::frontendPrintCertificateDivisionTeacher')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Confirm', __NAMESPACE__ . '\Frontend::frontendConfirmPrintCertificate')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\History', __NAMESPACE__ . '\Frontend::frontendPrintCertificateHistory')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\History\Person', __NAMESPACE__ . '\Frontend::frontendPrintCertificateHistoryPerson')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\History\Division', __NAMESPACE__ . '\Frontend::frontendPrintCertificateHistoryDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\History\Division\Selected', __NAMESPACE__ . '\Frontend::frontendPrintCertificateHistorySelectedDivision')
        );
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {
        return new Frontend();
    }
}