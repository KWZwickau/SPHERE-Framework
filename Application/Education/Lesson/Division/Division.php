<?php
namespace SPHERE\Application\Education\Lesson\Division;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Blackboard;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * @deprecated
 *
 * Class Division
 *
 * @package SPHERE\Application\Education\Lesson\Division
 */
class Division implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Klassen')
                , new Link\Icon(new Blackboard())
            ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'\Frontend::frontendCreateLevelDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Student/Add', __NAMESPACE__.'\Frontend::frontendStudentAdd'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Teacher/Add', __NAMESPACE__.'\Frontend::frontendTeacherAdd'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/ClassRepresentative/Add', __NAMESPACE__.'\Frontend::frontendRepresentativeAdd'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Custody/Add', __NAMESPACE__.'\Frontend::frontendCustodyAdd'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Subject/Add', __NAMESPACE__.'\Frontend::frontendSubjectAdd'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Copy', __NAMESPACE__.'\Frontend::frontendCopyDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectStudent/Add', __NAMESPACE__.'\Frontend::frontendSubjectStudentAdd'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectStudent/AddAll', __NAMESPACE__.'\Frontend::frontendSubjectStudentAddAll'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectStudent/RemoveAll', __NAMESPACE__.'\Frontend::frontendSubjectStudentRemoveAll'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectTeacher/Add', __NAMESPACE__.'\Frontend::frontendSubjectTeacherAdd'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectGroup/Add', __NAMESPACE__.'\Frontend::frontendSubjectGroupAdd'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectGroup/Change', __NAMESPACE__.'\Frontend::frontendSubjectGroupChange'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectGroup/Remove', __NAMESPACE__.'\Frontend::frontendSubjectGroupRemove'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Change', __NAMESPACE__.'\Frontend::frontendDivisionChange'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Show', __NAMESPACE__.'\Frontend::frontendDivisionShow'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SubjectTeacher/Show', __NAMESPACE__.'\Frontend::frontendSubjectTeacherShow'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Destroy', __NAMESPACE__.'\Frontend::frontendDivisionDestroy'
        ));

        /*
         * Sort
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Sort', __NAMESPACE__ . '\Sort\Frontend::frontendSortDivision')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Sort\Alphabetically', __NAMESPACE__ . '\Sort\Frontend::frontendSortDivisionAlphabetically')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'\Sort\Gender', __NAMESPACE__.'\Sort\Frontend::frontendSortDivisionGender')
        );
    }

    /**
     * @deprecated
     *
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

    /**
     * @deprecated
     *
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Education', 'Lesson', 'Division', null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }
}
