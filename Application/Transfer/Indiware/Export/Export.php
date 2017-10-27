<?php

namespace SPHERE\Application\Transfer\Indiware\Export;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade\AppointmentGrade;
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
        AppointmentGrade::registerModule();

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Daten exportieren'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Indiware', 'Datentransfer');

        $Stage->setMessage('Daten exportieren');

        $PanelLectureshipImport[] = new PullClear('Stichtagsnoten exportieren '.
            new Center(new Standard('', __NAMESPACE__.'/AppointmentGrade', new Upload()
                , array(), 'Stichtagsnoten Herunterladen')));
//        $PanelLectureshipImport[] = new PullClear('Lehraufträge importieren: '.
//            new Center(new Standard('', __NAMESPACE__.'/AppointmentGrade/Prepare', new Upload()
//                , array(), 'Hochladen, danach bearbeiten')));

        $Stage->setMessage('Importvorbereitung / Daten importieren');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Indiware-Export für Stichtagsnoten:', $PanelLectureshipImport
                                , Panel::PANEL_TYPE_INFO)
                            , 4),
                    ))
                )
            )
        );

        return $Stage;
    }
}