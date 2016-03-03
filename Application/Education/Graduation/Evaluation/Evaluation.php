<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.12.2015
 * Time: 09:40
 */

namespace SPHERE\Application\Education\Graduation\Evaluation;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\ClipBoard;
use SPHERE\Common\Frontend\Icon\Repository\Document;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;

/**
 * Class Evaluation
 *
 * @package SPHERE\Application\Education\Graduation\Evaluation
 */
class Evaluation implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'\Test'), new Link\Name('Leistungsüberprüfung'),
                new Link\Icon(new Document()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'\Headmaster\Test'), new Link\Name('Leistungsüberprüfung'),
                new Link\Icon(new Document()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'\DivisionTeacher\Task'),
                new Link\Name('Notenaufträge'),
                new Link\Icon(new ClipBoard()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'\Headmaster\Task'),
                new Link\Name('Notenaufträge'),
                new Link\Icon(new ClipBoard()))
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Test',
                __NAMESPACE__.'\Frontend::frontendTest')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Test\Selected',
                __NAMESPACE__.'\Frontend::frontendTestSelected')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Test\Edit',
                __NAMESPACE__.'\Frontend::frontendEditTest')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Test\Destroy',
                __NAMESPACE__.'\Frontend::frontendDestroyTest')
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Test\Grade\Edit',
                __NAMESPACE__.'\Frontend::frontendEditTestGrade')
        );

        /*
         * Headmaster
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Headmaster\Test',
                __NAMESPACE__.'\Frontend::frontendHeadmasterTest')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Headmaster\Test\Selected',
                __NAMESPACE__.'\Frontend::frontendHeadmasterTestSelected')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Headmaster\Test\Edit',
                __NAMESPACE__.'\Frontend::frontendHeadmasterEditTest')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Headmaster\Test\Destroy',
                __NAMESPACE__.'\Frontend::frontendHeadmasterDestroyTest')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Headmaster\Test\Grade\Edit',
                __NAMESPACE__.'\Frontend::frontendHeadmasterEditTestGrade')
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Headmaster\Task',
                __NAMESPACE__.'\Frontend::frontendHeadmasterTask')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Headmaster\Task\Edit',
                __NAMESPACE__.'\Frontend::frontendHeadmasterTaskEdit')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Headmaster\Task\Division',
                __NAMESPACE__.'\Frontend::frontendHeadmasterTaskDivision')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Headmaster\Task\Grades',
                __NAMESPACE__.'\Frontend::frontendHeadmasterTaskGrades')
        );

        /**
         * DivisionTeacher
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\DivisionTeacher\Task',
                __NAMESPACE__.'\Frontend::frontendDivisionTeacherTask')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\DivisionTeacher\Task\Grades',
                __NAMESPACE__.'\Frontend::frontendDivisionTeacherTaskGrades')
        );

    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Education', 'Graduation', 'Evaluation', null,
            Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
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
