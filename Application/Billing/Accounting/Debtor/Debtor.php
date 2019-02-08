<?php
namespace SPHERE\Application\Billing\Accounting\Debtor;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;


/**
 * Class Debtor
 * @package SPHERE\Application\Billing\Accounting\Debtor
 */
class Debtor implements IModuleInterface
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
        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Beitragszahler'))
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'/Frontend::frontendDebtor'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/View', __NAMESPACE__.'/Frontend::frontendDebtorView'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Edit', __NAMESPACE__.'/Frontend::frontendDebtorEdit'
        ));

    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Billing', 'Invoice', null, null, Consumer::useService()->getConsumerBySession()),
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
