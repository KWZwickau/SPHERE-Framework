<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Authorization
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization
 */
class Authorization implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Rechteverwaltung' ) ),
            new Link\Route( __NAMESPACE__ )
        );
        Main::getDisplay()->addModuleNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Role' ), new Link\Name( 'Rollen' ) ),
            new Link\Route( __NAMESPACE__ )
        );
        Main::getDisplay()->addModuleNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Access' ), new Link\Name( 'Zugriffslevel' ) ),
            new Link\Route( __NAMESPACE__ )
        );
        Main::getDisplay()->addModuleNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Privilege' ), new Link\Name( 'Privilegien' ) ),
            new Link\Route( __NAMESPACE__ )
        );
        Main::getDisplay()->addModuleNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Right' ), new Link\Name( 'Rechte' ) ),
            new Link\Route( __NAMESPACE__ )
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__, __NAMESPACE__.'\Frontend\Right::frontendCreateRight' )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__.'/Role', __NAMESPACE__.'\Frontend\Role::frontendCreateRole' )
            ->setParameterDefault( 'Name', null )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__.'/Access', __NAMESPACE__.'\Frontend\Access::frontendCreateAccess' )
            ->setParameterDefault( 'Name', null )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__.'/Privilege', __NAMESPACE__.'\Frontend\Privilege::frontendCreatePrivilege' )
            ->setParameterDefault( 'Name', null )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__.'/Right', __NAMESPACE__.'\Frontend\Right::frontendCreateRight' )
            ->setParameterDefault( 'Name', null )
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service( new Identifier( 'System', 'Gatekeeper', 'Authorization' ),
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
