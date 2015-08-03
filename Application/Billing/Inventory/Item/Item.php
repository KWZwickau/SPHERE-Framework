<?php

namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\IModuleInterface;
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
            Main::getDispatcher()->createRoute( __NAMESPACE__,
                __NAMESPACE__.'\Frontend::frontendItemStatus'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__.'/Create',
                __NAMESPACE__.'\Frontend::frontendItemCreate'
            )->setParameterDefault( 'Item', null )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__.'/Delete',
                __NAMESPACE__.'\Frontend::frontendItemDelete'
            )->setParameterDefault( 'Id', null )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__.'/Edit',
                __NAMESPACE__.'\Frontend::frontendItemEdit'
            )->setParameterDefault( 'Id', null )
                ->setParameterDefault( 'Item', null )
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service( new Identifier( 'Billing', 'Inventory', 'Item' ),
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