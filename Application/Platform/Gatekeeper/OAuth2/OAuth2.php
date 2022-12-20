<?php
namespace SPHERE\Application\Platform\Gatekeeper\OAuth2;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;

/**
 * Class OAuth2
 *
 * @package SPHERE\Application\Platform\Gatekeeper\OAuth2
 */
class OAuth2 implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/OAuthSite', __NAMESPACE__.'/Frontend::frontendOAuthRequest'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Vidis', __NAMESPACE__.'/Frontend::frontendVidis'
        ));
    }

    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    public static function useFrontend()
    {
        return new Frontend();
    }
}
