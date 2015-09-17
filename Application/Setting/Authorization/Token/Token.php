<?php
namespace SPHERE\Application\Setting\Authorization\Token;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\YubiKey;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Token
 *
 * @package SPHERE\Application\Setting\Authorization\Token
 */
class Token implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Hardware-Schlüssel'),
                new Link\Icon(new YubiKey()))
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__, __NAMESPACE__.'\Frontend::frontendYubiKey')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(
                __NAMESPACE__.'/Destroy', __NAMESPACE__.'\Frontend::frontendDestroyToken'
            )
                ->setParameterDefault('Id', null)
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Platform', 'Gatekeeper', 'Authorization', 'Token'),
            __DIR__.'/../../../Platform/Gatekeeper/Authorization/Token/Service/Entity',
            '\SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}
