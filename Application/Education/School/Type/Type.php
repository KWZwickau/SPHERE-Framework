<?php
namespace SPHERE\Application\Education\School\Type;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Type
 *
 * @package SPHERE\Application\Education\School\Type
 */
class Type implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route('SPHERE\Application\Education\Lesson\SchoolType'), new Link\Name('Schulart'), new Link\Icon(new Education()))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            'SPHERE\Application\Education\Lesson\SchoolType', __NAMESPACE__.'\Frontend::frontendSchoolType'
        ));
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {
        return new Frontend();
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Education', 'School', 'Type', null, Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/Service/Entity', __NAMESPACE__ . '\Service\Entity'
        );
    }
}
