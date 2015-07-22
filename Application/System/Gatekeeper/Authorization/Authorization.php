<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization;

use SPHERE\Application\IModuleInterface;
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
        // TODO: Implement registerModule() method.
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

    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

}
