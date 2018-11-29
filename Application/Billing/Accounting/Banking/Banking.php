<?php

namespace SPHERE\Application\Billing\Accounting\Banking;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;


/**
 * Class Banking
 * @package SPHERE\Application\Billing\Accounting\Banking
 */
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
//                Main::getDisplay()->addApplicationNavigation(
//                    new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Debitoren' ), new Link\Icon( new Money() ) )
//                );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __NAMESPACE__.'\Frontend::frontendBanking'
            ));
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
