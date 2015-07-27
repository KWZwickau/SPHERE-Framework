<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization\Token;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\System\Gatekeeper\Authorization\Token\Service;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Token
 *
 * @package SPHERE\Application\System\Gatekeeper\Token
 */
class Token implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addApplicationNavigation( new Link( new Link\Route( __NAMESPACE__ ),
            new Link\Name( 'Hardware-Token' ) ),
            new Link\Route( '/System/Gatekeeper/Authorization' )
        );
    }

    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service( new Identifier( 'System', 'Gatekeeper', 'Token' ),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }


}
