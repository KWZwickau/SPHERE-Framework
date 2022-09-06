<?php
namespace SPHERE\Application\Transfer\Indiware\Import;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;

class Replacement implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Replacement', __NAMESPACE__.'/ReplacementFrontend::frontendReplacementDashboard'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Replacement/Prepare', __NAMESPACE__.'/ReplacementFrontend::frontendReplacementPrepare'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Replacement/Import', __NAMESPACE__.'/ReplacementFrontend::frontendImportReplacement'
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