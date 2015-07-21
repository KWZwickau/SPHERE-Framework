<?php
namespace SPHERE\Application\System\Gatekeeper\Token;

use SPHERE\Application\IModuleInterface;
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
        // TODO: Implement registerModule() method.
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
