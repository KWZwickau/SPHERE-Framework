<?php

namespace SPHERE\Application\Billing\Accounting\Banking;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
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
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Destroy',
                __NAMESPACE__.'\Frontend::frontendBankingDestroy'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('Confirm', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Debtor/Payment/View',
                __NAMESPACE__.'\Frontend::frontendDebtorPaymentView'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('PaymentType', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Debtor/Payment/Change',
                __NAMESPACE__.'\Frontend::frontendDebtorPaymentTypeChange'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('PaymentType', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Account/Create',
                __NAMESPACE__.'\Frontend::frontendAccountCreate'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('Account', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Account/Activate',
                __NAMESPACE__.'\Frontend::frontendAccountActivate'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('Account', null)
                ->setParameterDefault('Path', null)
                ->setParameterDefault('IdBack', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Account/Change',
                __NAMESPACE__.'\Frontend::frontendAccountChange'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('AccountId', null)
                ->setParameterDefault('Account', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Account/Destroy',
                __NAMESPACE__.'\Frontend::frontendAccountDestroy'
            )->setParameterDefault('Id', null)
                ->setParameterDefault('Account', null)
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
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Debtor/Reference/Change',
                __NAMESPACE__.'\Frontend::frontendBankingDebtorReferenceChange'
            )->setParameterDefault('DebtorId', null)
                ->setParameterDefault('ReferenceId', null)
                ->setParameterDefault('AccountId', null)
                ->setParameterDefault('Reference', null)
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Debtor/Reference',
                __NAMESPACE__.'\Frontend::frontendBankingDebtorReference'
            )->setParameterDefault('DebtorId', null)
                ->setParameterDefault('AccountId', null)
                ->setParameterDefault('Reference', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Debtor/Reference/Deactivate',
                __NAMESPACE__.'\Frontend::frontendBankingDebtorReferenceDeactivate'
            )->setParameterDefault('ReferenceId', null)
                ->setParameterDefault('AccountId', null)
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
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

}
