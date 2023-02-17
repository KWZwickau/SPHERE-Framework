<?php
namespace SPHERE\Application\Education\Graduation;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Graduation
 *
 * @package SPHERE\Application\Education\Graduation
 */
class Graduation implements IApplicationInterface
{

    public static function registerApplication()
    {

//        Gradebook::registerModule();
//        Evaluation::registerModule();
        Grade::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Zensuren'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__ . '::frontendDashboard'
        ));

    }

    /**
     * @return Stage
     */
    public function frontendDashboard(): Stage
    {

        $Stage = new Stage('Dashboard', 'Zensuren');

        return $Stage;

//        $Stage->setContent(Main::getDispatcher()->fetchDashboard('Grade'));
//        $TableContent = array();
//        $Item = array();
//        if (Access::useService()->hasAuthorization('/Education/Graduation/Grade/GradeType')) {
//            $Item['Modul'] = new Bold('Zensuren-Typ');
//            $Item['Description'] = 'Verwaltung der Zensuren-Typen (Kopfnoten, Leistungsüberprüfung).';
//            array_push($TableContent, $Item);
//        }
//        if (Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Score')) {
//            $Item['Modul'] = new Bold('Berechnungsvorschrift');
//            $Item['Description'] = 'Verwaltung der Berechnungsvorschriften für die automatische Durchschnittsberechnung der Zensuren.';
//            array_push($TableContent, $Item);
//        }
//        if (Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Type')) {
//            $Item['Modul'] = new Bold('Bewertungssystem');
//            $Item['Description'] = 'Hier werden alle verfügbaren Bewertungssysteme angezeigt.
//            Nach der Auswahl eines Bewertungssystems können dem Bewertungssystem die entsprechenden Fach-Klassen zugeordnet werden.';
//            array_push($TableContent, $Item);
//        }
//        if (Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Gradebook/Teacher')) {
//            $Item['Modul'] = new Bold('Notenbuch (Ansicht: Lehrer)');
//            $Item['Description'] = 'Anzeige der Notenbücher, wo der angemeldete Lehrer als Fachlehrer oder Klassenlehrer hinterlegt ist.';
//            array_push($TableContent, $Item);
//        }
//        if (Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Gradebook/Headmaster')) {
//            $Item['Modul'] = new Bold('Notenbuch (Ansicht: Leitung)');
//            $Item['Description'] = 'Anzeige aller Notenbücher.';
//            array_push($TableContent, $Item);
//        }
////        if (Access::useService()->hasAuthorization('/Education/Graduation/Gradebook/Student/Gradebook')) {
////            $Item['Modul'] = new Bold('Notenübersicht');
////            $Item['Description'] = new Container('Anzeige der Zensuren für die Schüler und Eltern.')
////                .new Container('Der angemeldete Schüler sieht nur seine eigenen Zensuren')
////                .new COntainer('Der angemeldete Sorgeberechtigte sieht nur die Zensuren seiner Kinder.');
////            array_push($TableContent, $Item);
////        }
//        if (Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Test/Teacher')) {
//            $Item['Modul'] = new Bold('Leistungsüberprüfung (Ansicht: Lehrer)');
//            $Item['Description'] = 'Verwaltung der Leistungsüberprüfungen (inklusive Kopfnoten und Stichtagsnoten)
//                        , wo der angemeldete Lehrer als Fachlehrer hinterlegt ist.';
//            array_push($TableContent, $Item);
//        }
//        if (Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Test/Headmaster')) {
//            $Item['Modul'] = new Bold('Leistungsüberprüfung (Ansicht: Leitung)');
//            $Item['Description'] = 'Verwaltung aller Leistungsüberprüfungen (inklusive Kopfnoten und Stichtagsnoten).';
//            array_push($TableContent, $Item);
//        }
//        if (Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Task/Teacher')) {
//            $Item['Modul'] = new Bold('Notenaufträge (Ansicht: Lehrer)');
//            $Item['Description'] = 'Anzeige der Kopfnoten- und Stichtagsnotenaufträge (inklusive vergebenen Zensuren),
//                         wo der angemeldete Lehrer als Klassenlehrer hinterlegt ist.';
//            array_push($TableContent, $Item);
//        }
//        if (Access::useService()->hasAuthorization('/Education/Graduation/Evaluation/Task/Headmaster')) {
//            $Item['Modul'] = new Bold('Notenaufträge (Ansicht: Leitung)');
//            $Item['Description'] = 'Verwaltung aller Kopfnoten- und Stichtagsnotenaufträge (inklusive der Anzeige der vergebener Zensuren).';
//            array_push($TableContent, $Item);
//        }
//
//        $Stage->setContent(
//            new Layout(
//                new LayoutGroup(
//                    new LayoutRow(
//                        new LayoutColumn(
//                            new TableData($TableContent, null, array(
//                                'Modul'       => 'Modul',
//                                'Description' => 'Beschreibung'
//                            ), array(
//                                "paging"         => false, // Deaktivieren Blättern
//                                "iDisplayLength" => -1,    // Alle Einträge zeigen
//                                "searching"      => false, // Deaktivieren Suchen
//                                "info"           => false, // Deaktivieren Such-Info
//                                "sort"           => false  // Deaktivieren der Sortierung
//                            ))
//                        )
//                    )
//                )
//            )
//        );
//
//        return $Stage;
    }
}
