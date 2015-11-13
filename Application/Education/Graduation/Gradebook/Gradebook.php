<?php
namespace SPHERE\Application\Education\Graduation\Gradebook;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Book;
use SPHERE\Common\Frontend\Icon\Repository\Document;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
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
            new Link(new Link\Route(__NAMESPACE__ . '\GradeType'), new Link\Name('Zensuren-Typen'),
                new Link\Icon(new Tag()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__ . '\Test'), new Link\Name('Test'),
                new Link\Icon(new Document()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__ . '\Selected'), new Link\Name('Notenbuch'),
                new Link\Icon(new Book()))
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\GradeType',
                __NAMESPACE__ . '\Frontend::frontendGradeType')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\GradeType\Create',
                __NAMESPACE__ . '\Frontend::frontendCreateGradeType')
                ->setParameterDefault('GradeType', null)
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Selected',
                __NAMESPACE__ . '\Frontend::frontendSelectedGradebook')
                ->setParameterDefault('DivisionId', null)
                ->setParameterDefault('SubjectId', null)
                ->setParameterDefault('Select', null)
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Test',
                __NAMESPACE__ . '\Frontend::frontendTest')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Test\Create',
                __NAMESPACE__ . '\Frontend::frontendCreateTest')
                ->setParameterDefault('Test', null)
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Test\Edit',
                __NAMESPACE__ . '\Frontend::frontendEditTest')
                ->setParameterDefault('Id', null)
                ->setParameterDefault('Test', null)
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Test\Grade\Edit',
                __NAMESPACE__ . '\Frontend::frontendEditTestGrade')
                ->setParameterDefault('Id', null)
                ->setParameterDefault('Grade', null)
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service(new Identifier('Education', 'Graduation', 'Gradebook', null,
            Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/Service/Entity', __NAMESPACE__ . '\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {
        return new Frontend();
    }
}
