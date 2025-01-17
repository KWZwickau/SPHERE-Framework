<?php

namespace SPHERE\Application\Transfer\Untis\Export\Meta;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;

class Meta extends Extension implements IModuleInterface
{

    public static function registerModule()
    {
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'\Frontend::frontendPrepare'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Export', __NAMESPACE__.'\Frontend::frontendExport'
        ));
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