<?php
namespace SPHERE\Application\People\Meta\Agreement;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Agreement
 *
 * @package SPHERE\Application\People\Meta\Agreement
 */
class Agreement implements IModuleInterface
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
