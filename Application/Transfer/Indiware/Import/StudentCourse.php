<?php

namespace SPHERE\Application\Transfer\Indiware\Import;

use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Transfer\Indiware\Import\Service\Entity\TblIndiwareImportStudent;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title as TitleForm;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Listing as ListingIcon;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title as TitleLayout;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger as DangerMessage;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Underline;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class StudentCourse
 * @package SPHERE\Application\Transfer\Indiware\Import
 */
class StudentCourse extends Extension implements IFrontendInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__, __CLASS__.'::frontendStudentCourseUpload'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__.'/Prepare', __CLASS__.'::frontendStudentCoursePrepare'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__.'/Show', __CLASS__.'::frontendStudentCourseShow'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__.'/Edit', __CLASS__.'::frontendStudentCourseEdit'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__.'/DivisionRefresh', __CLASS__.'::frontendActiveDivision'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__.'/Destroy', __CLASS__.'::frontendStudentCourseDestroy'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__.'/Ignore', __CLASS__.'::frontendIgnoreImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__.'/Import', __CLASS__.'::frontendImportStudentCourse'
        ));

//        parent::registerModule();
    }

    public function frontendStudentCoursePrepare()
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

//        // try to POST tblYear if YearByNow exist
//        $tblYearList = Term::useService()->getYearByNow();
//        if ($tblYearList) {
//            $tblYear = false;
//            // last Entity should be the first created year
//            foreach ($tblYearList as $tblYearEntity) {
//                $tblYear = $tblYearEntity;
//            }
//            if ($tblYear) {
//                $Global = $this->getGlobal();
//                $Global->POST['tblYear'] = $tblYear->getId();
//                $Global->savePost();
//            }
//        }

        $tblIndiwareImportStudentList = Import::useService()->getIndiwareImportStudentAll(true);


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
                        new LayoutColumn(
                            ($tblIndiwareImportStudentList ? new WarningMessage(new WarningIcon().' Vorsicht vorhandene Importdaten werden entfernt!') : '')
                            , 6, array(LayoutColumn::GRID_OPTION_HIDDEN_SM)
                        )
                    ),
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            new Form(
                                new FormGroup(array(
                                    new FormRow(
                                        new FormColumn(
                                            new Panel('Import',
                                                array(
                                                    (new SelectBox('tblYear',
                                                        'Schuljahr auswählen '.new ToolTip(new InfoIcon(),
                                                            'Für welches Schuljahr soll der Import benutzt werden?'),
                                                        array(
                                                            '{{ Year }} {{ Description }}' => $tblYearAll
                                                        )))->setRequired(),
                                                    (new SelectBox('Level',
                                                        'Importauswahl '.new ToolTip(new InfoIcon(),
                                                            'Weche Werte sollen importiert werden'),
                                                        $LevelList
                                                    ))->setRequired(),
                                                    (new FileUpload('File', 'Datei auswählen', 'Datei auswählen '
                                                        .new ToolTip(new InfoIcon(), 'Schueler.csv'), null,
                                                        array('showPreview' => false)))->setRequired()
                                                ), Panel::PANEL_TYPE_INFO)
                                        )
                                    ),
                                )),
                                new Primary('Hochladen und Voransicht', new Upload()),
                                new Link\Route(__NAMESPACE__.'/StudentCourse')
                            )
                        ), 6)
                    )
                ), new TitleLayout('Schülerkurse', 'importieren'))
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
    public function frontendStudentCourseUpload(
        UploadedFile $File = null,
        $tblYear = null,
        $Level = null
    ) {

        $Stage = new Stage('Indiware', 'Daten importieren');
        $Stage->setMessage('Schüler-Kurse SEK II  importieren');

        if ($File === null || $tblYear === null || $tblYear <= 0) {
            $Stage->setContent(
                ($tblYear <= 0
                    ? new WarningMessage('Bitte geben Sie das Schuljahr an.')
                    : new WarningMessage('Bitte geben sie die Datei an.'))
                .new Redirect(new Route(__NAMESPACE__.'/StudentCourse/Prepare'), Redirect::TIMEOUT_ERROR, array(
                    'tblYear' => $tblYear,
                    'Level'   => $Level
                ))
            );
            return $Stage;
        }
        if ($Level == 0) {
            $Stage->setContent(
                new WarningMessage('Bitte geben Sie den zu importierenden Abschnitt "Importauswahl" an')
                .new Redirect(new Route(__NAMESPACE__.'/StudentCourse/Prepare'), Redirect::TIMEOUT_ERROR, array(
                    'tblYear' => $tblYear,
                    'Level'   => $Level
                ))
            );
            return $Stage;
        }

        $tblYear = Term::useService()->getYearById($tblYear);
        if (!$tblYear) {

            $Stage->setContent(
                new WarningMessage('Bitte geben Sie ein gültiges Schuljahr an.')
                .new Redirect(new Route(__NAMESPACE__.'/StudentCourse/Prepare'), Redirect::TIMEOUT_ERROR, array(
                    'tblYear' => $tblYear
                ))
            );
            return $Stage;
        }

        if ($File && !$File->getError()
            && (strtolower($File->getClientOriginalExtension()) == 'txt'
                || strtolower($File->getClientOriginalExtension()) == 'csv')
        ) {

            // remove existing import
            Import::useService()->destroyIndiwareImportStudentAll();

            // match File
            $Extension = (strtolower($File->getClientOriginalExtension()) == 'txt'
                ? 'csv'
                : strtolower($File->getClientOriginalExtension())
            );

            $Payload = new FilePointer($Extension);
            $Payload->setFileContent(file_get_contents($File->getRealPath()));
            $Payload->saveFile();

            // Test
            $Control = new StudentCourseControl($Payload->getRealPath());
            if (!$Control->getCompare()) {

                $LayoutColumnList = array();
                $LayoutColumnList[] = new LayoutColumn(new WarningMessage('Die Datei beinhaltet nicht alle benötigten Spalten'));
                $DifferenceList = $Control->getDifferenceList();
                if (!empty($DifferenceList)) {

                    foreach ($DifferenceList as $Column => $Value) {
                        $LayoutColumnList[] = new LayoutColumn(new Panel('Fehlende Spalte', $Value,
                            Panel::PANEL_TYPE_DANGER), 3);
                    }
                }

                $Stage->addButton(new Standard('Zurück', __NAMESPACE__.'/StudentCourse/Prepare', new ChevronLeft(),
                    array(
                        'tblYear' => $tblYear
                    )));
                $Stage->setContent(
                    new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                $LayoutColumnList
                            )
                        )
                    )
                );
                return $Stage;
            }

