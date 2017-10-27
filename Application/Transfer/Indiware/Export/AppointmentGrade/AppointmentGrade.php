<?php

namespace SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade;

use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Graduation
 * @package SPHERE\Application\Transfer\Export\Graduation
 */
class AppointmentGrade extends Extension implements IFrontendInterface
{

    public static function registerModule()
    {
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __NAMESPACE__.'\Frontend::frontendExport'
            )
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__.'/Process', __CLASS__.'::frontendAppointmentGradeUpload'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__.'/Prepare', __CLASS__.'::frontendAppointmentGradePrepare'
        ));
    }

    public function frontendAppointmentGradePrepare()
    {
        $Stage = new Stage('Indiware', 'Datentransfer');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import', new ChevronLeft()));

        $Stage->setMessage('Importvorbereitung / Daten importieren');
//        $tblYearAll = Term::useService()->getYearAll();
        // get short list of years (year must have periods)
        $tblYearAll = Term::useService()->getYearAllSinceYears(1);
        if (!$tblYearAll) {
            $tblYearAll = array();
        }


        $LevelList = array(
            0 => '',
            1 => 'Stufe 11 - 1.Halbjahr',
            2 => 'Stufe 11 - 2.Halbjahr',
            3 => 'Stufe 12 - 1.Halbjahr',
            4 => 'Stufe 12 - 2.Halbjahr'
        );

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            new Form(
                                new FormGroup(array(
                                    new FormRow(
                                        new FormColumn(
                                            new Panel('Import',
                                                array(
                                                    (new SelectBox('tblYear',
                                                        'Schuljahr auswählen '.new ToolTip(new Info(),
                                                            'Für welches Schuljahr soll der Import benutzt werden?'),
                                                        array(
                                                            '{{ Year }} {{ Description }}' => $tblYearAll
                                                        )))->setRequired(),
                                                    (new SelectBox('Level',
                                                        'Importauswahl '.new ToolTip(new Info(),
                                                            'Weche Werte sollen importiert werden'),
                                                        $LevelList
                                                    ))->setRequired(),
                                                    (new FileUpload('File', 'Datei auswählen', 'Datei auswählen '
                                                        .new ToolTip(new Info(), 'Schueler.csv'), null,
                                                        array('showPreview' => false)))->setRequired()
                                                ), Panel::PANEL_TYPE_INFO)
                                        )
                                    ),
                                )),
                                new Primary('Hochladen und Voransicht', new Upload()),
                                new Route(__NAMESPACE__.'/AppointmentGrade')
                            )
                        ), 6)
                    )
                ), new Title('Schülerkurse', 'importieren'))
            )
        );

        return $Stage;
    }

    /**
     * @param null|UploadedFile $File
     * @param null|int          $tblYear
     * @param null              $Level
     *
     * @return Stage|string
     */
    public function frontendAppointmentGradeUpload(
        UploadedFile $File = null,
        $tblYear = null,
        $Level = null
    ) {

        $Stage = new Stage('Indiware', 'Daten Export');
        $Stage->setMessage('Schüler-Kurse SEK II  importieren');

        if ($File === null || $tblYear === null || $tblYear <= 0) {
            $Stage->setContent(
                ($tblYear <= 0
                    ? new Warning('Bitte geben Sie das Schuljahr an.')
                    : new Warning('Bitte geben sie die Datei an.'))
                .new Redirect(new Route(__NAMESPACE__.'/AppointmentGrade/Prepare'), Redirect::TIMEOUT_ERROR, array(
                    'tblYear' => $tblYear,
                    'Level'   => $Level
                ))
            );
            return $Stage;
        }
        if ($Level == 0) {
            $Stage->setContent(
                new Warning('Bitte geben Sie den zu importierenden Abschnitt "Importauswahl" an')
                .new Redirect(new Route(__NAMESPACE__.'/AppointmentGrade/Prepare'), Redirect::TIMEOUT_ERROR, array(
                    'tblYear' => $tblYear,
                    'Level'   => $Level
                ))
            );
            return $Stage;
        }

        $tblYear = Term::useService()->getYearById($tblYear);
        if (!$tblYear) {

            $Stage->setContent(
                new Warning('Bitte geben Sie ein gültiges Schuljahr an.')
                .new Redirect(new Route(__NAMESPACE__.'/AppointmentGrade/Prepare'), Redirect::TIMEOUT_ERROR, array(
                    'tblYear' => $tblYear
                ))
            );
            return $Stage;
        }

        if ($File && !$File->getError()
            && (strtolower($File->getClientOriginalExtension()) == 'txt'
                || strtolower($File->getClientOriginalExtension()) == 'csv')
        ) {

//            // remove existing import
//            Import::useService()->destroyIndiwareImportStudentAll();

            // match File
            $Extension = (strtolower($File->getClientOriginalExtension()) == 'txt'
                ? 'csv'
                : strtolower($File->getClientOriginalExtension())
            );

            $Payload = new FilePointer($Extension);
            $Payload->setFileContent(file_get_contents($File->getRealPath()));
            $Payload->saveFile();

//            // Test
//            $Control = new AppointmentGradeControl($Payload->getRealPath());
//            Debugger::screenDump($Control->getCompare());
//            if (!$Control->getCompare()) {
//
//                $LayoutColumnList = array();
//                $LayoutColumnList[] = new LayoutColumn(new Warning('Die Datei beinhaltet nicht alle benötigten Spalten'));
//                $DifferenceList = $Control->getDifferenceList();
//                if (!empty($DifferenceList)) {
//
//                    foreach ($DifferenceList as $Column => $Value) {
//                        $LayoutColumnList[] = new LayoutColumn(new Panel('Fehlende Spalte', $Value,
//                            Panel::PANEL_TYPE_DANGER), 3);
//                    }
//                }
//
//                $Stage->addButton(new Standard('Zurück', __NAMESPACE__.'/Prepare', new ChevronLeft(),
//                    array(
//                        'tblYear' => $tblYear
//                    )));
//                $Stage->setContent(
//                    new Layout(
//                        new LayoutGroup(
//                            new LayoutRow(
//                                $LayoutColumnList
//                            )
//                        )
//                    )
//                );
//                return $Stage;
//            }

//            return $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Success('Datei Stimmt'));

//            // add import
//            $Gateway = new AppointmentGradeGateway($Payload->getRealPath(), $tblYear, $Level, $Control);
//
//            $ImportList = $Gateway->getImportList();
//            $tblAccount = Account::useService()->getAccountBySession();
//
//            $LevelString = false;
//            if ($Level == 1 || $Level == 2) {
//                $LevelString = '11';
//            } elseif ($Level == 3 || $Level == 4) {
//                $LevelString = '12';
//            }
//
//            if ($ImportList && $tblYear && $tblAccount && $LevelString) {
//                Import::useService()->createIndiwareImportStudentByImportList($ImportList, $tblYear, $tblAccount,
//                    $LevelString);
//            }

        } else {
            return $Stage->setContent(new Warning('Ungültige Dateiendung!'))
                .new Redirect('/Transfer/Indiware/Import', Redirect::TIMEOUT_ERROR);
        }

        return 'Bis hier her.';
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service();
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}
