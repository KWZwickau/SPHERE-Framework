<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization\Access;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Access
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Access
 */
class Access implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addApplicationNavigation( new Link( new Link\Route( __NAMESPACE__ ),
            new Link\Name( 'Rechteverwaltung' ) ),
            new Link\Route( '/System/Gatekeeper/Authorization' )
        );
        Main::getDisplay()->addModuleNavigation( new Link( new Link\Route( __NAMESPACE__.'/Role' ),
            new Link\Name( 'Rollen' ) ),
            new Link\Route( __NAMESPACE__ )
        );
        Main::getDisplay()->addModuleNavigation( new Link( new Link\Route( __NAMESPACE__.'/Level' ),
            new Link\Name( 'Zugriffslevel' ) ),
            new Link\Route( __NAMESPACE__ )
        );
        Main::getDisplay()->addModuleNavigation( new Link( new Link\Route( __NAMESPACE__.'/Privilege' ),
            new Link\Name( 'Privilegien' ) ),
            new Link\Route( __NAMESPACE__ )
        );
        Main::getDisplay()->addModuleNavigation( new Link( new Link\Route( __NAMESPACE__.'/Right' ),
            new Link\Name( 'Rechte' ) ),
            new Link\Route( __NAMESPACE__ )
        );

        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Frontend::frontendWelcome'
        ) );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Role', __NAMESPACE__.'\Frontend\Role::frontendCreateRole'
        )
            ->setParameterDefault( 'Name', null )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Level', __NAMESPACE__.'\Frontend\Level::frontendCreateLevel'
        )
            ->setParameterDefault( 'Name', null )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Privilege', __NAMESPACE__.'\Frontend\Privilege::frontendCreatePrivilege'
        )
            ->setParameterDefault( 'Name', null )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Right', __NAMESPACE__.'\Frontend\Right::frontendCreateRight'
        )
            ->setParameterDefault( 'Name', null )
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service( new Identifier( 'System', 'Gatekeeper', 'Access' ),
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