//            return $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Success('Datei Stimmt'));

            // add import
            $Gateway = new StudentCourseGateway($Payload->getRealPath(), $tblYear, $Level, $Control);

            $ImportList = $Gateway->getImportList();
            $tblAccount = Account::useService()->getAccountBySession();

            $LevelString = false;
            if ($Level == 1 || $Level == 2) {
                $LevelString = '11';
            } elseif ($Level == 3 || $Level == 4) {
                $LevelString = '12';
            }

            if ($ImportList && $tblYear && $tblAccount && $LevelString) {
                Import::useService()->createIndiwareImportStudentByImportList($ImportList, $tblYear, $tblAccount,
                    $LevelString);
            }

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new WarningMessage('Personeneinträge in'.new Danger(new Bold(' "rot" ')).'werden nicht importiert.'
                                    .new Container('Bitte Kontrollieren Sie ggf. die Personen-Daten Vor-,Nachname sowie das Geburtsdatum'))
                                , 4),
                            new LayoutColumn(
                                new TableData($Gateway->getResultList(), null,
                                    array(
                                        'FirstName'     => 'Datei: Vorname',
                                        'LastName'      => 'Datei: Nachname',
                                        'Birthday'      => 'Datei: Geburtsdatum',
                                        'AppPerson'     => 'Person',
                                        'FileSubject1'  => 'Datei: Kurskürzel',
                                        'AppSubject1'   => 'Software: Fach',
                                        'FileSubject2'  => 'Datei: Kurskürzel2',
                                        'AppSubject2'   => 'Software: Fach2',
                                        'FileSubject3'  => 'Datei: Kurskürzel3',
                                        'AppSubject3'   => 'Software: Fach3',
                                        'FileSubject4'  => 'Datei: Kurskürzel4',
                                        'AppSubject4'   => 'Software: Fach4',
                                        'FileSubject5'  => 'Datei: Kurskürzel5',
                                        'AppSubject5'   => 'Software: Fach5',
                                        'FileSubject6'  => 'Datei: Kurskürzel6',
                                        'AppSubject6'   => 'Software: Fach6',
                                        'FileSubject7'  => 'Datei: Kurskürzel7',
                                        'AppSubject7'   => 'Software: Fach7',
                                        'FileSubject8'  => 'Datei: Kurskürzel8',
                                        'AppSubject8'   => 'Software: Fach8',
                                        'FileSubject9'  => 'Datei: Kurskürzel9',
                                        'AppSubject9'   => 'Software: Fach9',
                                        'FileSubject10' => 'Datei: Kurskürzel10',
                                        'AppSubject10'  => 'Software: Fach10',
                                        'FileSubject11' => 'Datei: Kurskürzel11',
                                        'AppSubject11'  => 'Software: Fach11',
                                        'FileSubject12' => 'Datei: Kurskürzel12',
                                        'AppSubject12'  => 'Software: Fach12',
                                        'FileSubject13' => 'Datei: Kurskürzel13',
                                        'AppSubject13'  => 'Software: Fach13',
                                        'FileSubject14' => 'Datei: Kurskürzel14',
                                        'AppSubject14'  => 'Software: Fach14',
                                        'FileSubject15' => 'Datei: Kurskürzel15',
                                        'AppSubject15'  => 'Software: Fach15',
                                        'FileSubject16' => 'Datei: Kurskürzel16',
                                        'AppSubject16'  => 'Software: Fach16',
                                        'FileSubject17' => 'Datei: Kurskürzel17',
                                        'AppSubject17'  => 'Software: Fach17',
                                    ),
                                    array(
                                        'order'      => array(array(0, 'desc')),
                                        'columnDefs' => array(
                                            array('type' => 'natural', 'targets' => 0),
                                        ),
                                        'responsive' => false
                                    )
                                )
                            ),
                            new LayoutColumn(
                                new DangerLink('Abbrechen', '/Transfer/Indiware/Import').
                                new Standard('Weiter', '/Transfer/Indiware/Import/StudentCourse/Show',
                                    new ChevronRight())
                            )
                        ))
                        , new TitleLayout('Validierung'
//                        , 'Rote '.new Danger(new WarningIcon()).' Einträge wurden nicht für die Bearbeitung aufgenommen! '
//                        .new ToolTip(new InfoIcon(), 'Werden Klassen nicht in der Schulsoftware gefunden, kann kein
//                        Lehrauftrag für diese erstellt werden!')
                    ))
                )
            );
        } else {
            return $Stage->setContent(new WarningMessage('Ungültige Dateiendung!'))
                .new Redirect('/Transfer/Indiware/Import', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @param bool $Visible
     *
     * @return Stage
     */
    public function frontendStudentCourseShow($Visible = false)
    {
        $Stage = new Stage('Schüler-Kurse SEK II ', 'Übersicht');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import', new ChevronLeft()));
        $tblIndiwareImportStudentList = Import::useService()->getIndiwareImportStudentAll(true);
        $TableContent = array();
//        $TableCompare = array();
        $tblYear = false;
        $Level = false;
        $IsDisableImport = false;
        if ($tblIndiwareImportStudentList) {
            array_walk($tblIndiwareImportStudentList, function (TblIndiwareImportStudent $tblIndiwareImportStudent)
            use (&$TableContent, &$tblYear, $Visible, &$Level, &$IsDisableImport) {
                $tblPerson = $tblIndiwareImportStudent->getServiceTblPerson();
                $tblYear = $tblIndiwareImportStudent->getServiceTblYear();
                $Level = $tblIndiwareImportStudent->getLevel();
                $tblDivision = $tblIndiwareImportStudent->getServiceTblDivision();
                $Item['Person'] = '';
                $Item['Year'] = '';
                $Item['Division'] = new Center(new Danger(new Remove()));
                for ($i = 1; $i <= 17; $i++) {
                    $Item['SubjectAndGroup'.$i] = '';
                }
                if ($tblPerson) {
                    $Item['Person'] = $tblPerson->getLastFirstName();
                }
                if ($tblYear) {
                    $Item['Year'] = $tblIndiwareImportStudent->getServiceTblYear();
                }
                if ($tblDivision) {
                    // Klassen Farblich Markieren wenn die Stufe nicht zum Import passt
                    if ($tblDivision->getTblLevel() && $tblDivision->getTblLevel()->getName() == $Level) {
                        $Item['Division'] = new Center($tblDivision->getDisplayName());
                    } else {
                        $Item['Division'] = new Danger(new Center(new ToolTip(new InfoIcon(),
                                'Klassenstufe stimmt nicht mit dem gewählten Import überein!')
                            .' '.$tblDivision->getDisplayName()));
                        // Verhindern des Imports wenn Klassenstufen nicht übereinstimmen (Deaktivierte ignorieren)
                        if (!$tblIndiwareImportStudent->getIsIgnore()) {
                            $IsDisableImport = true;
                        }
                    }
                } else {
                    $Item['Division'] = new Danger(new Center(new ToolTip(new InfoIcon(), 'Keine Klasse vorhanden!')));
                    // Verhindern des Imports wenn Klasse nicht mehr existiert (Deaktivierte ignorieren)
                    if (!$tblIndiwareImportStudent->getIsIgnore()) {
                        $IsDisableImport = true;
                    }
                }
                $Item['Option'] = new Standard('', '/Transfer/Indiware/Import/StudentCourse/Edit'
                    , new Edit(), array('Id' => $tblIndiwareImportStudent->getId(), 'Visible' => $Visible),
                        'Importvorbereitung bearbeiten').
                    new Standard('', '/Transfer/Indiware/Import/StudentCourse/DivisionRefresh', new Repeat(),
                        array('Id' => $tblIndiwareImportStudent->getId(), 'Visible' => $Visible),
                        'Aktualisieren der Klasse');
                if ($tblIndiwareImportStudent->getIsIgnore()) {
                    $Item['Option'] .= new Standard('', '/Transfer/Indiware/Import/StudentCourse/Ignore',
                        new SuccessIcon(),
                        array(
                            'Id'      => $tblIndiwareImportStudent->getId(),
                            'Visible' => $Visible
                        ), 'Manuell freigeben');
                    $Item['Ignore'] = new Center(new Warning(new ToolTip(new WarningIcon(), 'Manuell Deaktiviert')));

                } else {
                    $Item['Option'] .= new Standard('', '/Transfer/Indiware/Import/StudentCourse/Ignore',
                        new Remove(),
                        array(
                            'Id'      => $tblIndiwareImportStudent->getId(),
                            'Visible' => $Visible
                        ), 'Manuell sperren');
                    $Item['Ignore'] = new Center(new Success(new SuccessIcon()));
                }

                $IsImportError = false;
                $tblIndiwareImportStudentCourseList = Import::useService()
                    ->getIndiwareImportStudentCourseByIndiwareImportStudent($tblIndiwareImportStudent);
                if ($tblIndiwareImportStudentCourseList) {
                    foreach ($tblIndiwareImportStudentCourseList as $tblIndiwareImportStudentCourse) {
                        $ListContent = array();
                        $CourseNumber = $tblIndiwareImportStudentCourse->getCourseNumber();
                        $SubjectString = '';
                        if (($tblSubject = $tblIndiwareImportStudentCourse->getServiceTblSubject())) {
                            $SubjectString = $tblSubject->getAcronym().' - '.$tblSubject->getName();
                        }
                        if ($tblIndiwareImportStudentCourse->getIsIntensiveCourse()) {
                            $GroupIsIntensiveString = new Muted(new Small(new Bold(' LK')));
                        } else {
                            $GroupIsIntensiveString = new Muted(new Small(' GK'));
                        }
                        $SubjectName = $tblIndiwareImportStudentCourse->getSubjectName();

//                        if ($SubjectName != '' && !$tblIndiwareImportStudentCourse->getServiceTblSubject()) {
//                            $SubjectString = ' Fach nicht gefunden!';
//                        }

                        if ($tblIndiwareImportStudentCourse->getisIgnoreCourse()) {
                            $SubjectString = new Info(new InfoIcon().' Fach/Kurs wird Ignoriert');
                        } elseif ($SubjectName != '' && !$tblIndiwareImportStudentCourse->getServiceTblSubject()) {
                            $SubjectString = new Danger(new InfoIcon().' Fach nicht gefunden!');
                        }
                        $ListContent[] = $SubjectString;
//                            $ListContent[] = $GroupIsIntensiveString;
                        $GroupString = $tblIndiwareImportStudentCourse->getSubjectGroup();
                        $ListContent[] = $GroupString.' - '.$GroupIsIntensiveString;
                        $Item['SubjectAndGroup'.$CourseNumber] = new Listing($ListContent);

                        // Error wenn Fächerzuweisung fehlt
                        if (!$tblSubject && $tblIndiwareImportStudentCourse->getSubjectGroup() != '' && !$tblIndiwareImportStudentCourse->getisIgnoreCourse()) {
                            if (!$tblIndiwareImportStudent->getIsIgnore()) {
                                $Item['Ignore'] = new Center(new Warning(new ToolTip(new Disable(),
                                    'Einige Fächer werden nicht importiert! (Fach nicht gefunden)')));
                            }
                            $IsImportError = true;
                        }
                    }
                }

                if (!$tblDivision) {
                    $Item['Ignore'] = new Center(new Danger(new ToolTip(new Disable(),
                        'Ohne Klasse kann die Person nicht importiert werden')));
                    $IsImportError = true;
                } elseif ($tblDivision->getTblLevel() && $tblDivision->getTblLevel()->getName() != $Level) {
                    $Item['Ignore'] = new Center(new Danger(new ToolTip(new Disable(),
                        'mit der Falschen Klassenstufe kann die Person nicht importiert werden')));
                    $IsImportError = true;
                }
                // Ignore priorisierte Ausgabe
                if ($tblIndiwareImportStudent->getIsIgnore()) {
                    $Item['Ignore'] = new Center(new Warning(new ToolTip(new WarningIcon(), 'Manuell Deaktiviert')));
                    $IsImportError = true;
                }

                if (!$Visible) {
                    array_push($TableContent, $Item);
                } else {
                    if ($Visible == 1) {
                        // only rows they would be import
                        if (!$IsImportError) {
                            array_push($TableContent, $Item);
                        }
                    } elseif ($Visible == 2) {
                        // only rows they need update to import
                        if ($IsImportError) {
                            array_push($TableContent, $Item);
                        }
                    }
                }

//                if (!$IsImportError) {
//                    array_push($TableCompare, $Item);
//                }
            });
        } else {
            $Stage->setContent(new WarningMessage('Leider konnten keine Schüler-Kurs Zuweisungen importiert werden.
            Bitte kontrollieren Sie ihre Datei und das angegebene Schuljahr')
                .new Redirect('/Transfer/Indiware/Import', Redirect::TIMEOUT_WAIT));
            return $Stage;
        }

        $HeaderPanel = '';
        /** @var TblYear $tblYear */
        if ($tblYear) {
            $HeaderPanel = new Panel('Importvorbereitung Stufe '.$Level,
                'für das Schuljahr: '.$tblYear->getDisplayName(),
                Panel::PANEL_TYPE_SUCCESS);
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $HeaderPanel
                        ),
                        new LayoutColumn(
                            new Standard((!$Visible ? new Info(new Bold('Alle')) : 'Alle'),
                                '/Transfer/Indiware/Import/StudentCourse/Show', new ListingIcon(), array(),
                                'Anzeige aller verfügbarer Daten')
                            .new Standard(($Visible == 1 ? new Info(new Bold('Gültige Importe')) : 'Gültige Importe'),
                                '/Transfer/Indiware/Import/StudentCourse/Show', new Ok(), array('Visible' => 1),
                                'Anzeige der aktuell möglichen Importe')
                            .new Standard(($Visible == 2 ? new Info(new Bold('Fehler')) : 'Fehler'),
                                '/Transfer/Indiware/Import/StudentCourse/Show', new EyeOpen(), array('Visible' => 2),
                                'Anzeige aller Fehler')
                        ),
                        new LayoutColumn(
                            new TableData($TableContent, new Title((!$Visible ? 'Alle Datensätze' :
                                ($Visible == 1 ? 'Importfähige Datensätze' : 'korrekturbedürftige Datensätze'))),
                                array(
                                    'Person'            => 'Schüler',
                                    'Ignore'            => 'Importieren',
                                    'Division'          => 'Klasse',
                                    'Option'            => '',
//                                    'FileSubject1'      => 'Datei: Fächerkürzel',
                                    'SubjectAndGroup1'  => new Underline('1. Fach').'<br/>'.'Gruppe',
                                    'SubjectAndGroup2'  => new Underline('2. Fach').'<br/>'.'Gruppe',
                                    'SubjectAndGroup3'  => new Underline('3. Fach').'<br/>'.'Gruppe',
                                    'SubjectAndGroup4'  => new Underline('4. Fach').'<br/>'.'Gruppe',
                                    'SubjectAndGroup5'  => new Underline('5. Fach').'<br/>'.'Gruppe',
                                    'SubjectAndGroup6'  => new Underline('6. Fach').'<br/>'.'Gruppe',
                                    'SubjectAndGroup7'  => new Underline('7. Fach').'<br/>'.'Gruppe',
                                    'SubjectAndGroup8'  => new Underline('8. Fach').'<br/>'.'Gruppe',
                                    'SubjectAndGroup9'  => new Underline('9. Fach').'<br/>'.'Gruppe',
                                    'SubjectAndGroup10' => new Underline('10. Fach').'<br/>'.'Gruppe',
                                    'SubjectAndGroup11' => new Underline('11. Fach').'<br/>'.'Gruppe',
                                    'SubjectAndGroup12' => new Underline('12. Fach').'<br/>'.'Gruppe',
                                    'SubjectAndGroup13' => new Underline('13. Fach').'<br/>'.'Gruppe',
                                    'SubjectAndGroup14' => new Underline('14. Fach').'<br/>'.'Gruppe',
                                    'SubjectAndGroup15' => new Underline('15. Fach').'<br/>'.'Gruppe',
                                    'SubjectAndGroup16' => new Underline('16. Fach').'<br/>'.'Gruppe',
                                    'SubjectAndGroup17' => new Underline('17. Fach').'<br/>'.'Gruppe',
                                ),
                                array(
                                    'order'      => array(array(0, 'asc')),
                                    'columnDefs' => array(
//                                        array('orderable' => false, 'width' => '60px', 'targets' => -1),
//                                        array('orderable' => false, 'width' => '73px', 'targets' => -2),
//                                        array('type' => 'natural', 'targets' => 0),
//                                        array('type' => 'natural', 'targets' => 1)
                                    ),
                                    'responsive' => false
                                ))
                        ),
                        new LayoutColumn(
                            ($IsDisableImport
                                ? new Container((new PrimaryLink('Import der Änderungen',
                                '/Transfer/Indiware/Import/StudentCourse/Import', new Save(),
                                array('YearId' => ($tblYear ? $tblYear->getId() : null)),
                                    'Diese Aktion ist unwiderruflich!'))->setDisabled())
                                : new Container((new PrimaryLink('Import der Änderungen',
                                    '/Transfer/Indiware/Import/StudentCourse/Import', new Save(),
                                    array('YearId' => ($tblYear ? $tblYear->getId() : null)),
                                    'Diese Aktion ist unwiderruflich!')))
                            )
                            , 4)
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param bool $Visible
     *
     * @return Stage
     */
    public function frontendActiveDivision($Id = null, $Visible = false)
    {

        $Stage = new Stage('Setzen der aktuellen Klasse');
        $tblIndiwareImportStudent = Import::useService()->getIndiwareImportStudentById($Id);
        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        (Import::useService()->updateIndiwareImportStudentDivision($tblIndiwareImportStudent)
                            ? new SuccessMessage('Änderung erfolgt, Im gesuchtem Jahr wurde eine Klasse gefunden')
                            .new Redirect('/Transfer/Indiware/Import/StudentCourse/Show', Redirect::TIMEOUT_SUCCESS,
                                array('Visible' => $Visible))
                            : new WarningMessage('Änderung nicht erfolg da keine Klasse im ausgewähltem Jahr gefunden wurde')
                            .new Redirect('/Transfer/Indiware/Import/StudentCourse/Show', Redirect::TIMEOUT_ERROR,
                                array('Visible' => $Visible))
                        )
                    )
                ))
            )
        ));

        return $Stage;
    }

    /**
     * @param null     $Id
     * @param null     $Data
     * @param bool|int $Visible
     *
     * @return Stage
     */
    public function frontendStudentCourseEdit($Id = null, $Data = null, $Visible = false)
    {
        $Stage = new Stage('Lehrauftrag', 'Bearbeiten');
        $tblIndiwareImportStudent = ($Id !== null ? Import::useService()->getIndiwareImportStudentById($Id) : false);
        if (!$tblIndiwareImportStudent) {
            $Stage->setContent(new WarningMessage('Schüler-Zuweisung nicht gefunden.')
                .new Redirect('/Transfer/Indiware/Import/StudentCourse/Show', Redirect::TIMEOUT_ERROR));
            return $Stage;
        }

        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import/StudentCourse/Show', new ChevronLeft(),
            array('Visible' => $Visible)));

        $tblIndiwareImportStudentCourseList = Import::useService()->getIndiwareImportStudentCourseByIndiwareImportStudent(
            $tblIndiwareImportStudent);
        $tblPerson = $tblIndiwareImportStudent->getServiceTblPerson();
        $tblDivision = $tblIndiwareImportStudent->getServiceTblDivision();
        $tblYear = $tblIndiwareImportStudent->getServiceTblYear();

