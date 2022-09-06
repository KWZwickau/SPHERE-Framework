<?php
namespace SPHERE\Application\Education\Certificate\Generator;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Generator
 *
 * @package SPHERE\Application\Education\Certificate\Generator
 */
class Generator implements IModuleInterface
{

    public static function registerModule()
    {
        // kein Frontend mehr vorhanden, Service wird aber noch benötigt
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
     * @return \SPHERE\Common\Frontend\IFrontendInterface|void
     */
    public static function useFrontend()
    {

        // nicht vorhanden
    }

}
