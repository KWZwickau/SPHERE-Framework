<?php

namespace SPHERE\Application\Education\ClassRegister\ScheduleTime;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Clock;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

class ScheduleTime extends Extension implements IModuleInterface
{
    const BASIC_ROUTE = 'SPHERE\Application\Education\ClassRegister\Digital\ScheduleTime';

    /**
     * @return void
     */
    public static function registerModule(): void
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(self::BASIC_ROUTE), new Link\Name('Zeitplan'),
                new Link\Icon(new Clock()))
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(self::BASIC_ROUTE, __NAMESPACE__ . '\Frontend::frontendScheduleTime')
        );
    }

    /**
     * @return Service
     */
    public static function useService(): Service
    {
        return new Service(
            new Identifier('Education', 'Application', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend(): Frontend
    {
        return new Frontend();
    }
}