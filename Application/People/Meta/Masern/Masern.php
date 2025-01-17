<?php
namespace SPHERE\Application\People\Meta\Masern;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Masern
 *
 * @package SPHERE\Application\People\Meta\Masern
 */
class Masern implements IModuleInterface
{

    public static function registerModule()
    {
        // Implement registerModule() method.
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('People', 'Meta', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     */
    public static function useFrontend()
    {

    }
}
