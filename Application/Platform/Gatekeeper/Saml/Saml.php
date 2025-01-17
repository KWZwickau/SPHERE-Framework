<?php
namespace SPHERE\Application\Platform\Gatekeeper\Saml;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;

/**
 * Class Saml
 *
 * @package SPHERE\Application\Platform\Gatekeeper\Saml
 */
class Saml implements IModuleInterface
{

    public static function registerModule()
    {


        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Placeholder/MetaData', __NAMESPACE__.'/Frontend::XMLMetaDataPlaceholder'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/DLLP/MetaData', __NAMESPACE__.'/Frontend::XMLMetaDataDLLP'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/DLLPDemo/MetaData', __NAMESPACE__.'/Frontend::XMLMetaDataDLLPDemo'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Login/Placeholder', __NAMESPACE__.'/Frontend::frontendLoginPlaceholder'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Login/DLLP', __NAMESPACE__.'/Frontend::frontendLoginDLLP'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Login/DLLPDemo', __NAMESPACE__.'/Frontend::frontendLoginDLLPDemo'
        ));

//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__.'/Logout/Placeholder', __NAMESPACE__.'/Frontend::frontendLogoutPlaceholder'
//        ));
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__.'/Logout/DLLP', __NAMESPACE__.'/Frontend::frontendLogoutDLLP'
//        ));
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__.'/Logout/DLLPDemo', __NAMESPACE__.'/Frontend::frontendLogoutDLLPDemo'
//        ));
    }

    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }
}
