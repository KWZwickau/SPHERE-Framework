<?php
namespace SPHERE\Application\Education\Graduation\Gradebook;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Gradebook
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook
 */
class Gradebook implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Notenbuch'))
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendGradeType')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Create',
                __NAMESPACE__ . '\Frontend::frontendCreateGradeType')
                ->setParameterDefault('GradeType', null)
        );
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        return new Service(new Identifier('Education', 'Graduation', 'Gradebook', null,
            Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/Service/Entity', __NAMESPACE__ . '\Service\Entity'
        );
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        return new Frontend();
    }
}
