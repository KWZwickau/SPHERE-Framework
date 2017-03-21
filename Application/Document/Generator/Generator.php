<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.03.2017
 * Time: 14:30
 */

namespace SPHERE\Application\Document\Generator;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Generator
 *
 * @package SPHERE\Application\Document\Standard
 */
class Generator implements IApplicationInterface, IModuleInterface
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
    public static function useService()
    {

        return new Service(
            new Identifier('Setting', 'Consumer', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }


    public static function useFrontend()
    {

    }
}