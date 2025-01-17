<?php

namespace SPHERE\Application\Transfer\Education;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\System\Database\Link\Identifier;

class Education implements IApplicationInterface, IModuleInterface
{
    public static function registerApplication()
    {
        self::registerModule();
    }

    public static function registerModule()
    {

    }

    /**
     * @return Service
     */
    public static function useService(): Service
    {
        return new Service(new Identifier('Education', 'Application', null, null,
            Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/Service/Entity', __NAMESPACE__ . '\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend(): Frontend
    {
        return new Frontend();
    }
}