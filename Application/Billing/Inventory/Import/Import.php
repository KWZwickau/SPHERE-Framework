<?php

namespace SPHERE\Application\Billing\Inventory\Import;

use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;


/**
 * Class Import
 * @package SPHERE\Application\Billing\Inventory\Import
 */
class Import extends Extension implements IModuleInterface
{

    public static function registerModule()
    {
        if(Debtor::useService()->getDebtorSelectionCount() == 0){
            Main::getDisplay()->addModuleNavigation(
                new Link(new Link\Route(__NAMESPACE__), new Link\Name('Daten importieren'))
            );
        }
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'/Frontend::frontendDashboard'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Prepare', __NAMESPACE__.'/Frontend::frontendImportPrepare'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Upload', __NAMESPACE__.'/Frontend::frontendUpload'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Do', __NAMESPACE__.'/Frontend::frontendDoImport'
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