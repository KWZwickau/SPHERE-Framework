<?php

namespace SPHERE\Application\Setting\Authorization\GroupRole;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\PersonKey;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class GroupRole
 *
 * @package SPHERE\Application\Setting\Authorization\GroupRole
 */
class GroupRole  implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Benutzerrollen'), new Link\Icon(new PersonKey()))
        );

        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendGroupRole')
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service(new Identifier('Setting', 'Consumer', null, null,
            Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/Service/Entity', __NAMESPACE__ . '\Service\Entity'
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