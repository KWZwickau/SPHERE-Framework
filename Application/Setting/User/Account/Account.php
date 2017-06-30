<?php
namespace SPHERE\Application\Setting\User\Account;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Account
 * @package SPHERE\Application\Setting\User\Account
 */
class Account implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Student/Add'), new Link\Name('Neu Schüler'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Custody/Add'), new Link\Name('Neu Sorgeberechtigte'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Student/Show'), new Link\Name('Schülerübersicht'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Custody/Show'), new Link\Name('Sorgeberechtigtenübersicht'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Export'), new Link\Name('Export'))
        );

        // Dashboard
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Person',
                __NAMESPACE__.'\Frontend::frontendPreparePersonList')
        );
        //remove from Reset Password
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Reset',
                __NAMESPACE__.'\Frontend::frontendResetAccount')
        );
        //remove from prepare
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Destroy',
                __NAMESPACE__.'\Frontend::frontendDestroyPrepare')
        );
        // add StudentAccount
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Student/Add',
                __NAMESPACE__.'\Frontend::frontendStudentAdd')
        );
        // add CustodyAccount
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Custody/Add',
                __NAMESPACE__.'\Frontend::frontendCustodyAdd')
        );
        // show StudentAccount
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Student/Show',
                __NAMESPACE__.'\Frontend::frontendStudentShow')
        );
        // show CustodyAccount
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Custody/Show',
                __NAMESPACE__.'\Frontend::frontendCustodyShow')
        );
        // export AccountList
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Export',
                __NAMESPACE__.'\Frontend::frontendAccountExport')
        );


    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Setting', 'Consumer', null, null, Consumer::useService()->getConsumerBySession()),
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