<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
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

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/View'), new Link\Name('Rechnungsliste Beitragszahler'),
                new Link\Icon(new ListingTable()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/CauserView'), new Link\Name('Rechnungsliste Beitragsverursacher'),
                new Link\Icon(new ListingTable()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/UnPaid'), new Link\Name('Offene Posten'),
                new Link\Icon(new Listing()))
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/View', __NAMESPACE__.'\Frontend::frontendInvoiceView')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/CauserView',
                __NAMESPACE__.'\Frontend::frontendInvoiceCauserView')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/UnPaid', __NAMESPACE__.'\Frontend::frontendUnPaid')
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
