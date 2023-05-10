<?php
namespace SPHERE\Application\Education\Lesson;

use SPHERE\Application\Education\Lesson\Course\Course;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Lesson
 *
 * @package SPHERE\Application\Education\Lesson
 */
class Lesson implements IApplicationInterface
{

    public static function registerApplication()
    {

        Subject::registerModule();
        Term::registerModule();
        Division::registerModule();
        if (School::useService()->hasConsumerTechnicalSchool()){
            Course::registerModule();
        }

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Unterricht'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {
        $Stage = new Stage('Dashboard', 'Unterricht');

        return $Stage;
    }
}
