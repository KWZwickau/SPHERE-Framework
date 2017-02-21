<?php
namespace SPHERE\Application\Transfer\Untis;

use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Transfer\Untis\Export\Lectureship as ExportLectureship;
use SPHERE\Application\Transfer\Untis\Import\Import;
use SPHERE\Application\Transfer\Untis\Import\Lectureship as ImportLectureship;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Thumbnail;
use SPHERE\Common\Frontend\Link\Repository\Standard;
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
        ImportLectureship::registerModule();
        ExportLectureship::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Untis'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));

        // load if Lectureship exist (by Account)
        $tblUntisImportLectureship = Import::useService()->getUntisImportLectureshipByAccount();
        if ($tblUntisImportLectureship) {
            Main::getDispatcher()->registerWidget('Untis', array(__CLASS__, 'widgetLectureship'), 2, 2);
        }
    }

    /**
     * @return Thumbnail
     */
    public static function widgetLectureship()
    {

        return new Thumbnail(
            FileSystem::getFileLoader('/Common/Style/Resource/logo_kreide2.png'),
            'Offener Untis', 'Lehraufträge-Import',
            new Standard('', '/Transfer/Untis/Import/Lectureship/Show', new Edit(), array(), 'Bearbeiten')
            .new Standard('', '/Transfer/Untis/Import/Lectureship/Destroy', new Remove(), array(), 'Löschen')
        );
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Untis', 'Datentransfer');

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Untis'));

        return $Stage;
    }
}