<?php
namespace SPHERE\Application\Transfer\Untis\Import;

use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Lectureship
 * @package SPHERE\Application\Transfer\Untis\Import
 */
class Lectureship extends Import
{


    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Lectureship', __CLASS__.'::frontendUpload'
        ));

        parent::registerModule();
    }

    /**
     * @param null|UploadedFile $File
     * @param null|int          $tblYear
     *
     * @return Stage|string
     */
    public function frontendUpload(UploadedFile $File = null, $tblYear = null)
    {

        $Stage = new Stage('Untis', 'Daten importieren');
        $Stage->setMessage('Lehraufträge importieren');

        if ($File === null || $tblYear === null || $tblYear <= 0) {
            // TODO: Form Error
            $Stage->setContent(
                ( $tblYear <= 0
                    ? new WarningMessage('Bitte geben Sie das Schuljahr sowie eine Datei an.')
                    : new WarningMessage('Bitte geben sie die Datei an.') ).
                new Redirect(new Route(__NAMESPACE__), Redirect::TIMEOUT_ERROR, array(
                    'tblYear' => $tblYear
                ))
            );
            return $Stage;
        }
        $tblYear = Term::useService()->getYearById($tblYear);
        if (!$tblYear) {
            // TODO: Form Error
            $Stage->setContent(
                new Redirect(new Route(__NAMESPACE__), Redirect::TIMEOUT_ERROR, array(
                    'tblYear' => $tblYear
                ))
            );
            return $Stage;
        }

        if ($File && !$File->getError()
            && ( $File->getClientOriginalExtension() == 'txt'
                || $File->getClientOriginalExtension() == 'csv' )
        ) {

            // remove existing import
            Import::useService()->destroyUntisImportLectureship();

            // match File
            $Extension = ( $File->getClientOriginalExtension() == 'txt'
                ? 'csv'
                : $File->getClientOriginalExtension()
            );

            $Payload = new FilePointer($Extension);
            $Payload->setFileContent(file_get_contents($File->getRealPath()));
            $Payload->saveFile();

            // add import
            $Gateway = new LectureshipGateway($Payload->getRealPath(), $tblYear);

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new TableData($Gateway->getResultList(), null,
                                    array('FileDivision'     => 'Datei: Klasse',
                                          'AppDivision'      => 'Software: Klasse',
                                          'FileTeacher'      => 'Datei: Lehrer',
                                          'AppTeacher'       => 'Software: Lehrer',
                                          'FileSubject'      => 'Datei: Fachkürzel',
                                          'AppSubject'       => 'Software: Fach',
                                          'FileSubjectGroup' => 'Datei: Gruppe',
                                          'AppSubjectGroup'  => 'Software: Gruppe'
                                    ),
                                    array('order'      => array(array(0, 'desc')),
                                          'columnDefs' => array(
                                              array('type' => 'natural', 'targets' => 0),
                                          )
                                    )
                                )
                            ),
                            new LayoutColumn(
                                new Standard('Weiter', '/Transfer/Untis/Import/Lectureship/Show', new ChevronRight())
                            )
                        ))
                        , new Title('Validierung', new Danger('Rote Einträge werden nicht Importiert!')))
                )
            );
        } else {
            return $Stage->setContent(new WarningMessage('Ungültige Dateiendung!'))
                .new Redirect('/Transfer/Untis/Import', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }
}