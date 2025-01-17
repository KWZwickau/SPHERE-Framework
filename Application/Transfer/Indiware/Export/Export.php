<?php

namespace SPHERE\Application\Transfer\Indiware\Export;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Transfer\Indiware\Export\Meta\Frontend;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
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
 * Class Export
 * @package SPHERE\Application\Transfer\Indiware\Export
 */
class Export implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Daten exportieren'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     */
    public static function useService()
    {
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        return (new Frontend());
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Indiware', 'Datentransfer');

        $Stage->setMessage('Daten exportieren');

        $PanelAppointmentGradeExport[] = new PullClear('Stichtagsnoten exportieren: '.
            new Center(new Standard('', __NAMESPACE__.'/AppointmentGrade/Prepare', new Upload()
                , array(), 'Export')));

        $PanelMetaExport[] = new PullClear('Grunddaten für eine Klasse exportieren: '.
            new Center(new Standard('', __NAMESPACE__.'/Meta', new Listing()
                , array(), 'Auswahl der Klasse')));

        $Stage->setMessage('Exportvorbereitung / Daten exportieren');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Indiware-Notenexport für SEK II', $PanelAppointmentGradeExport
                                , Panel::PANEL_TYPE_INFO)
                            , 4),
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