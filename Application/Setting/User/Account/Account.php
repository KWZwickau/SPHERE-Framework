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
            new Link(new Link\Route(__NAMESPACE__.'/Custody/Add'), new Link\Name('Neu Eltern'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Student/Show'), new Link\Name('Schülerübersicht'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Custody/Show'), new Link\Name('Elternübersicht'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/Export'), new Link\Name('Export'))
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Student/Add',
                __NAMESPACE__.'\Frontend::frontendStudentAdd')
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__, __NAMESPACE__.'\Frontend::frontendPrepare')
        );
        //FrontendAddress
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Address', __NAMESPACE__.'\Frontend::frontendAddress')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Address/Edit',
                __NAMESPACE__.'\Frontend::frontendAddressEdit')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Address/Select',
                __NAMESPACE__.'\Frontend::frontendAddressSelect')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Address/IsExport',
                __NAMESPACE__.'\Frontend::frontendSendAddressIsExport')
        );
        //FrontendMail
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Mail', __NAMESPACE__.'\Frontend::frontendMail')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Mail/Edit',
                __NAMESPACE__.'\Frontend::frontendMailEdit')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Mail/Select',
                __NAMESPACE__.'\Frontend::frontendMailSelect')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Mail/IsSend',
                __NAMESPACE__.'\Frontend::frontendMailIsSend')
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

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Person',
                __NAMESPACE__.'\Frontend::frontendPreparePersonList')
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