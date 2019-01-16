<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Invoice
 * @package SPHERE\Application\Billing\Bookkeeping\Invoice
 */
class Invoice implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Navigation
         */
//        Main::getDisplay()->addApplicationNavigation(
//            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Rechnungen' ), new Link\Icon( new Info() ) )
//        );
        /**
         * Register Route
         */
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
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

}
