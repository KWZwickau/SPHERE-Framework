<?php
namespace SPHERE\Application\Transfer\ItsLearning;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Transfer\ItsLearning\Export\Export;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class ItsLearning
 * @package SPHERE\Application\Transfer\ItsLearning
 */
class ItsLearning implements IApplicationInterface
{
    public static function registerApplication()
    {

//        Import::registerModule();
        Export::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('ItsLearning'))
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
        $Stage = new Stage('Indiware', 'Datentransfer');
        $Stage->addButton(new Standard('Zurück', '/Transfer', new ChevronLeft()));

        return $Stage;
    }
}