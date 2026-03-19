<?php

namespace SPHERE\Application\Transfer\Indiware;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Transfer\Indiware\ErrorLog\ErrorLog;
use SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade\AppointmentGrade;
use SPHERE\Application\Transfer\Indiware\Export\Export;
use SPHERE\Application\Transfer\Indiware\Export\Meta\Meta;
use SPHERE\Application\Transfer\Indiware\Import\Import;
use SPHERE\Application\Transfer\Indiware\Import\Lectureship\Lectureship as ImportLectureshipIndiware;
use SPHERE\Application\Transfer\Indiware\Import\Replacement\Replacement as ImportReplacement;
use SPHERE\Application\Transfer\Indiware\Import\StudentCourse\StudentCourse;
use SPHERE\Application\Transfer\Indiware\Import\Timetable\Timetable as ImportTimetable;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Indiware
 * @package SPHERE\Application\Transfer\Indiware
 */
class Indiware implements IApplicationInterface
{
    public static function registerApplication()
    {

        Import::registerModule();
//        ImportLectureship::registerModule();
        ImportLectureshipIndiware::registerModule();
//        ImportStudentCourse::registerModule();
        StudentCourse::registerModule();
        ImportTimetable::registerModule();
        ImportReplacement::registerModule();
        Export::registerModule();
        AppointmentGrade::registerModule();
        Meta::registerModule();
//        ExportLectureship::registerModule();
        ErrorLog::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Indiware'))
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

        $Stage = new Stage('Indiware', 'Datentransfer');
        $Stage->setContent(
            new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    new \SPHERE\Common\Frontend\Link\Repository\Link(
                        new Thumbnail(FileSystem::getFileLoader('/Common/Style/Resource/SSWImport.png')
                        , 'Indiware Import', 'Leitfaden zur Informationsbeschaffung')
                        , '/Api/Document/Standard/Manual/Create/Pdf', null, array('Select' => 'Indiware'))
                , 2),
            ))))
        );

        return $Stage;
    }
}