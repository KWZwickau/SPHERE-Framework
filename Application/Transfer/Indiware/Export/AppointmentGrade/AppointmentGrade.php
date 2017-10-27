<?php

namespace SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;

/**
 * Class Graduation
 * @package SPHERE\Application\Transfer\Export\Graduation
 */
class AppointmentGrade implements IModuleInterface
{

    public static function registerModule()
    {
        /**
         * Register Navigation
         */
//        Main::getDisplay()->addModuleNavigation(
//            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Export Stichtagsnoten'),
//                new Link\Icon(new Download()))
//        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __NAMESPACE__.'\Frontend::frontendExport'
            )
        );
//        Main::getDispatcher()->registerRoute(
//            Main::getDispatcher()->createRoute('\Billing\Bookkeeping\Export'.'/Prepare/View',
//                __NAMESPACE__.'\Frontend::frontendPrepareView'
//            )
//        );
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
