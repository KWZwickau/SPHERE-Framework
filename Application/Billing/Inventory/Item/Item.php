<?php

namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

class Item implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Module
         */
//        Support::registerModule();
        /**
         * Register Navigation
         */

        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __NAMESPACE__.'\Frontend::frontendItemStatus'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Create',
                __NAMESPACE__.'\Frontend::frontendItemCreate'
            )->setParameterDefault('Item', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Destroy',
                __NAMESPACE__.'\Frontend::frontendItemDestroy'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('Confirm', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Change',
                __NAMESPACE__.'\Frontend::frontendItemChange'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('Item', null)
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Billing', 'Inventory', 'Item', null,
            Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

}
