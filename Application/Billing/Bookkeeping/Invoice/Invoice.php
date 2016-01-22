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
         * Register Module
         */
//        Error::registerModule();
        /**
         * Register Navigation
         */
//        Main::getDisplay()->addApplicationNavigation(
//            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Rechnungen' ), new Link\Icon( new Info() ) )
//        );
//        Main::getDisplay()->addModuleNavigation(
//            new Link( new Link\Route( __NAMESPACE__.'/Order' ), new Link\Name( 'Freigeben' ), new Link\Icon( new Info() ) )
//        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __NAMESPACE__.'\Frontend::frontendInvoiceList'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Order',
                __NAMESPACE__.'\Frontend::frontendOrderOrderList'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Order/Edit',
                __NAMESPACE__.'\Frontend::frontendOrderEdit'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Show',
                __NAMESPACE__.'\Frontend::frontendInvoiceShow'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Confirm',
                __NAMESPACE__.'\Frontend::frontendInvoiceConfirm'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Cancel',
                __NAMESPACE__.'\Frontend::frontendInvoiceCancel'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Destroy',
                __NAMESPACE__.'\Frontend::frontendOrderDestroy'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Pay',
                __NAMESPACE__.'\Frontend::frontendInvoicePay'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Order/Item/Change',
                __NAMESPACE__.'\Frontend::frontendOrderItemChange'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('IdItem', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Order/Item/Remove',
                __NAMESPACE__.'\Frontend::frontendOrderItemRemove'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Order/Address/Select',
                __NAMESPACE__.'\Frontend::frontendInvoiceAddressSelect'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Order/Address/Change',
                __NAMESPACE__.'\Frontend::frontendInvoiceAddressChange'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('AddressId', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Order/Payment/Type/Select',
                __NAMESPACE__.'\Frontend::frontendInvoicePaymentTypeSelect'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(                               // ToDo Change Account! not PaymentType
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Order/PaymentType/Change',
                __NAMESPACE__.'\Frontend::frontendInvoicePaymentTypeChange'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('PaymentType', null)
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Billing', 'Bookkeeping', 'Invoice', null,
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
