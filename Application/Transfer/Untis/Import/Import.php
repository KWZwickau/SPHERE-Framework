<?php
namespace SPHERE\Application\Transfer\Untis\Import;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Transfer\Education\Education;
use SPHERE\Application\Transfer\Education\Service\Entity\TblImport;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\Ruler;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Lectureship
 *
 * @package SPHERE\Application\Transfer\Untis\Import
 */
class Import extends Extension implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Daten importieren'))
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
     */
    public static function useFrontend()
    {

    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {
        $Stage = new Stage('Untis', 'Datentransfer');

        $tblAccount = Account::useService()->getAccountBySession();

        $PanelStudentCourseImport[] = new PullClear('<span style="color: black!important">Schüler-Kurse SEK II importieren: </span>'.
            new Center(new Standard('', '/Transfer/Untis/Import/StudentCourse/Prepare', new Upload()
                , array(), 'Hochladen, danach bearbeiten')));
        if ($tblAccount
            && ($tblImport = Education::useService()->getImportByAccountAndExternSoftwareNameAndTypeIdentifier(
                $tblAccount, TblImport::EXTERN_SOFTWARE_NAME_UNTIS, TblImport::TYPE_IDENTIFIER_STUDENT_COURSE
            ))
        ) {
            $PanelStudentCourseImport[] = '<span style="color: black!important">Vorhandenen Schüler-Kurse der SEK II bearbeiten: </span>'.
                new Center(
                    new Standard('', '/Transfer/Untis/Import/StudentCourse/Show', new Edit(), array('ImportId' => $tblImport->getId()), 'Bearbeiten')
                    . new Standard('', '/Transfer/Untis/Import/StudentCourse/Destroy', new Remove(), array('ImportId' => $tblImport->getId()), 'Löschen')
                );
        }

        $PanelLectureshipImport[] = new PullClear('Lehraufträge importieren: '.
            new Center(new Standard('', '/Transfer/Untis/Import/Lectureship/Prepare', new Upload(), array(), 'Hochladen, danach bearbeiten')));
        if ($tblAccount
            && ($tblImport = Education::useService()->getImportByAccountAndExternSoftwareNameAndTypeIdentifier(
                $tblAccount, TblImport::EXTERN_SOFTWARE_NAME_UNTIS, TblImport::TYPE_IDENTIFIER_LECTURESHIP
            ))
        ) {
            $PanelLectureshipImport[] = '<span style="color: black!important">Vorhandenen Import der Lehraufträge bearbeiten: </span>'
                . $tblImport->getFileName()
                . new Center(
                    new Standard('', '/Transfer/Untis/Import/Lectureship/Show', new Edit(), array('ImportId' => $tblImport->getId()), 'Bearbeiten')
                    . new Standard('', '/Transfer/Untis/Import/Lectureship/Destroy', new Remove(), array('ImportId' => $tblImport->getId()), 'Löschen')
                );
        }

        $PanelTimetable[] = new PullClear('Stundenplan aus Untis: '.
            new Center(new Standard('', '/Transfer/Untis/Import/Timetable', new Upload())));
        $PanelTimetableReplacement[] = new PullClear('Vertretungsplan aus Untis: '.
            new Center(new Standard('', '/Transfer/Untis/Import/Replacement', new Upload())));

        $Stage->setMessage('Importvorbereitung / Daten importieren');

        $Stage->setContent(
            new Layout(new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        new Warning(
                            new Container('Bitte beachten Sie die Reihenfolge für den Import:').
                            new Container('1. Untis-Import für Schüler-Kurse SEK II').
                            new Container('2. Untis-Import für Lehraufträge')
                            . new Container('3. Untis-Import für Stundenplan')
                        )
                    )
                ),
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Untis-Import für Schüler-Kurse SEK II:', $PanelStudentCourseImport
                            , Panel::PANEL_TYPE_INFO)
                    , 4),
                    new LayoutColumn(
                        new Panel('Untis-Import für Lehraufträge:', $PanelLectureshipImport
                            , Panel::PANEL_TYPE_INFO)
                    , 4),
                    new LayoutColumn(
                        new Ruler()
                    ),
                    new LayoutColumn(
                        new Panel('Import Stundenplan:', $PanelTimetable
                            , Panel::PANEL_TYPE_INFO)
                    , 4),
                    new LayoutColumn(
                        new Panel('Import Vertretungsplan:', $PanelTimetableReplacement
                            , Panel::PANEL_TYPE_INFO)
                    , 4),
                ))
            )))
        );

        return $Stage;
    }
}