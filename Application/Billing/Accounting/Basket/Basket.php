<?php

namespace SPHERE\Application\Billing\Accounting\Basket;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Basket
 * @package SPHERE\Application\Billing\Accounting\Basket
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
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Content',
                __NAMESPACE__.'\Frontend::frontendBasketContent'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Item/Select',
                __NAMESPACE__.'\Frontend::frontendItemSelect'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Item/Add',
                __NAMESPACE__.'\Frontend::frontendBasketItemAdd'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Commodity/Add',
                __NAMESPACE__.'\Frontend::frontendBasketCommodityAdd'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Item/Remove',
                __NAMESPACE__.'\Frontend::frontendBasketItemRemove'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Person/Select',
                __NAMESPACE__.'\Frontend::frontendBasketPersonSelect'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Person/Add',
                __NAMESPACE__.'\Frontend::frontendBasketPersonAdd'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Person/Remove',
                __NAMESPACE__.'\Frontend::frontendBasketPersonRemove'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Calculation',
                __NAMESPACE__.'\Frontend::frontendBasketCalculation'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Verification',
                __NAMESPACE__.'\Frontend::frontendBasketVerification'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Verification/Person',
                __NAMESPACE__.'\Frontend::frontendBasketVerificationPersonShow'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Verification/Edit',
                __NAMESPACE__.'\Frontend::frontendBasketVerificationEdit'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Verification/Person/Remove',
                __NAMESPACE__.'\Frontend::frontendBasketVerificationPersonRemove'
            ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Verification/Destroy',
                __NAMESPACE__.'\Frontend::frontendBasketVerificationDestroy'
            ));
//        Main::getDispatcher()->registerRoute(
//            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Summary',
//                __NAMESPACE__.'\Frontend::frontendBasketSummary'
//            )->setParameterDefault('Id', null)
//                ->setParameterDefault('Basket', null)
//        );
//        Main::getDispatcher()->registerRoute(
//            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Debtor/Select',
//                __NAMESPACE__.'\Frontend::frontendBasketDebtorSelect'
//            )->setParameterDefault('Id', null)
//                ->setParameterDefault('Date', null)
//                ->setParameterDefault('Data', null)
//                ->setParameterDefault('Save', null)
//        );
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
