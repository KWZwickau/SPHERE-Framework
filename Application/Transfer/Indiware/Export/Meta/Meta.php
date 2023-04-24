<?php

namespace SPHERE\Application\Transfer\Indiware\Export\Meta;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;
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
    public static function useService(): Service
    {
        return new Service(new Identifier('Setting', 'Consumer', null, null,
            Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity',
            __NAMESPACE__.'\Service\Entity'
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