//        $arraySubjectName = array();

        $Global = $this->getGlobal();
//        $Global->POST['Data']['DivisionId'] = ($tblDivision ? $tblDivision->getId() : null);
        if ($tblIndiwareImportStudentCourseList) {
            foreach ($tblIndiwareImportStudentCourseList as $tblIndiwareImportStudentCourse) {
                if (($tblSubject = $tblIndiwareImportStudentCourse->getServiceTblSubject())) {
                    $Number = $tblIndiwareImportStudentCourse->getCourseNumber();
                    $Global->POST['Data']['SubjectId'.$Number] = $tblSubject->getId();
                    $Global->POST['Data']['SubjectGroup'.$Number] = $tblIndiwareImportStudentCourse->getSubjectGroup();
                    if ($tblIndiwareImportStudentCourse->getIsIntensiveCourse()) {
                        $Global->POST['Data']['IsIntensivCourse'.$Number] = 1;
                    }
//                    $arraySubjectName[$Number] = $tblIndiwareImportStudentCourse->getSubjectName();
                } else {
                    $Number = $tblIndiwareImportStudentCourse->getCourseNumber();
                    $Global->POST['Data']['SubjectGroup'.$Number] = $tblIndiwareImportStudentCourse->getSubjectGroup();
                    if ($tblIndiwareImportStudentCourse->getisIgnoreCourse()) {
                        $Global->POST['Data']['IsIgnoreCourse'.$Number] = 1;
                    }
                }
                $Global->savePost();
            }
        }


        $Name = ($tblPerson ? $tblPerson->getFullName() : 'Person nicht gefunden');
        $Division = ($tblDivision ? $tblDivision->getDisplayName() : 'Klasse nicht gefunden');
        $Year = ($tblYear ? $tblYear->getDisplayName() : 'Jahr nicht gefunden');

        $PanelHead = new Panel('Person', array('Name: '.$Name, 'Klasse: '.$Division, 'Jahr: '.$Year),
            Panel::PANEL_TYPE_SUCCESS);


        $Form = $this->formSubjectCourse($tblIndiwareImportStudent, $tblYear);
        $Form->appendFormButton(new Primary('Speichern', new Save()));
        $Form->setConfirm('Die Zuweisung der Personen wurde noch nicht gespeichert.');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            $PanelHead
                            , 6)
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(Import::useService()
                                ->updateIndiwareImportStudentCourse($Form, $tblIndiwareImportStudent, $Data, $Visible
//                                    , $arraySubjectName
                                )
                            )
                        )
                    )
                ))
            )
        );

        return $Stage;
    }

    /**
     * @param TblIndiwareImportStudent $tblIndiwareImportStudent
     * @param TblYear|null             $tblYear
     *
     * @return Form
     */
    public function formSubjectCourse(TblIndiwareImportStudent $tblIndiwareImportStudent, TblYear $tblYear = null)
    {

        $FormSubjectAll = array();
        $tblSubjectList = Subject::useService()->getSubjectAll();
        for ($i = 1; $i <= 17; $i++) {
            $tblIndiwareImportStudentCourse = Import::useService()->getIndiwareImportStudentCourseByIndiwareImportStudentAndNumber(
                $tblIndiwareImportStudent, $i);
            $Status = false;
            if ($tblIndiwareImportStudentCourse) {
                $Subject = 'Fach-Import: '.$tblIndiwareImportStudentCourse->getSubjectName();
                if ($tblIndiwareImportStudentCourse->getSubjectName() && $tblIndiwareImportStudentCourse->getServiceTblSubject()) {
                    $Status = Panel::PANEL_TYPE_INFO;
                }
                if ($tblIndiwareImportStudentCourse->getSubjectName() && !$tblIndiwareImportStudentCourse->getServiceTblSubject()) {
                    $Status = Panel::PANEL_TYPE_DANGER;
                    $Subject .= ' '.new ToolTip(new InfoIcon(),
                            'Bitte wählen Sie das Fach aus welches das Kürzel beschreibt');
                }

            } else {
                $Subject = 'Fach-Import:';
            }

            $FormSubjectAll[] = new FormColumn(
                new Panel($Subject, array(
                    new SelectBox('Data[SubjectId'.$i.']', 'Fach',
                        array('{{ Acronym }} - {{ Name }}' => $tblSubjectList)),
                    new TextField('Data[SubjectGroup'.$i.']', '', 'Kurs Name'),
                    new CheckBox('Data[IsIntensivCourse'.$i.']', 'Leistungskurs', '1'),
                    ($Status == Panel::PANEL_TYPE_DANGER ? new CheckBox('Data[IsIgnoreCourse'.$i.']', 'Fach Ignorieren',
                        '1') : '')
                ), ($Status ? $Status : Panel::PANEL_TYPE_DEFAULT)
                )
                , 2);
        }

        // set frontend to 6 columns in a row
        $FormRowList = array();
        $FormRowCount = 0;
        $FormRow = null;
        /**
         * @var FormColumn $FormSubject
         */
        foreach ($FormSubjectAll as $FormSubject) {
            if ($FormRowCount % 6 == 0) {
                $FormRow = new FormRow(array());
                $FormRowList[] = $FormRow;
            }
            $FormRow->addColumn($FormSubject);
            $FormRowCount++;
        }

        $tblDivisionList = array();
        $tblDivisionResult = Division::useService()->getDivisionAllByYear($tblYear);
        if ($tblDivisionResult) {
            foreach ($tblDivisionResult as $tblDivision) {
                if (($tblLevel = $tblDivision->getTblLevel())) {
                    if ($tblLevel->getName() == 11 || $tblLevel->getName() == 12) {
                        $tblDivisionList[] = $tblDivision;
                    }
                }
            }
        }

        return new Form(array(
                new FormGroup(
                    $FormRowList
//                    new FormRow(
//                    new FormColumn(
//                        new Panel('Klasse', new SelectBox('Data[DivisionId]', 'Klasse des Schülers',
//                            array('{{ DisplayName }} - {{ tblLevel.serviceTblType.Name }}' => $tblDivisionList)),
//                            Panel::PANEL_TYPE_INFO)
//                    ))
                    , new TitleForm(new Edit().' Bearbeiten', 'der Kurse')
                ),
//                new FormGroup(
//                    $FormRowList
////                new FormRow(
////                    $FormSubjectAll
////                )
//                )
            )
        );
    }

    /**
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendStudentCourseDestroy($Confirm = false)
    {

        $Stage = new Stage('Importvorbereitung', 'Leeren');
        $Stage->setMessage('Hierbei werden alle nicht importierte Daten der letzten Importvorbereitung gelöscht.');
        $tblIndiwareImportStudentList = Import::useService()->getIndiwareImportStudentAll(true);
        if (!$tblIndiwareImportStudentList) {
            $Stage->setContent(new Warning('Keine Restdaten eines Import\s vorhanden'));
            return $Stage.new Redirect('/Transfer/Indiware/Import', Redirect::TIMEOUT_ERROR);
        }
        if (!$Confirm) {

            $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import', new ChevronLeft()));
            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(new Question().'Vorhandene Importvorbereitung der Schüler-Kurse SEK II wirklich löschen? '
                        .new Muted(new Small('Anzahl Schüler-Datensätze: "<b>'.count($tblIndiwareImportStudentList).'</b>"')),
                        '',
                        Panel::PANEL_TYPE_DANGER,
                        new Standard(
                            'Ja', '/Transfer/Indiware/Import/StudentCourse/Destroy', new Ok(),
                            array('Confirm' => true)
                        )
                        .new Standard(
                            'Nein', '/Transfer/Indiware/Import', new Disable()
                        )
                    )
                    , 6))))
            );
        } else {

            // Destroy Basket
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            (Import::useService()->destroyIndiwareImportStudentAll()
                                ? new SuccessMessage('Der Import ist nun leer')
                                .new Redirect('/Transfer/Indiware/Import', Redirect::TIMEOUT_SUCCESS)
                                : new WarningMessage('Der Import konnte nicht vollständig gelöscht werden')
                                .new Redirect('/Transfer/Indiware/Import', Redirect::TIMEOUT_ERROR)
                            )
                        )))
                    ))
                )
            );
        }
        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Visible
     *
     * @return Stage
     */
    public function frontendIgnoreImport($Id = null, $Visible = null)
    {
        $Stage = new Stage('Import', 'Verhindern');
        $tblIndiwareImportStudent = Import::useService()->getIndiwareImportStudentById($Id);
        if ($tblIndiwareImportStudent) {
            Import::useService()->updateIndiwareImportStudentIsIgnore($tblIndiwareImportStudent,
                !$tblIndiwareImportStudent->getIsIgnore());
            if ($tblIndiwareImportStudent->getIsIgnore()) {
                $Stage->setContent(new SuccessMessage('Import wird nun manuell verhindert.')
                    .new Redirect('/Transfer/Indiware/Import/StudentCourse/Show', Redirect::TIMEOUT_SUCCESS,
                        array('Visible' => $Visible)));
            } else {
                $Stage->setContent(new SuccessMessage('Import wird nun nicht mehr manuell verhindert.')
                    .new Redirect('/Transfer/Indiware/Import/StudentCourse/Show', Redirect::TIMEOUT_SUCCESS,
                        array('Visible' => $Visible)));
            }
        } else {
            $Stage->setContent(new DangerMessage('Datensatz nicht gefunden')
                .new Redirect('/Transfer/Indiware/Import/StudentCourse/Show', Redirect::TIMEOUT_ERROR));
        }
        return $Stage;
    }

    /**
     * @param $YearId
     *
     * @return Stage
     */
    public function frontendImportStudentCourse($YearId = null)
    {
        $Stage = new Stage('Import', 'Ergebnis');
        $tblYear = Term::useService()->getYearById($YearId);
        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import', new ChevronLeft(), array(),
            'Zurück zum Indiware-Import'));

        $Stage->setMessage(
            new Container('Abgebildet werden alle Schüler-Kurse SEK II  aller importierten Klassen für das ausgewählte Jahr '.($tblYear ? $tblYear->getYear() : '').'.')
            .new Container('Kurse anderer Klassen bleiben unangetastet!'));
        $isImport = Import::useService()->importIndiwareStudentCourse();
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ($isImport
                                ? new SuccessMessage('Der Import wurde erfolgreich durchgeführt')
                                : new DangerMessage('Der Import enthielt keine gültigen Daten'))
                        )
                    )
                )
            )
        );
        return $Stage;
    }
}