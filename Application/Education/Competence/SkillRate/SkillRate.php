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
            Main::getDispatcher()->createRoute(__NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendSkills')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\Edit', __NAMESPACE__ . '\Frontend::frontendEditSkills')
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