<?php

namespace SPHERE\Application\Education\Competence\SkillRate;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Book;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

class SkillRate implements IModuleInterface
{
    /**
     * @return void
     */
    public static function registerModule(): void
    {
        /**
         * Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Kompetenzbewertung'), new Link\Icon(new Book()))
        );

        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendSkillRateSelect')
        );
        // nur für Route
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Teacher', __NAMESPACE__.'\Frontend::frontendSkillRateSelect')
        );
        // nur für Route
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Headmaster', __NAMESPACE__.'\Frontend::frontendSkillRateSelect')
        );
        // nur für Route
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\AllReadOnly', __NAMESPACE__.'\Frontend::frontendSkillRateSelect')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\DivisionCourse', __NAMESPACE__ . '\Frontend::frontendDivisionCourse')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Student', __NAMESPACE__ . '\Frontend::frontendStudent')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Student\Edit', __NAMESPACE__ . '\Frontend::frontendEditStudent')
        );
    }

    /**
     * @return Service
     */
    public static function useService(): Service
    {
        return new Service(
            new Identifier('Education', 'Application', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/Service/Entity',
            __NAMESPACE__ . '\Service\Entity'
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