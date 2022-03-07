<?php

namespace SPHERE\Application\Education\ClassRegister\Digital;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;

class Digital  implements IModuleInterface
{
    public static function registerModule()
    {
        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ , __NAMESPACE__ . '\Frontend::frontendSelectDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Teacher', __NAMESPACE__ . '\Frontend::frontendTeacherSelectDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Headmaster', __NAMESPACE__ . '\Frontend::frontendHeadmasterSelectDivision')
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service(new Identifier('Education', 'Application', null, null,
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