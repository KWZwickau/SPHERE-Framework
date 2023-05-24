<?php
namespace SPHERE\Application\Education\Graduation\Gradebook;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Book;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * @deprecated
 *
 * Class Gradebook
 *
 * @package SPHERE\Application\Education\Graduation\Gradebook
 */
class Gradebook implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'\Score'), new Link\Name('Berechnungsvorschrift'),
                new Link\Icon(new Pencil()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'\Type'), new Link\Name('Bewertungssystem'),
                new Link\Icon(new Quantity()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'\MinimumGradeCount'), new Link\Name('Mindestnotenanzahl'),
                new Link\Icon(new Quantity()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'\Gradebook'), new Link\Name('Notenbuch'),
                new Link\Icon(new Book()))
        );

        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Gradebook',
                __NAMESPACE__.'\Frontend::frontendGradebook')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Gradebook\Teacher',
                __NAMESPACE__.'\Frontend::frontendTeacherGradebook')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Gradebook\Teacher\Selected',
                __NAMESPACE__.'\Frontend::frontendTeacherSelectedGradebook')
        );

        // studentoverview for teachers
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Gradebook\Teacher\Division', __NAMESPACE__ . '\Frontend::frontendTeacherDivisionList')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Gradebook\Teacher\Division\Student', __NAMESPACE__ . '\Frontend::frontendTeacherSelectStudent')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '\Gradebook\Teacher\Division\Student\Overview', __NAMESPACE__ . '\Frontend::frontendTeacherStudentOverview')
        );

        /*
         * Headmaster
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Gradebook\Headmaster',
                __NAMESPACE__.'\Frontend::frontendHeadmasterGradebook')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Gradebook\Headmaster\Selected',
                __NAMESPACE__.'\Frontend::frontendHeadmasterSelectedGradebook')
        );
        // StudentOverview for Headmaster
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'\Gradebook\Headmaster\Division', __NAMESPACE__.'\Frontend::frontendHeadmasterDivisionList')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'\Gradebook\Headmaster\Division\Student', __NAMESPACE__.'\Frontend::frontendHeadmasterSelectStudent')
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'\Gradebook\Headmaster\Division\Student\Overview', __NAMESPACE__.'\Frontend::frontendHeadmasterStudentOverview')
        );

        /*
         * ScoreRule
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score',
                __NAMESPACE__.'\Frontend::frontendScore')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Edit',
                __NAMESPACE__.'\Frontend::frontendEditScore')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Destroy',
                __NAMESPACE__.'\Frontend::frontendDestroyScore')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Activate',
                __NAMESPACE__.'\Frontend::frontendActivateScore')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Condition',
                __NAMESPACE__.'\Frontend::frontendScoreCondition')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Condition\Edit',
                __NAMESPACE__.'\Frontend::frontendEditScoreCondition')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Condition\Destroy',
                __NAMESPACE__.'\Frontend::frontendDestroyScoreCondition')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Condition\Activate',
                __NAMESPACE__.'\Frontend::frontendActivateScoreCondition')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Condition\Select',
                __NAMESPACE__.'\Frontend::frontendScoreRuleConditionSelect')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Condition\Add',
                __NAMESPACE__.'\Frontend::frontendScoreRuleConditionAdd')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Condition\Remove',
                __NAMESPACE__.'\Frontend::frontendScoreRuleConditionRemove')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Group',
                __NAMESPACE__.'\Frontend::frontendScoreGroup')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Group\Edit',
                __NAMESPACE__.'\Frontend::frontendEditScoreGroup')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Group\Destroy',
                __NAMESPACE__.'\Frontend::frontendDestroyScoreGroup')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Group\Activate',
                __NAMESPACE__.'\Frontend::frontendActivateScoreGroup')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Condition\Group\Select',
                __NAMESPACE__.'\Frontend::frontendScoreGroupSelect')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Condition\Group\Add',
                __NAMESPACE__.'\Frontend::frontendScoreGroupAdd')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Condition\Group\Remove',
                __NAMESPACE__.'\Frontend::frontendScoreGroupRemove')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Group\GradeType\Select',
                __NAMESPACE__.'\Frontend::frontendScoreGroupGradeTypeSelect')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Group\GradeType\Add',
                __NAMESPACE__.'\Frontend::frontendScoreGroupGradeTypeAdd')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Group\GradeType\Remove',
                __NAMESPACE__.'\Frontend::frontendScoreGroupGradeTypeRemove')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Condition\GradeType\Select',
                __NAMESPACE__.'\Frontend::frontendScoreConditionGradeTypeSelect')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Condition\GradeType\Add',
                __NAMESPACE__.'\Frontend::frontendScoreConditionGradeTypeAdd')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Condition\GradeType\Remove',
                __NAMESPACE__.'\Frontend::frontendScoreConditionGradeTypeRemove')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\Division',
                __NAMESPACE__.'\Frontend::frontendScoreDivision')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Score\SubjectGroup',
                __NAMESPACE__.'\Frontend::frontendScoreSubjectGroup')
        );

        /*
         * ScoreType
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Type',
                __NAMESPACE__.'\Frontend::frontendScoreType')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Type\Select',
                __NAMESPACE__.'\Frontend::frontendScoreTypeSelect')
        );

        /*
         * MinimumGradeCount
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\MinimumGradeCount',
                __NAMESPACE__.'\MinimumGradeCount\Frontend::frontendMinimumGradeCount')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\MinimumGradeCount\Edit',
                __NAMESPACE__.'\MinimumGradeCount\Frontend::frontendEditMinimumGradeCount')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\MinimumGradeCount\Destroy',
                __NAMESPACE__.'\MinimumGradeCount\Frontend::frontendDestroyMinimumGradeCount')
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Gradebook\MinimumGradeCount\Teacher\Reporting',
                __NAMESPACE__.'\Frontend::frontendTeacherMinimumGradeCountReporting')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\Gradebook\MinimumGradeCount\Headmaster\Reporting',
                __NAMESPACE__.'\Frontend::frontendHeadmasterMinimumGradeCountReporting')
        );
    }

    /**
     * @deprecated
     *
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Education', 'Graduation', 'Gradebook', null,
            Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
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
}
