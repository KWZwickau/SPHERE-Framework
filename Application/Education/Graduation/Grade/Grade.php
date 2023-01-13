<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Book;
use SPHERE\Common\Frontend\Icon\Repository\ClipBoard;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
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
            new Link(new Link\Route(__NAMESPACE__.'\ScoreRule'), new Link\Name('Berechnungsvorschrift'),
                new Link\Icon(new Pencil()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'\ScoreType'), new Link\Name('Bewertungssystem'),
                new Link\Icon(new Quantity()))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'\MinimumGradeCount'), new Link\Name('Mindestnotenanzahl'),
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

        // Berechnungsvorschrift
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule', __NAMESPACE__.'\Frontend::frontendScoreRule')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Edit', __NAMESPACE__.'\Frontend::frontendEditScoreRule')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Destroy', __NAMESPACE__.'\Frontend::frontendDestroyScoreRule')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Activate', __NAMESPACE__.'\Frontend::frontendActivateScoreRule')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Condition\Select', __NAMESPACE__.'\Frontend::frontendScoreRuleConditionSelect')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Condition\Add', __NAMESPACE__.'\Frontend::frontendScoreRuleConditionAdd')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Condition\Remove', __NAMESPACE__.'\Frontend::frontendScoreRuleConditionRemove')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Subject', __NAMESPACE__.'\Frontend::frontendScoreRuleSubject')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\SubjectDivisionCourse', __NAMESPACE__.'\Frontend::frontendScoreRuleSubjectDivisionCourse')
        );
        // Berechnungsvorschrift - Berechnungsvariante
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Condition', __NAMESPACE__.'\Frontend::frontendScoreCondition')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Condition\Edit', __NAMESPACE__.'\Frontend::frontendEditScoreCondition')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Condition\Destroy', __NAMESPACE__.'\Frontend::frontendDestroyScoreCondition')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Condition\Activate', __NAMESPACE__.'\Frontend::frontendActivateScoreCondition')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Condition\Group\Select', __NAMESPACE__.'\Frontend::frontendScoreGroupSelect')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Condition\Group\Add', __NAMESPACE__.'\Frontend::frontendScoreGroupAdd')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Condition\Group\Remove', __NAMESPACE__.'\Frontend::frontendScoreGroupRemove')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Condition\GradeType\Select', __NAMESPACE__.'\Frontend::frontendScoreConditionGradeTypeSelect')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Condition\GradeType\Add', __NAMESPACE__.'\Frontend::frontendScoreConditionGradeTypeAdd')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Condition\GradeType\Remove', __NAMESPACE__.'\Frontend::frontendScoreConditionGradeTypeRemove')
        );
        // Berechnungsvorschrift - Zensuren-Gruppe
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Group', __NAMESPACE__.'\Frontend::frontendScoreGroup')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Group\Edit', __NAMESPACE__.'\Frontend::frontendEditScoreGroup')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Group\Destroy', __NAMESPACE__.'\Frontend::frontendDestroyScoreGroup')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Group\Activate', __NAMESPACE__.'\Frontend::frontendActivateScoreGroup')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Group\GradeType\Select', __NAMESPACE__.'\Frontend::frontendScoreGroupGradeTypeSelect')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Group\GradeType\Add', __NAMESPACE__.'\Frontend::frontendScoreGroupGradeTypeAdd')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreRule\Group\GradeType\Remove', __NAMESPACE__.'\Frontend::frontendScoreGroupGradeTypeRemove')
        );

        // Bewertungssystem
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreType', __NAMESPACE__.'\Frontend::frontendScoreType')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'\ScoreType\Subject', __NAMESPACE__.'\Frontend::frontendScoreTypeSubject')
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

        // Mindestnoten
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\MinimumGradeCount', __NAMESPACE__ . '\Frontend::frontendMinimumGradeCount')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\MinimumGradeCount\Edit', __NAMESPACE__ . '\Frontend::frontendEditMinimumGradeCount')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ . '\MinimumGradeCount\Destroy', __NAMESPACE__ . '\Frontend::frontendDestroyMinimumGradeCount')
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