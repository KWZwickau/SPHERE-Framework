<?php
namespace SPHERE\Application\Corporation\Company;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

/**
 * Class Company
 *
 * @package SPHERE\Application\Corporation\Company
 */
class Company extends Extension implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__ . '/Create'), new Link\Name('Institution anlegen'),
                new Link\Icon(new Building())
            )
        );
    }

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'FrontendReadOnly::frontendCompanyReadOnly'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Create', __NAMESPACE__. '\FrontendReadOnly::frontendCompanyCreate'
        ));
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Destroy', __NAMESPACE__.'\Frontend::frontendDestroyCompany')
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Contact/Create', __NAMESPACE__.'\Frontend::frontendContact'
        ));
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Corporation', 'Company', null, null, Consumer::useService()->getConsumerBySession()),
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
