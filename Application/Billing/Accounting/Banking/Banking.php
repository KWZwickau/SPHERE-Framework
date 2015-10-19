<?php

namespace SPHERE\Application\Billing\Accounting\Banking;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

class Banking implements IModuleInterface
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
        //            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Debitoren' ), new Link\Icon( new Money() ) )
        //        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __NAMESPACE__.'\Frontend::frontendBanking'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Person',
                __NAMESPACE__.'\Frontend::frontendBankingPerson'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Person/Select',
                __NAMESPACE__.'\Frontend::frontendBankingPersonSelect'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('Debtor', null)
        );
//        Main::getDispatcher()->registerRoute(
//            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Delete',
//                __NAMESPACE__.'\Frontend::frontendBankingDelete'
//            )->setParameterDefault('Id', null)
//        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Destroy',
                __NAMESPACE__.'\Frontend::frontendBankingDestroy'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('Confirm', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Commodity/Select',
                __NAMESPACE__.'\Frontend::frontendBankingCommoditySelect'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Commodity/Remove',
                __NAMESPACE__.'\Frontend::frontendBankingCommodityRemove'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Commodity/Add',
                __NAMESPACE__.'\Frontend::frontendBankingCommodityAdd'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('CommodityId', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Reference/Deactivate',
                __NAMESPACE__.'\Frontend::frontendBankingReferenceDeactivate'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Debtor/View',
                __NAMESPACE__.'\Frontend::frontendBankingDebtorView'
            )->setParameterDefault('Id', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Debtor/Change',
                __NAMESPACE__.'\Frontend::frontendBankingDebtorChange'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('Debtor', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Debtor/Reference',
                __NAMESPACE__.'\Frontend::frontendBankingDebtorReference'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('Reference', null)
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Billing', 'Accounting', 'Banking', null,
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
