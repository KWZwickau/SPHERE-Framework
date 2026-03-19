<?php
namespace SPHERE\Application\Transfer\Indiware\Import\Replacement;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;

class Replacement implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'/ReplacementFrontend::frontendReplacementDashboard'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Prepare', __NAMESPACE__.'/ReplacementFrontend::frontendReplacementPrepare'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Import', __NAMESPACE__.'/ReplacementFrontend::frontendImportReplacement'
        ));
    }

    /**
     * @return ReplacementFrontend
     */
    public static function useFrontend()
    {
        return new ReplacementFrontend();
    }

    /**
     * @return ReplacementService
     */
    public static function useService()
    {
        return new ReplacementService();
    }

}