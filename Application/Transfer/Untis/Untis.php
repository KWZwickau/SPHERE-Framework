<?php
namespace SPHERE\Application\Transfer\Untis;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Transfer\Untis\Export\Export;
use SPHERE\Application\Transfer\Untis\Export\Meta\Meta;
use SPHERE\Application\Transfer\Untis\Import\Import;
use SPHERE\Application\Transfer\Untis\Import\Lectureship as ImportLectureship;
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
        Meta::registerModule();
        Export::registerModule();
//        ExportLectureship::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Untis'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));


//        Main::getDispatcher()->registerWidget('Untis', array(__CLASS__, 'widgetLectureship'), 2, 2);
    }

//    /**
//     * @return Thumbnail
//     */
//    public static function widgetLectureship()
//    {
//
//        return new Thumbnail(
//            FileSystem::getFileLoader('/Common/Style/Resource/logo_kreide2.png'),
//            'Offener Untis', 'Lehraufträge-Import',
//            new Standard('', '/Transfer/Untis/Import/Lectureship/Show', new Edit(), array(), 'Bearbeiten')
//            .new Standard('', '/Transfer/Untis/Import/Lectureship/Destroy', new Remove(), array(), 'Löschen')
//        );
//    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Untis', 'Datentransfer');

        return $Stage;
    }
}