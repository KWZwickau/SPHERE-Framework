<?php

namespace SPHERE\Application\Billing\Accounting\Account;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

class Account implements IModuleInterface
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
//            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'FIBU-Konten' ), new Link\Icon( new FolderOpen() ) )
//        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__,
                __NAMESPACE__.'\Frontend::frontendAccountFibu'
            )->setParameterDefault( 'Account', null )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__.'\Create',
                __NAMESPACE__.'\Frontend::frontendAccountCreate'
            )->setParameterDefault( 'Account', null )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__.'\Activate',
                __NAMESPACE__.'\Frontend::frontendAccountFibuActivate'
            )->setParameterDefault( 'Id', null )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__.'\Deactivate',
                __NAMESPACE__.'\Frontend::frontendAccountFibuDeactivate'
            )->setParameterDefault( 'Id', null )
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service( new Identifier( 'Billing', 'Accounting', 'Account' ),
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
