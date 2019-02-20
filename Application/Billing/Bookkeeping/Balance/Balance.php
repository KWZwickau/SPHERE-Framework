<?php
namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Balance
 * @package SPHERE\Application\Billing\Bookkeeping\Balance
 */
class Balance implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Module
         */
//        Error::registerModule();
        /**
         * Register Navigation Application
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route('/Billing/Balance'), new Link\Name('Belegdruck'),
                new Link\Icon(new Listing()))
        );
        /**
         * Register Navigation Module
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route('/Billing/Balance/Excel'), new Link\Name('Belegdruck Serienbrief'),
                new Link\Icon(new Listing()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route('/Billing/Balance/MonthOverview'), new Link\Name('Monatsübersicht'),
                new Link\Icon(new Listing()))
        );
//        Main::getDisplay()->addApplicationNavigation(
//            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Offene Posten' ), new Link\Icon( new Info() ) )
//        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Balance',
                __NAMESPACE__.'\Frontend::frontendBalance'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Balance/Excel',
                __NAMESPACE__.'\Frontend::frontendBalanceExcel'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('/Billing/Balance/MonthOverview',
                __NAMESPACE__.'\Frontend::frontendMonthOverview'
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
