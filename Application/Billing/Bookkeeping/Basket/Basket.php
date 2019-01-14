<?php

namespace SPHERE\Application\Billing\Bookkeeping\Basket;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Basket
 * @package SPHERE\Application\Billing\Bookkeeping\Basket
 */
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
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/View',
                __NAMESPACE__.'\Frontend::frontendBasketView'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/DebtorSelection',
                __NAMESPACE__.'\Frontend::frontendBasketDebtorSelection'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/DoInvoice',
                __NAMESPACE__.'\Frontend::frontendDoInvoice'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Bookkeeping/InvoiceView',
                __NAMESPACE__.'\Frontend::frontendInvoiceView'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Bookkeeping/InvoiceCauserView',
                __NAMESPACE__.'\Frontend::frontendInvoiceCauserView'
            )
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Billing', 'Invoice', null, null,
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