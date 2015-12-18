<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.12.2015
 * Time: 09:40
 */

namespace SPHERE\Application\Education\Graduation\Evaluation;

use SPHERE\Application\IModuleInterface;
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
            new Link(new Link\Route(__NAMESPACE__ . '\Test'), new Link\Name('Leistungsüberprüfung'),
                new Link\Icon(new Document()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__ . '\Headmaster\Test'), new Link\Name('Leistungsüberprüfung (Leitung)'),
                new Link\Icon(new Document()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__ . '\Headmaster\Task\AppointedDate'), new Link\Name('Stichtagsnotenaufträge'),
                new Link\Icon(new Document()))
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Test',
                __NAMESPACE__ . '\Frontend::frontendTest')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Test\Selected',
                __NAMESPACE__ . '\Frontend::frontendTestSelected')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Test\Edit',
                __NAMESPACE__ . '\Frontend::frontendEditTest')
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Test\Grade\Edit',
                __NAMESPACE__ . '\Frontend::frontendEditTestGrade')
        );

        /*
         * Headmaster
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Headmaster\Test',
                __NAMESPACE__ . '\Frontend::frontendHeadmasterTest')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Headmaster\Test\Selected',
                __NAMESPACE__ . '\Frontend::frontendHeadmasterTestSelected')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Headmaster\Test\Edit',
                __NAMESPACE__ . '\Frontend::frontendHeadmasterEditTest')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Headmaster\Test\Grade\Edit',
                __NAMESPACE__ . '\Frontend::frontendHeadmasterEditTestGrade')
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Headmaster\Task\AppointedDate',
                __NAMESPACE__ . '\Frontend::frontendHeadmasterTaskAppointedDate')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Headmaster\Task\AppointedDate\Edit',
                __NAMESPACE__ . '\Frontend::frontendHeadmasterTaskAppointedDateEdit')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Headmaster\Task\AppointedDate\Division',
                __NAMESPACE__ . '\Frontend::frontendHeadmasterTaskAppointedDateDivision')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Headmaster\Task\AppointedDate\Division\Add',
                __NAMESPACE__ . '\Frontend::frontendHeadmasterTaskAppointedDateAddDivision')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Headmaster\Task\AppointedDate\Division\Remove',
                __NAMESPACE__ . '\Frontend::frontendHeadmasterTaskAppointedDateRemoveDivision')
        );

    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service(new Identifier('Education', 'Graduation', 'Evaluation', null,
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