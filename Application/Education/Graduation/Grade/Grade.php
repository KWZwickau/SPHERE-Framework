<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Book;
use SPHERE\Common\Frontend\Icon\Repository\ClipBoard;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

class Grade  implements IModuleInterface
{
    public static function registerModule()
    {
        /**
         * Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'\GradeType'), new Link\Name('Zensuren-Typ'),
                new Link\Icon(new Tag()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'\ScoreType'), new Link\Name('Bewertungssystem'),
                new Link\Icon(new Quantity()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'\GradeBook'), new Link\Name('Notenbuch'), new Link\Icon(new Book()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'\Task'), new Link\Name('Notenaufträge'), new Link\Icon(new ClipBoard()))
        );

        /**
         * Route
         */
        // Zensuren-Typ
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\GradeType', __NAMESPACE__.'\Frontend::frontendGradeType')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\GradeType\Edit', __NAMESPACE__.'\Frontend::frontendEditGradeType')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\GradeType\Destroy', __NAMESPACE__.'\Frontend::frontendDestroyGradeType')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\GradeType\Activate', __NAMESPACE__.'\Frontend::frontendActivateGradeType')
        );

        // Bewertungssystem
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreType',
                __NAMESPACE__.'\Frontend::frontendScoreType')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreType\Edit',
                __NAMESPACE__.'\Frontend::frontendScoreTypeEdit')
        );

        // Notenbuch
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\GradeBook', __NAMESPACE__.'\Frontend::frontendGradeBook')
        );
        // nur für Route
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\GradeBook\Teacher', __NAMESPACE__.'\Frontend::frontendGradeBook')
        );
        // nur für Route
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\GradeBook\Headmaster', __NAMESPACE__.'\Frontend::frontendGradeBook')
        );
        // nur für Route
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\GradeBook\AllReadOnly', __NAMESPACE__.'\Frontend::frontendGradeBook')
        );

        // Notenauftrag
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Task', __NAMESPACE__.'\Frontend::frontendTask')
        );
    }

    /**
     * @return Service
     */
    public static function useService(): Service
    {
        return new Service
            (new Identifier('Education', 'Application', null, null, Consumer::useService()->getConsumerBySession()),
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