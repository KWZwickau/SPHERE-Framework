<?php
namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Item
 * @package SPHERE\Application\Billing\Inventory\Item
 */
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
            ));
//        Main::getDispatcher()->registerRoute(
//            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Destroy',
//                __NAMESPACE__.'\Frontend::frontendItemDestroy'
//            )->setParameterDefault('Id', null)
//                ->setParameterDefault('Confirm', null)
//        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Change',
                __NAMESPACE__.'\Frontend::frontendItemChange'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Condition',
                __NAMESPACE__.'\Frontend::frontendItemCondition'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Condition/Change',
                __NAMESPACE__.'\Frontend::frontendItemConditionChange'
            ));
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
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

}
