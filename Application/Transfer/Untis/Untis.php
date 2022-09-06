<?php
namespace SPHERE\Application\Transfer\Untis;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Transfer\Untis\Export\Export;
use SPHERE\Application\Transfer\Untis\Export\Meta\Meta;
use SPHERE\Application\Transfer\Untis\Import\Import;
use SPHERE\Application\Transfer\Untis\Import\Lectureship as ImportLectureship;
use SPHERE\Application\Transfer\Untis\Import\Replacement as ImportReplacement;
use SPHERE\Application\Transfer\Untis\Import\StudentCourse as ImportStudentCourse;
use SPHERE\Application\Transfer\Untis\Import\Timetable as ImportTimetable;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Untis
 * @package SPHERE\Application\Transfer\Untis
 */
class Untis implements IApplicationInterface
{
    public static function registerApplication()
    {

        Import::registerModule();
        ImportLectureship::registerModule();
        ImportStudentCourse::registerModule();
        ImportTimetable::registerModule();
        ImportReplacement::registerModule();
        Meta::registerModule();
        Export::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Untis'))
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

        $Stage = new Stage('Untis', 'Datentransfer');

        return $Stage;
    }
}