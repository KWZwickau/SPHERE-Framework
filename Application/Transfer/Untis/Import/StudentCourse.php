<?php
namespace SPHERE\Application\Transfer\Untis\Import;

use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Transfer\Untis\Import\Service\Entity\TblUntisImportStudent;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Listing as ListingIcon;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Frontend\Text\Repository\Underline;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Title as TitleForm;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Success as SuccessIcon;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title as TitleLayout;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary as PrimaryLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger as DangerMessage;
use SPHERE\Common\Frontend\Message\Repository\Success as SuccessMessage;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class StudentCourse
 * @package SPHERE\Application\Transfer\Untis\Import
 */
class StudentCourse extends Import implements IFrontendInterface
{


    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__, __CLASS__.'::frontendUpload'
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
    }

    public function frontendStudentCoursePrepare()
    {

        $Stage = new Stage('Untis', 'Datentransfer');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Untis/Import', new ChevronLeft()));

        $Stage->setMessage('Importvorbereitung / Daten importieren');
        $tblYearAll = Term::useService()->getYearAllSinceYears(1);
        if (!$tblYearAll) {
            $tblYearAll = array();
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
//                    new LayoutRow(
//                        new LayoutColumn(
//                            ( $tblUntisImportStudentCourseList ? new WarningMessage(new WarningIcon().' Vorsicht vorhandene Importdaten werden entfernt!') : '' )
//                            , 6, array(LayoutColumn::GRID_OPTION_HIDDEN_SM)
//                        )),
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            new Form(
                                new FormGroup(array(
                                    new FormRow(
                                        new FormColumn(
                                            new Panel('Import',
                                                array(
                                                    ( new SelectBox('tblYear', 'Schuljahr auswählen', array(
                                                        '{{ Year }} {{ Description }}' => $tblYearAll
                                                    )) )->setRequired(),
                                                    ( new FileUpload('File10', 'Datei auswählen', 'Datei auswählen '.new ToolTip('GPU010', 'GPU010.txt'), null, array('showPreview' => false)) )->setRequired(),
                                                    ( new FileUpload('File15', 'Datei auswählen', 'Datei auswählen '.new ToolTip('GPU015', 'GPU015.txt'), null, array('showPreview' => false)) )->setRequired()
                                                ), Panel::PANEL_TYPE_INFO)
                                        )
                                    ),
                                )),
                                new Primary('Hochladen und Voransicht', new Upload()),
                                new Link\Route(__NAMESPACE__.'/StudentCourse')
                            )
                        ), 6)
                    )
                ), new TitleLayout('Schüler-Kurse SEK II', 'importieren'))
            )
        );

        return $Stage;
    }

    /**
     * @param UploadedFile|null $File10
     * @param UploadedFile|null $File15
     * @param null              $tblYear
     *
     * @return Stage|string
     */
    public function frontendUpload(UploadedFile $File10 = null, UploadedFile $File15 = null, $tblYear = null)
    {

        $Stage = new Stage('Untis', 'Daten importieren');
        $Stage->setMessage('Schüler-Kurse SEK II importieren');

        if ($File10 === null || $File15 === null || $tblYear === null || $tblYear <= 0) {
            $Stage->setContent(
                ( $tblYear <= 0
                    ? new WarningMessage('Bitte geben Sie das Schuljahr an.')
                    : new WarningMessage('Bitte geben Sie beide Dateien an.') )
                .new Redirect(new Route(__NAMESPACE__.'/StudentCourse/Prepare'), Redirect::TIMEOUT_ERROR, array(
                    'tblYear' => $tblYear
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

        Import::useService()->destroyUntisImportStudentAll();

        if ($File10 && !$File10->getError()
            && strtolower($File10->getClientOriginalExtension()) == 'txt'
        ){

            $Payload10 = new FilePointer('csv');
            $Payload10->setFileContent(file_get_contents($File10->getRealPath()));
            $Payload10->saveFile();

            // prepare import
            $Gateway010 = new StudentCourseGPU010($Payload10->getRealPath(), $tblYear);
        }

        if ($File15 && !$File15->getError()
            && strtolower($File15->getClientOriginalExtension()) == 'txt'
            && isset($Gateway010)
        ){

            $Payload15 = new FilePointer('csv');
            $Payload15->setFileContent(file_get_contents($File15->getRealPath()));
            $Payload15->saveFile();

            // prepare import
            $Gateway015 = new StudentCourseGPU015($Payload15->getRealPath(), $Gateway010->getImportList());
        }

        if(isset($Gateway015)){
            $ImportList = $Gateway015->getImportList();
            $tblAccount = Account::useService()->getAccountBySession();
            if ($ImportList && $tblYear && $tblAccount) {
                Import::useService()->createUntisImportStudentCourseByImportList($ImportList, $tblYear, $tblAccount);
            }
        }

        if(isset($Gateway015)){
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new TableData($Gateway010->getResultList(), null,
                                    array(
                                        'ShortName'        => 'Datei: Kurzname',
                                        'FileFirstName'    => 'Datei: Vorname',
                                        'FileLastName'     => 'Datei: Nachname',
                                        'readableBirthday' => 'Datei: Geburtstag',
                                        'Identifier'       => 'Datei: Schülernummer',
                                        'AppPerson'        => 'Software: Person',
                                        'FileDivision'     => 'Datei: Klasse',
                                        'AppDivision'      => 'Software: Klasse'
                                    ),
                                    array('order'      => array(array(0, 'desc')),
                                          'columnDefs' => array(
                                              array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0,1,2),
                                          )
                                    )
                                )
                            ),
                            new LayoutColumn(
                                new DangerLink('Abbrechen', '/Transfer/Untis/Import').
                                new Standard('Weiter', '/Transfer/Untis/Import/StudentCourse/Show',
                                    new ChevronRight())
                            )
                        ))
                    , new TitleLayout('Validierung Personenzuweisung', new Danger('Alle gelisteten Personen wurden nicht für die Bearbeitung aufgenommen!')))
                )
            );
        } else {
            return $Stage->setContent(new WarningMessage('Ungültige Datei!'))
                .new Redirect('/Transfer/Untis/Import', Redirect::TIMEOUT_ERROR);
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
        $Stage->addButton(new Standard('Zurück', '/Transfer/Untis/Import', new ChevronLeft()));
        $tblUntisImportStudentList = Import::useService()->getUntisImportStudentAll(true);
        $TableContent = array();
//        $TableCompare = array();
        $tblYear = false;
        $IsDisableImport = false;
        if ($tblUntisImportStudentList) {
            array_walk($tblUntisImportStudentList, function (TblUntisImportStudent $tblUntisImportStudent)
            use (&$TableContent, &$tblYear, $Visible, &$IsDisableImport) {
                $tblPerson = $tblUntisImportStudent->getServiceTblPerson();
                $tblYear = $tblUntisImportStudent->getServiceTblYear();
                $Level = $tblUntisImportStudent->getLevel();
                $tblDivision = $tblUntisImportStudent->getServiceTblDivision();
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
                    $Item['Year'] = $tblUntisImportStudent->getServiceTblYear();
                }
                if ($tblDivision) {
                    // Klassen Farblich Markieren wenn die Stufe nicht zum Import passt
                    if ($tblDivision->getTblLevel() && $tblDivision->getTblLevel()->getName() == $Level) {
                        $Item['Division'] = new Center($tblDivision->getDisplayName());
                    } else {
                        $Item['Division'] = new Danger(new Center(new ToolTip(new InfoIcon(),
                                'Klassenstufe/Schuljahr für diesen Schüler stimmt nicht überein!')
                            .' '.$tblDivision->getDisplayName()));
                          // Verhindern des Imports wenn Klassenstufen nicht übereinstimmen (Deaktivierte ignorieren)
                        if (!$tblUntisImportStudent->getIsIgnore()) {
                            $IsDisableImport = true;
                        }
                    }
                } else {
                    $Item['Division'] = new Danger(new Center(new ToolTip(new InfoIcon(), 'Keine Klasse vorhanden!')));
                    // Verhindern des Imports wenn Klasse nicht mehr existiert (Deaktivierte ignorieren)
                    if (!$tblUntisImportStudent->getIsIgnore()) {
                        $IsDisableImport = true;
                    }
                }
                $Item['Option'] = new Standard('', '/Transfer/Untis/Import/StudentCourse/Edit'
                        , new Edit(), array('Id' => $tblUntisImportStudent->getId(), 'Visible' => $Visible),
                        'Importvorbereitung bearbeiten').
                    new Standard('', '/Transfer/Untis/Import/StudentCourse/DivisionRefresh', new Repeat(),
                        array('Id' => $tblUntisImportStudent->getId(), 'Visible' => $Visible),
                        'Verwende die aktuelle Klasse des Schülers');
                if ($tblUntisImportStudent->getIsIgnore()) {
                    $Item['Option'] .= new Standard('', '/Transfer/Untis/Import/StudentCourse/Ignore',
                        new SuccessIcon(),
                        array(
                            'Id'      => $tblUntisImportStudent->getId(),
                            'Visible' => $Visible
                        ), 'Manuell freigeben');
                    $Item['Ignore'] = $this->setSortOrder(4).new Center(new Warning(new ToolTip(new Disable(), 'Manuell deaktiviert')));

                } else {
                    $Item['Option'] .= new Standard('', '/Transfer/Untis/Import/StudentCourse/Ignore',
                        new Remove(),
                        array(
                            'Id'      => $tblUntisImportStudent->getId(),
                            'Visible' => $Visible
                        ), 'Manuell sperren');
                    $Item['Ignore'] = $this->setSortOrder(5).new Center(new Success(new SuccessIcon()));
                }

                $IsImportError = false;
                $tblUntisImportStudentCourseList = Import::useService()
                    ->getUntisImportStudentCourseByUntisImportStudent($tblUntisImportStudent);

                $SubjectCount = 0;
                $IsWarning = false;
                if ($tblUntisImportStudentCourseList) {
                    foreach ($tblUntisImportStudentCourseList as $tblUntisImportStudentCourse) {
                        $ListContent = array();
                        $CourseNumber = $tblUntisImportStudentCourse->getCourseNumber();
                        $SubjectString = '';
                        if (($tblSubject = $tblUntisImportStudentCourse->getServiceTblSubject())) {
                            $SubjectCount++;
                            $SubjectString = $tblSubject->getAcronym().' - '.$tblSubject->getName();
                        }
                        $SubjectName = $tblUntisImportStudentCourse->getSubjectName();

//                        if ($SubjectName != '' && !$tblUntisImportStudentCourse->getServiceTblSubject()) {
//                            $SubjectString = ' Fach nicht gefunden!';
//                        }

                        if ($tblUntisImportStudentCourse->getIsIgnoreCourse()) {
                            $SubjectString = new Info(new InfoIcon().' Fach/Kurs wird Ignoriert');
                        } elseif ($SubjectName != '' && !$tblUntisImportStudentCourse->getServiceTblSubject()) {
                            $IsWarning = true;
                            $SubjectString = new Danger(new InfoIcon().' Fach nicht gefunden!');
                        }
                        $ListContent[] = $SubjectString;
//                            $ListContent[] = $GroupIsIntensiveString;
                        $GroupString = $tblUntisImportStudentCourse->getSubjectGroup();
                        $ListContent[] = $GroupString;
                        $Item['SubjectAndGroup'.$CourseNumber] = '<div style="min-width: 110px;">'.
                            new \SPHERE\Common\Frontend\Layout\Repository\Listing($ListContent)
                            .'</div>';

                        // Error wenn Fächerzuweisung fehlt
                        if (!$tblSubject && $tblUntisImportStudentCourse->getSubjectGroup() != '' && !$tblUntisImportStudentCourse->getIsIgnoreCourse()) {
                            if (!$tblUntisImportStudent->getIsIgnore()) {
                                $Item['Ignore'] = new Center(new Warning(new ToolTip(new Disable(),
                                    'Einige Fächer werden nicht importiert! (Fach nicht gefunden)')));
                            }
                            $IsImportError = true;
                        }
                    }
                }
                if (!$tblDivision) {
                    $Item['Ignore'] = $this->setSortOrder(1).new Center(new Danger(new ToolTip(new Disable(),
                        'Ohne Klasse kann die Person nicht importiert werden')));
                    $IsImportError = true;
                } elseif ($tblDivision->getTblLevel() && $tblDivision->getTblLevel()->getName() != $Level) {
                    $Item['Ignore'] = $this->setSortOrder(1).new Center(new Danger(new ToolTip(new Disable(),
                        'mit der Falschen Klassenstufe kann die Person nicht importiert werden')));
                    $IsImportError = true;
                }

                // Warnungen werden ergänzt
                if(!$IsImportError){
                    if($IsWarning){ // Fach nicht gefunden
                        $Item['Ignore'] = $this->setSortOrder(2).new Center(new Warning(new ToolTip(new WarningIcon(),
                            'nicht alle Fächer in der Schulsoftware gefunden')));
                    } elseif($SubjectCount < 5){     // Weniger als 5 Fächer hinterlegt
                        $Item['Ignore'] = $this->setSortOrder(3). new Center(new Warning(new ToolTip(new WarningIcon(),
                            'Möglicher Fehler, weniger als 5 Fächer vergeben')));
                    }
                }

                // Ignore priorisierte Ausgabe
                if ($tblUntisImportStudent->getIsIgnore()) {
                    $Item['Ignore'] = $this->setSortOrder(4).new Center(new Warning(new ToolTip(new Disable(), 'Manuell deaktiviert')));
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
                .new Redirect('/Transfer/Untis/Import', Redirect::TIMEOUT_WAIT));
            return $Stage;
        }

        $HeaderPanel = '';
        /** @var TblYear $tblYear */
        if ($tblYear) {
            $HeaderPanel = new Panel('Importvorbereitung',
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
                                '/Transfer/Untis/Import/StudentCourse/Show', new ListingIcon(), array(),
                                'Anzeige aller verfügbarer Daten')
                            .new Standard(($Visible == 1 ? new Info(new Bold('Gültige Importe')) : 'Gültige Importe'),
                                '/Transfer/Untis/Import/StudentCourse/Show', new Ok(), array('Visible' => 1),
                                'Anzeige der aktuell möglichen Importe')
                            .new Standard(($Visible == 2 ? new Info(new Bold('Fehler')) : 'Fehler'),
                                '/Transfer/Untis/Import/StudentCourse/Show', new EyeOpen(), array('Visible' => 2),
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
                                    'SubjectAndGroup1'  => new Underline('1. Fach').'<br/>'.'Kurs',
                                    'SubjectAndGroup2'  => new Underline('2. Fach').'<br/>'.'Kurs',
                                    'SubjectAndGroup3'  => new Underline('3. Fach').'<br/>'.'Kurs',
                                    'SubjectAndGroup4'  => new Underline('4. Fach').'<br/>'.'Kurs',
                                    'SubjectAndGroup5'  => new Underline('5. Fach').'<br/>'.'Kurs',
                                    'SubjectAndGroup6'  => new Underline('6. Fach').'<br/>'.'Kurs',
                                    'SubjectAndGroup7'  => new Underline('7. Fach').'<br/>'.'Kurs',
                                    'SubjectAndGroup8'  => new Underline('8. Fach').'<br/>'.'Kurs',
                                    'SubjectAndGroup9'  => new Underline('9. Fach').'<br/>'.'Kurs',
                                    'SubjectAndGroup10' => new Underline('10. Fach').'<br/>'.'Kurs',
                                    'SubjectAndGroup11' => new Underline('11. Fach').'<br/>'.'Kurs',
                                    'SubjectAndGroup12' => new Underline('12. Fach').'<br/>'.'Kurs',
                                    'SubjectAndGroup13' => new Underline('13. Fach').'<br/>'.'Kurs',
                                    'SubjectAndGroup14' => new Underline('14. Fach').'<br/>'.'Kurs',
                                    'SubjectAndGroup15' => new Underline('15. Fach').'<br/>'.'Kurs',
                                    'SubjectAndGroup16' => new Underline('16. Fach').'<br/>'.'Kurs',
                                    'SubjectAndGroup17' => new Underline('17. Fach').'<br/>'.'Kurs',
                                ),
                                array(
                                    'order'      => array(array(0, 'asc')),
                                    'columnDefs' => array(
//                                        array('orderable' => false, 'width' => '60px', 'targets' => -1),
//                                        array('orderable' => false, 'width' => '73px', 'targets' => -2),
//                                        array('type' => 'natural', 'targets' => 0),
//                                        array('type' => 'natural', 'targets' => 1)
                                    ),
                                    'responsive' => false,
                                    'pageLength' => -1,
                                ))
                        ),
                        new LayoutColumn(
                            ($IsDisableImport
                                ? new Container((new PrimaryLink('Import der Änderungen',
                                    '/Transfer/Untis/Import/StudentCourse/Import', new Save(),
                                    array('YearId' => ($tblYear ? $tblYear->getId() : null)),
                                    'Diese Aktion ist unwiderruflich!'))->setDisabled())
                                : new Container((new PrimaryLink('Import der Änderungen',
                                    '/Transfer/Untis/Import/StudentCourse/Import', new Save(),
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
    private function setSortOrder($number = 1)
    {

        return '<span hidden>'.$number.'</span>';
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
        $tblUntisImportStudent = Import::useService()->getUntisImportStudentById($Id);
        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        (Import::useService()->updateUntisImportStudentDivision($tblUntisImportStudent)
                            ? new SuccessMessage('Änderung erfolgt, Im gesuchtem Jahr wurde eine Klasse gefunden')
                            .new Redirect('/Transfer/Untis/Import/StudentCourse/Show', Redirect::TIMEOUT_SUCCESS,
                                array('Visible' => $Visible))
                            : new WarningMessage('Änderung nicht erfolg da keine Klasse im ausgewähltem Jahr gefunden wurde')
                            .new Redirect('/Transfer/Untis/Import/StudentCourse/Show', Redirect::TIMEOUT_ERROR,
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
        $Stage = new Stage('Schüler-Kurse SEK II', 'Bearbeiten');
        $tblUntisImportStudent = ($Id !== null ? Import::useService()->getUntisImportStudentById($Id) : false);
        if (!$tblUntisImportStudent) {
            $Stage->setContent(new WarningMessage('Schüler-Zuweisung nicht gefunden.')
                .new Redirect('/Transfer/Untis/Import/StudentCourse/Show', Redirect::TIMEOUT_ERROR));
            return $Stage;
        }

        $Stage->addButton(new Standard('Zurück', '/Transfer/Untis/Import/StudentCourse/Show', new ChevronLeft(),
            array('Visible' => $Visible)));

        $tblUntisImportStudentCourseList = Import::useService()->getUntisImportStudentCourseByUntisImportStudent(
            $tblUntisImportStudent);
        $tblPerson = $tblUntisImportStudent->getServiceTblPerson();
        $tblDivision = $tblUntisImportStudent->getServiceTblDivision();
        $tblYear = $tblUntisImportStudent->getServiceTblYear();

//        $arraySubjectName = array();

        $Global = $this->getGlobal();
//        $Global->POST['Data']['DivisionId'] = ($tblDivision ? $tblDivision->getId() : null);
        if ($tblUntisImportStudentCourseList) {
            foreach ($tblUntisImportStudentCourseList as $tblUntisImportStudentCourse) {
                if (($tblSubject = $tblUntisImportStudentCourse->getServiceTblSubject())) {
                    $Number = $tblUntisImportStudentCourse->getCourseNumber();
                    $Global->POST['Data']['SubjectId'.$Number] = $tblSubject->getId();
                    $Global->POST['Data']['SubjectGroup'.$Number] = $tblUntisImportStudentCourse->getSubjectGroup();
                    if ($tblUntisImportStudentCourse->getIsIntensiveCourse()) {
                        $Global->POST['Data']['IsIntensivCourse'.$Number] = 1;
                    }
//                    $arraySubjectName[$Number] = $tblUntisImportStudentCourse->getSubjectName();
                } else {
                    $Number = $tblUntisImportStudentCourse->getCourseNumber();
                    $Global->POST['Data']['SubjectGroup'.$Number] = $tblUntisImportStudentCourse->getSubjectGroup();
                    if ($tblUntisImportStudentCourse->getIsIgnoreCourse()) {
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


        $Form = $this->formSubjectCourse($tblUntisImportStudent, $tblYear);
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
                                ->updateUntisImportStudentCourse($Form, $tblUntisImportStudent, $Data, $Visible
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
     * @param TblUntisImportStudent $tblUntisImportStudent
     * @param TblYear|null          $tblYear
     *
     * @return Form
     */
    public function formSubjectCourse(TblUntisImportStudent $tblUntisImportStudent, TblYear $tblYear = null)
    {

        $FormSubjectAll = array();
        $tblSubjectList = Subject::useService()->getSubjectAll();
        for ($i = 1; $i <= 17; $i++) {
            $tblUntisImportStudentCourse = Import::useService()->getUntisImportStudentCourseByUntisImportStudentAndNumber(
                $tblUntisImportStudent, $i);
            $Status = false;
            if ($tblUntisImportStudentCourse) {
                $Subject = 'Fach-Import: '.$tblUntisImportStudentCourse->getSubjectName();
                if ($tblUntisImportStudentCourse->getSubjectName() && $tblUntisImportStudentCourse->getServiceTblSubject()) {
                    $Status = Panel::PANEL_TYPE_INFO;
                }
                if ($tblUntisImportStudentCourse->getSubjectName() && !$tblUntisImportStudentCourse->getServiceTblSubject()) {
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
     * @param null $Id
     * @param null $Visible
     *
     * @return Stage
     */
    public function frontendIgnoreImport($Id = null, $Visible = null)
    {
        $Stage = new Stage('Import', 'Verhindern');
        $tblUntisImportStudent = Import::useService()->getUntisImportStudentById($Id);
        if ($tblUntisImportStudent) {
            Import::useService()->updateUntisImportStudentIsIgnore($tblUntisImportStudent,
                !$tblUntisImportStudent->getIsIgnore());
            if ($tblUntisImportStudent->getIsIgnore()) {
                $Stage->setContent(new SuccessMessage('Import wird nun manuell verhindert.')
                    .new Redirect('/Transfer/Untis/Import/StudentCourse/Show', Redirect::TIMEOUT_SUCCESS,
                        array('Visible' => $Visible)));
            } else {
                $Stage->setContent(new SuccessMessage('Import wird nun nicht mehr manuell verhindert.')
                    .new Redirect('/Transfer/Untis/Import/StudentCourse/Show', Redirect::TIMEOUT_SUCCESS,
                        array('Visible' => $Visible)));
            }
        } else {
            $Stage->setContent(new DangerMessage('Datensatz nicht gefunden')
                .new Redirect('/Transfer/Untis/Import/StudentCourse/Show', Redirect::TIMEOUT_ERROR));
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
        $Stage->addButton(new Standard('Zurück', '/Transfer/Untis/Import', new ChevronLeft(), array(),
            'Zurück zum Untis-Import'));

        $Stage->setMessage(
            new Container('Abgebildet werden alle Schüler-Kurse SEK II  aller importierten Klassen für das ausgewählte Jahr '.($tblYear ? $tblYear->getYear() : '').'.')
            .new Container('Kurse anderer Klassen bleiben unangetastet!'));
        $isImport = Import::useService()->importUntisStudentCourse();
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ($isImport
                                ? new SuccessMessage('Der Import wurde erfolgreich durchgeführt')
                                .new Redirect('/Transfer/Untis/Import', Redirect::TIMEOUT_SUCCESS)
                                : new DangerMessage('Der Import enthielt keine gültigen Daten')
                                .new Redirect('/Transfer/Untis/Import', Redirect::TIMEOUT_ERROR)
                            )
                        )
                    )
                )
            )
        );
        return $Stage;
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
        $tblUntisImportStudentList = Import::useService()->getUntisImportStudentAll(true);
        if (!$tblUntisImportStudentList) {
            $Stage->setContent(new Warning('Keine Restdaten eines Import\s vorhanden'));
            return $Stage.new Redirect('/Transfer/Untis/Import', Redirect::TIMEOUT_ERROR);
        }
        if (!$Confirm) {

            $Stage->addButton(new Standard('Zurück', '/Transfer/Untis/Import', new ChevronLeft()));
            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(new Question().'Vorhandene Importvorbereitung der Schüler-Kurse SEK II wirklich löschen? '
                        .new Muted(new Small('Anzahl Schüler: "<b>'.count($tblUntisImportStudentList).'</b>"')),
                        '',
                        Panel::PANEL_TYPE_DANGER,
                        new Standard(
                            'Ja', '/Transfer/Untis/Import/StudentCourse/Destroy', new Ok(),
                            array('Confirm' => true)
                        )
                        .new Standard(
                            'Nein', '/Transfer/Untis/Import', new Disable()
                        )
                    )
                    , 6))))
            );
        } else {

            // Destroy Basket
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(
                            ( Import::useService()->destroyUntisImportStudentAll()
                                ? new SuccessMessage('Der Import ist nun leer')
                                .new Redirect('/Transfer/Untis/Import', Redirect::TIMEOUT_SUCCESS)
                                : new WarningMessage('Der Import konnte nicht vollständig gelöscht werden')
                                .new Redirect('/Transfer/Untis/Import', Redirect::TIMEOUT_ERROR)
                            )
                        ))
                    ))
                )
            );
        }
        return $Stage;
    }
}