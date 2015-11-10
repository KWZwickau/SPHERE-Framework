<?php

namespace SPHERE\Application\Billing\Accounting\Basket;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

class Basket implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __NAMESPACE__.'\Frontend::frontendBasketList'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Create',
                __NAMESPACE__.'\Frontend::frontendBasketCreate'
            )->setParameterDefault('Basket', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Change',
                __NAMESPACE__.'\Frontend::frontendBasketChange'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('Basket', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Destroy',
                __NAMESPACE__.'\Frontend::frontendBasketDestroy'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('Confirm', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Commodity/Select',
                __NAMESPACE__.'\Frontend::frontendBasketCommoditySelect'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Commodity/Add',
                __NAMESPACE__.'\Frontend::frontendBasketCommodityAdd'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('CommodityId', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Commodity/Remove',
                __NAMESPACE__.'\Frontend::frontendBasketCommodityRemove'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('CommodityId', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Item',
                __NAMESPACE__.'\Frontend::frontendBasketItemStatus'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Item/Remove',
                __NAMESPACE__.'\Frontend::frontendBasketItemRemove'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Item/Change',
                __NAMESPACE__.'\Frontend::frontendBasketItemChange'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('BasketItem', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Person/Select',
                __NAMESPACE__.'\Frontend::frontendBasketPersonSelect'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Person/Add',
                __NAMESPACE__.'\Frontend::frontendBasketPersonAdd'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('PersonId', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Person/Remove',
                __NAMESPACE__.'\Frontend::frontendBasketPersonRemove'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('PersonId', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Summary',
                __NAMESPACE__.'\Frontend::frontendBasketSummary'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('Basket', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Debtor/Select',
                __NAMESPACE__.'\Frontend::frontendBasketDebtorSelect'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('Date', null)
                ->setParameterDefault('Data', null)
                ->setParameterDefault('Save', null)
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Billing', 'Accounting', 'Basket', null,
            Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

}
