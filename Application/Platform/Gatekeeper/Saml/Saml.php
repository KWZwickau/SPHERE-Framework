<?php
namespace SPHERE\Application\Platform\Gatekeeper\Saml;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\PersonKey;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

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
            __NAMESPACE__.'/MetaData', __NAMESPACE__.'/Frontend::XMLMetaData'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Login', __NAMESPACE__.'/Frontend::frontendLogin'
        ));
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
