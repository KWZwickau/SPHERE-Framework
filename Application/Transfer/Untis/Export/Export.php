<?php
namespace SPHERE\Application\Transfer\Untis\Export;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Lectureship
 * @package SPHERE\Application\Transfer\Untis\Export
 */
class Export implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Daten exportieren'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__ . '::frontendDashboard'
        ));
    }

    /**
     */
    public static function useService()
    {
    }

    /**
     */
    public static function useFrontend()
    {
    }

    /**
     * @return Stage
     */
    public function frontendDashboard(): Stage
    {

        $Stage = new Stage('Untis', 'Datentransfer');

        $Stage->setMessage('Daten exportieren');

        $PanelMetaExport[] = new PullClear(
            'Grunddaten für einen Kurs exportieren: '.
            new Center(
                new Standard('', __NAMESPACE__.'/Meta', new Listing(), array(), 'Auswahl des Kurses')
            )
        );

        $Stage->setMessage('Exportvorbereitung / Daten exportieren');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Export der Schüler-Grunddaten', $PanelMetaExport
                                , Panel::PANEL_TYPE_INFO)
                            , 4),
                    ))
                )
            )
        );

        return $Stage;
    }
}