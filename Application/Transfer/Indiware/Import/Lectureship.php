<?php

namespace SPHERE\Application\Transfer\Indiware\Import;

use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectTeacher;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Transfer\Indiware\Import\Service\Entity\TblIndiwareImportLectureship;
use SPHERE\Common\Frontend\Form\Repository\Field\FileUpload;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Title as TitleForm;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
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
 * Class Lectureship
 * @package SPHERE\Application\Transfer\Indiware\Import
 */
class Lectureship extends Import implements IFrontendInterface
{


    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__, __CLASS__.'::frontendUpload'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__.'/Prepare', __CLASS__.'::frontendLectureshipPrepare'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__.'/Show', __CLASS__.'::frontendLectureshipShow'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__.'/Edit', __CLASS__.'::frontendLectureshipEdit'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__.'/Destroy', __CLASS__.'::frontendLectureshipDestroy'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__.'/Ignore', __CLASS__.'::frontendIgnoreImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__.'/Import', __CLASS__.'::frontendImportLectureship'
        ));

        parent::registerModule();
    }

    public function frontendLectureshipPrepare()
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

        // try to POST tblYear if YearByNow exist
        $tblYearList = Term::useService()->getYearByNow();
        if ($tblYearList) {
            $tblYear = false;
            // last Entity should be the first created year
            foreach ($tblYearList as $tblYearEntity) {
                $tblYear = $tblYearEntity;
            }
            if ($tblYear) {
                $Global = $this->getGlobal();
                $Global->POST['tblYear'] = $tblYear->getId();
                $Global->savePost();
            }
        }

        $tblIndiwareImportLectureshipList = Import::useService()->getIndiwareImportLectureshipAll(true);

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            ($tblIndiwareImportLectureshipList ? new WarningMessage(new WarningIcon().' Vorsicht vorhandene Importdaten werden entfernt!') : '')
                            , 6, array(LayoutColumn::GRID_OPTION_HIDDEN_SM)
                        )),
                    new LayoutRow(
                        new LayoutColumn(new Well(array(
                            new Form(
                                new FormGroup(array(
                                    new FormRow(
                                        new FormColumn(
                                            new Panel('Import',
                                                array(
                                                    (new SelectBox('tblYear', 'Schuljahr auswählen', array(
                                                        '{{ Year }} {{ Description }}' => $tblYearAll
                                                    )))->setRequired(),
                                                    (new FileUpload('File', 'Datei auswählen', 'Datei auswählen', null,
                                                        array('showPreview' => false)))->setRequired()
                                                ), Panel::PANEL_TYPE_INFO)
                                        )
                                    ),
                                )),
                                new Primary('Hochladen und Voransicht', new Upload()),
                                new Link\Route(__NAMESPACE__.'/Lectureship')
                            )
                        )), 6)
                    )
                ), new TitleLayout('Lehraufträge', 'importieren'))
            )
        );

        return $Stage;
    }

    /**
     * @param null|UploadedFile $File
     * @param null|int          $tblYear
     *
     * @return Stage|string
     */
    public function frontendUpload(UploadedFile $File = null, $tblYear = null)
    {

        $Stage = new Stage('Indiware', 'Daten importieren');
        $Stage->setMessage('Lehraufträge importieren');

        if ($File === null || $tblYear === null || $tblYear <= 0) {
            $Stage->setContent(
                ($tblYear <= 0
                    ? new WarningMessage('Bitte geben Sie das Schuljahr an.')
                    : new WarningMessage('Bitte geben sie die Datei an.'))
                .new Redirect(new Route(__NAMESPACE__), Redirect::TIMEOUT_ERROR, array(
                    'tblYear' => $tblYear
                ))
            );
            return $Stage;
        }
        $tblYear = Term::useService()->getYearById($tblYear);
        if (!$tblYear) {

            $Stage->setContent(
                new WarningMessage('Bitte geben Sie ein gültiges Schuljahr an.')
                .new Redirect(new Route(__NAMESPACE__), Redirect::TIMEOUT_ERROR, array(
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
            Import::useService()->destroyIndiwareImportLectureship();

            // match File
            $Extension = (strtolower($File->getClientOriginalExtension()) == 'txt'
                ? 'csv'
                : strtolower($File->getClientOriginalExtension())
            );

            $Payload = new FilePointer($Extension);
            $Payload->setFileContent(file_get_contents($File->getRealPath()));
            $Payload->saveFile();

            // add import
            $Gateway = new LectureshipGateway($Payload->getRealPath(), $tblYear);

            $ImportList = $Gateway->getImportList();
            $tblAccount = Account::useService()->getAccountBySession();
            if ($ImportList && $tblYear && $tblAccount) {
                Import::useService()->createIndiwareImportLectureShipByImportList($ImportList, $tblYear, $tblAccount);
            }

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new TableData($Gateway->getResultList(), null,
                                    array(
                                        'FileDivision1'    => 'Datei: Klasse 1',
                                        'AppDivision1'     => 'Software: Klasse 1',
                                        'FileDivision2'    => 'Datei: Klasse 2',
                                        'AppDivision2'     => 'Software: Klasse 2',
                                        'FileTeacher1'     => 'Datei: Lehrer 1',
                                        'AppTeacher1'      => 'Software: Lehrer 1',
                                        'FileTeacher2'     => 'Datei: Lehrer 2',
                                        'AppTeacher2'      => 'Software: Lehrer 2',
                                        'FileTeacher3'     => 'Datei: Lehrer 3',
                                        'AppTeacher3'      => 'Software: Lehrer 3',
                                        'FileSubject'      => 'Datei: Fachkürzel',
                                        'AppSubject'       => 'Software: Fach',
                                        'FileSubjectGroup' => 'Datei: Gruppe',
                                        'AppSubjectGroup'  => 'Software: Gruppe'
                                    ),
                                    array(
                                        'order'      => array(array(0, 'desc')),
                                        'columnDefs' => array(
                                            array('type' => 'natural', 'targets' => 0),
                                        )
                                    )
                                )
                            ),
                            new LayoutColumn(
                                new Standard('Weiter', '/Transfer/Indiware/Import/Lectureship/Show', new ChevronRight())
                            )
                        ))
                        , new TitleLayout('Validierung',
                        new Danger('Rote Einträge wurden nicht für die Bearbeitung aufgenommen!')))
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
    public function frontendLectureshipShow($Visible = false)
    {
        $Stage = new Stage('Lehraufträge', 'Übersicht');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import', new ChevronLeft()));
        $tblIndiwareImportLectureshipList = Import::useService()->getIndiwareImportLectureshipAll(true);
        $TableContent = array();
        $TableCompare = array();
        $tblYear = false;
        if ($tblIndiwareImportLectureshipList) {
            array_walk($tblIndiwareImportLectureshipList,
                function (TblIndiwareImportLectureship $tblIndiwareImportLectureship)
                use (&$TableContent, &$tblYear, $Visible, &$TableCompare) {

                    $ImportError = 0;
                    //compare informations
                    $Item['PersonId'] = '';
                    $Item['SubjectId'] = '';
                    $Item['DivisionId'] = '';


                    $Item['FileDivision'] = $tblIndiwareImportLectureship->getSchoolClass();
                    $Item['AppDivision'] = new Warning('Keine Klasse hinterlegt');
                    $Item['FileTeacher'] = $tblIndiwareImportLectureship->getTeacherAcronym();
                    $Item['AppTeacher'] = new Warning('Kein Lehrer');
                    $Item['FileSubject'] = $tblIndiwareImportLectureship->getSubjectName();
                    $Item['AppSubject'] = new Warning('Kein Fach');
                    $Item['FileSubjectGroup'] = $tblIndiwareImportLectureship->getSubjectGroupName();
                    $Item['AppSubjectGroup'] = $tblIndiwareImportLectureship->getSubjectGroup();
                    $Item['Option'] = new Standard('', '/Transfer/Indiware/Import/Lectureship/Edit'
                        , new Edit(), array('Id' => $tblIndiwareImportLectureship->getId(), 'Visible' => $Visible),
                        'Importvorbereitung bearbeiten');

                    if (!$tblYear && $tblIndiwareImportLectureship->getServiceTblYear()) {
                        $tblYear = $tblIndiwareImportLectureship->getServiceTblYear();
                    }
                    if (($tblDivision = $tblIndiwareImportLectureship->getServiceTblDivision())) {
                        $Item['AppDivision'] = $tblDivision->getDisplayName();
                        $Item['DivisionId'] = $tblDivision->getId();
                    } else {
                        $ImportError++;
                    }
                    if (($tblTeacher = $tblIndiwareImportLectureship->getServiceTblTeacher())) {
                        $Item['AppTeacher'] = $tblTeacher->getAcronym().' - '.
                            (($tblPerson = $tblTeacher->getServiceTblPerson()) ? $tblPerson->getFullName() : 'Fehlende Person');
                        if ($tblPerson) {
                            $Item['PersonId'] = $tblPerson->getId();
                        }
                    } else {
                        $ImportError++;
                    }
                    if (($tblSubject = $tblIndiwareImportLectureship->getServiceTblSubject())) {
                        $Item['AppSubject'] = $tblSubject->getAcronym().' - '.$tblSubject->getName();
                        $Item['SubjectId'] = $tblSubject->getId();
                    } else {
                        $ImportError++;
                    }
//                // not empty SubjectGroup by import file
//                if ($tblIndiwareImportLectureship->getSubjectGroupName() !== '') {
//                    $Item['AppSubjectGroup'] = new Warning('Keine Gruppe');
//                    $ImportError++;
//                }
//                // found SubjectGroup
//                if (( $tblSubjectGroup = $tblIndiwareImportLectureship->getSubjectGroup() )) {
//                    $Item['AppSubjectGroup'] = $tblSubjectGroup->getName().' '.new Small(new Muted($tblSubjectGroup->getDescription()));
//                    $ImportError--;
//                }
                    $showIgnoreButton = false;
                    // no import by Warning
                    if ($ImportError >= 1) {
                        $Item['Ignore'] = new Center(new Danger(new Ban()));
                    } else {
                        // manual no Import
                        if ($tblIndiwareImportLectureship->getIsIgnore()) {
                            $Item['Ignore'] = new Center(new Danger(new Disable()));
                            $ImportError++;
                        } else {
                            $Item['Ignore'] = new Center(new Success(new SuccessIcon()));
                            $showIgnoreButton = true;
                        }
                    }
                    if ($showIgnoreButton) {
                        $Item['Option'] .= new Standard('', '/Transfer/Indiware/Import/Lectureship/Ignore',
                            new Remove(),
                            array('Id' => $tblIndiwareImportLectureship->getId()), 'Manuell sperren');
                    }

                    if (!$Visible) {
                        array_push($TableContent, $Item);
                    } else {
                        if ($Visible == 1) {
                            // only rows they would be import
                            if ($ImportError == 0) {
                                array_push($TableContent, $Item);
                            }
                        } elseif ($Visible == 2) {
                            // only rows they need update to import
                            if ($ImportError >= 1) {
                                array_push($TableContent, $Item);
                            }
                        }
                    }
                    if ($ImportError == 0) {
                        array_push($TableCompare, $Item);
                    }

                });
        } else {
            $Stage->setContent(new WarningMessage('Leider konnten keine Lehraufträge importiert werden.
            Bitte kontrollieren Sie ihre Datei und das angegebene Schuljahr')
                .new Redirect('/Transfer/Indiware/Import', Redirect::TIMEOUT_WAIT));
            return $Stage;
        }

        // get all exist Lectureship by matched Import
        $tblSubjectTeacherList = array();
        /** @var TblYear $tblYear */
        if (!empty($tblIndiwareImportLectureshipList)) {
            $tblDivisionList = Import::useService()->getDivisionListByIndiwareImportLectureship($tblIndiwareImportLectureshipList);
            if ($tblDivisionList) {
                $tblSubjectTeacherList = Import::useService()->getSubjectTeacherListByDivisionList($tblDivisionList);
            }
        }

        // find existing and/or probably soon deleted Lectureship
        $TableControl = array();
        // need persistent List by boubled import Lectureship entrys
        $tblComparePersistent = $TableCompare;
        if (!empty($tblSubjectTeacherList)) {
            array_walk($tblSubjectTeacherList, function (TblSubjectTeacher $tblSubjectTeacher) use (
                &$TableControl,
                &$TableCompare,
                $tblComparePersistent
            ) {
                $Item['Division'] = '';
                $Item['Person'] = '';
                $Item['Subject'] = '';
                $Item['SubjectGroup'] = '';
                $Item['Status'] = new Danger(new Remove().' Lehrauftrag löschen!');

                $tblDivisionSubject = $tblSubjectTeacher->getTblDivisionSubject();
                if ($tblDivisionSubject) {
                    $tblSubject = $tblDivisionSubject->getServiceTblSubject();
                    if ($tblSubject) {
                        $Item['Subject'] = $tblSubject->getAcronym().' - '.$tblSubject->getName();
                        $tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup();
                        if ($tblSubjectGroup) {

                            $Item['SubjectGroup'] = $tblSubjectGroup->getName();
                        }
                    }
                    $tblDivision = $tblDivisionSubject->getTblDivision();
                    if ($tblDivision) {
                        $Item['Division'] = $tblDivision->getDisplayName();
                    }
                }

                $tblPerson = $tblSubjectTeacher->getServiceTblPerson();
                if ($tblPerson) {
                    $Item['Person'] = $tblPerson->getFullName();
                    $tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson);
                    if ($tblTeacher) {
                        $Item['Person'] = $tblTeacher->getAcronym().' - '.$tblPerson->getFullName();
                    }
                    if (!empty($tblComparePersistent)) {
                        // matched rows will be recreated
                        foreach ($tblComparePersistent as $Key => &$Compare) {
                            if ($Compare['PersonId'] == $tblPerson->getId()
                                && $Compare['AppSubject'] == $Item['Subject']
                                && $Compare['AppSubjectGroup'] == $Item['SubjectGroup']
                                && $Compare['AppDivision'] == $Item['Division']
                            ) {
                                $Item['Status'] = new Info(new PersonIcon()).' Lehrauftrag behalten';
                                unset($TableCompare[$Key]);
                            }
                        }
                    }
                }
                array_push($TableControl, $Item);
            });
            $TableCompare = array_filter($TableCompare);
        }

        // Add Lectureship
        if (!empty($TableCompare)) {
            array_walk($TableCompare, function ($AddLectureship) use (&$TableControl) {

                $Item['Division'] = $AddLectureship['AppDivision'];
                $Item['Person'] = '';
                $Item['Subject'] = $AddLectureship['AppSubject'].new Success(' (Neu)');
                $Item['SubjectGroup'] = ($AddLectureship['AppSubjectGroup'] !== '' ? $AddLectureship['AppSubjectGroup'].new Success(' (Neu)') : '');

                $tblDivision = (isset($AddLectureship['DivisionId'])
                    ? Division::useService()->getDivisionById($AddLectureship['DivisionId'])
                    : false);
                $tblSubject = (isset($AddLectureship['SubjectId'])
                    ? Subject::useService()->getSubjectById($AddLectureship['SubjectId'])
                    : false);
                if ($tblDivision && $tblSubject) {
                    if (Division::useService()->getSubjectGroupByNameAndDivisionAndSubject(
                        $AddLectureship['AppSubjectGroup'],
                        $tblDivision,
                        $tblSubject)
                    ) {
                        $Item['SubjectGroup'] = $AddLectureship['AppSubjectGroup'];
                    }

                    if (Division::useService()->getDivisionSubjectBySubjectAndDivision($tblSubject, $tblDivision)) {
                        $Item['Subject'] = $AddLectureship['AppSubject'];
                    }
                }


                $Item['Status'] = new Success(new Ok().' Lehrauftrag erstellen!');
                $tblPerson = Person::useService()->getPersonById($AddLectureship['PersonId']);
                if ($tblPerson) {
                    $Item['Person'] = $tblPerson->getFullName();
                    $tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson);
                    if ($tblTeacher) {
                        $Item['Person'] = $tblTeacher->getAcronym().' - '.$tblPerson->getFullName();
                    }
                }
                array_push($TableControl, $Item);
            });
        }

        $HeaderPanel = '';
        /** @var TblYear $tblYear */
        if ($tblYear) {
            $HeaderPanel = new Panel('Importvorbereitung', 'für das Schuljahr: '.$tblYear->getDisplayName(),
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
                                '/Transfer/Indiware/Import/Lectureship/Show', new Listing(), array(),
                                'Anzeige aller verfügbarer Daten')
                            .new Standard(($Visible == 1 ? new Info(new Bold('Gültige Importe')) : 'Gültige Importe'),
                                '/Transfer/Indiware/Import/Lectureship/Show', new Ok(), array('Visible' => 1),
                                'Anzeige der aktuell möglichen Importe')
                            .new Standard(($Visible == 2 ? new Info(new Bold('Fehler')) : 'Fehler'),
                                '/Transfer/Indiware/Import/Lectureship/Show', new EyeOpen(), array('Visible' => 2),
                                'Anzeige aller Fehler')
                        ),
                        new LayoutColumn(
                            new TableData($TableContent, new Title((!$Visible ? 'Alle Datensätze' :
                                ($Visible == 1 ? 'Importfähige Datensätze' : 'korrekturbedürftige Datensätze'))),
                                array(
                                    'FileDivision'     => 'Datei: Klasse',
                                    'AppDivision'      => 'Software: Klasse',
                                    'FileTeacher'      => 'Datei: Lehrerkürzel',
                                    'AppTeacher'       => 'Software: Lehrer',
                                    'FileSubject'      => 'Datei: Fächerkürzel',
                                    'AppSubject'       => 'Software: Fach',
                                    'FileSubjectGroup' => 'Datei: Gruppe',
                                    'AppSubjectGroup'  => 'Software: Gruppe',
                                    'Ignore'           => 'Importieren',
                                    'Option'           => '',
                                ),
                                array(
                                    'order'      => array(array(1, 'asc'), array(5, 'asc')),
                                    'columnDefs' => array(
                                        array('orderable' => false, 'width' => '60px', 'targets' => -1),
                                        array('orderable' => false, 'width' => '73px', 'targets' => -2),
                                        array('type' => 'natural', 'targets' => 0),
                                        array('type' => 'natural', 'targets' => 1)
                                    )
                                ))
                        ),
                        new LayoutColumn(array(
                            new TitleLayout(new EyeOpen().' Änderungsübersicht zu den Livedaten'),
                            new TableData($TableControl, null,
                                array(
                                    'Division'     => 'Klasse',
                                    'Person'       => 'Lehrer',
                                    'Subject'      => 'Fach',
                                    'SubjectGroup' => 'Fach Gruppe',
                                    'Status'       => 'Änderung',
                                ),
                                array(
                                    'order'      => array(
                                        array(4, 'desc'),
                                        array(0, 'asc'),
                                        array(2, 'asc')
                                    ),
                                    'columnDefs' => array(
//                                        array('orderable' => false, 'width' => '60px', 'targets' => -1),
                                        array('type' => 'natural', 'targets' => 0)
                                    )
                                ))
                        )),
                        new LayoutColumn(
                            new Container(new PrimaryLink('Import der Änderungen',
                                '/Transfer/Indiware/Import/Lectureship/Import', new Save(),
                                array('YearId' => ($tblYear ? $tblYear->getId() : null)),
                                'Diese Aktion ist unwiderruflich!'))
                            , 4)
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null     $Id
     * @param null     $Data
     * @param bool|int $Visible
     *
     * @return Stage
     */
    public function frontendLectureshipEdit($Id = null, $Data = null, $Visible = false)
    {
        $Stage = new Stage('Lehrauftrag', 'Bearbeiten');
        $tblIndiwareImportLectureship = ($Id !== null ? Import::useService()->getIndiwareImportLectureshipById($Id) : false);
        if (!$tblIndiwareImportLectureship) {
            $Stage->setContent(new WarningMessage('Lehrauftrag nicht gefunden.')
                .new Redirect('/Transfer/Indiware/Import/Lectureship/Show', Redirect::TIMEOUT_ERROR));
            return $Stage;
        }

        $tblYear = $tblIndiwareImportLectureship->getServiceTblYear();
        if (!$tblYear) {
            $Stage->setContent(new WarningMessage('Schuljahr nicht gefunden. Dies erfordert einen erneuten Import')
                .new Redirect('/Transfer/Indiware/Import/Lectureship/Show', Redirect::TIMEOUT_ERROR));
            return $Stage;
        }

        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import/Lectureship/Show', new ChevronLeft(),
            array('Visible' => $Visible)));

        $Global = $this->getGlobal();
        if ($Data === null) {
            if (($tblDivision = $tblIndiwareImportLectureship->getServiceTblDivision())) {
                $Global->POST['Data']['DivisionId'] = $tblDivision->getId();
            }
            if (($tblTeacher = $tblIndiwareImportLectureship->getServiceTblTeacher())) {
                $Global->POST['Data']['TeacherId'] = $tblTeacher->getId();
            }
            if (($tblSubject = $tblIndiwareImportLectureship->getServiceTblSubject())) {
                $Global->POST['Data']['SubjectId'] = $tblSubject->getId();
            }
            if (($SubjectGroup = $tblIndiwareImportLectureship->getSubjectGroup())) {
                $Global->POST['Data']['SubjectGroup'] = $SubjectGroup;
            }
            if (($IsIgnore = $tblIndiwareImportLectureship->getIsIgnore())) {
                $Global->POST['Data']['IsIgnore'] = $IsIgnore;
            }
            $Global->savePost();
        }

        $Form = $this->formImport($tblYear);
        $Form->appendFormButton(new Primary('Speichern', new Save()));
        $Form->setConfirm('Die Zuweisung der Personen wurde noch nicht gespeichert.');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Schuljahr: '.$tblYear->getDisplayName(),
                                'Die verfügbare Klassenauswahl begrenzt sich auf dieses Schuljahr',
                                Panel::PANEL_TYPE_SUCCESS)
                        ),
                        new LayoutColumn(
                            new TitleLayout('Daten', 'aus dem Import:')
                        ),
                        new LayoutColumn(
                            new Panel('Klasse:',
                                $tblIndiwareImportLectureship->getSchoolClass(), Panel::PANEL_TYPE_SUCCESS)
                            , 3),
                        new LayoutColumn(
                            new Panel('Lehrer:',
                                $tblIndiwareImportLectureship->getTeacherAcronym(), Panel::PANEL_TYPE_SUCCESS)
                            , 3),
                        new LayoutColumn(
                            new Panel('Fach:',
                                $tblIndiwareImportLectureship->getSubjectName(), Panel::PANEL_TYPE_SUCCESS)
                            , 3),
                        new LayoutColumn(
                            new Panel('Gruppe:',
                                $tblIndiwareImportLectureship->getSubjectGroupName(), Panel::PANEL_TYPE_SUCCESS)
                            , 3),
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Import::useService()->updateIndiwareImportLectureship(
                                    $Form, $tblIndiwareImportLectureship, $Data, $Visible
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
     * @param TblYear $tblYear
     *
     * @return Form
     */
    public function formImport(TblYear $tblYear)
    {

        $tblDivisionList = Division::useService()->getDivisionByYear($tblYear);
        $tblDivisionList = ($tblDivisionList ? $tblDivisionList : array());
        $tblTeacherList = Teacher::useService()->getTeacherAll();
        $tblTeacherList = ($tblTeacherList ? $tblTeacherList : array());
        $tblSubjectList = Subject::useService()->getSubjectAll();
        $tblSubjectList = ($tblSubjectList ? $tblSubjectList : array());
        $tblSubjectGroupList = Division::useService()->getSubjectGroupAll();

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Klasse', new SelectBox('Data[DivisionId]', '',
                            array('{{ DisplayName }} - {{ tblLevel.serviceTblType.Name }}' => $tblDivisionList)),
                            Panel::PANEL_TYPE_INFO)
                        , 3),
                    new FormColumn(
                        new Panel('Lehrer', new SelectBox('Data[TeacherId]', '',
                            array('{{ Acronym }} - {{ ServiceTblPerson.FullName }}' => $tblTeacherList)),
                            Panel::PANEL_TYPE_INFO)
                        , 3),
                    new FormColumn(
                        new Panel('Fach',
                            new SelectBox('Data[SubjectId]', '',
                                array('{{ Acronym }} - {{ Name }}' => $tblSubjectList)),
                            Panel::PANEL_TYPE_INFO)
                        , 3),
                    new FormColumn(
                        new Panel('Gruppe',
                            new AutoCompleter('Data[SubjectGroup]', '', '', array('Name' => $tblSubjectGroupList)),
                            Panel::PANEL_TYPE_INFO)
                        , 3)
                )),
                new FormRow(
                    new FormColumn(
                        new Panel('Importverhalten',
                            new CheckBox('Data[IsIgnore]', 'Import verhindern', '1'),
                            Panel::PANEL_TYPE_INFO)
                        , 2)
                )
            ), new TitleForm(new Edit().' Bearbeiten', 'der Angaben'))
        );
    }

    /**
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendLectureshipDestroy($Confirm = false)
    {

        $Stage = new Stage('Importvorbereitung', 'Leeren');
        $Stage->setMessage('Hierbei werden alle nicht importierte Daten der letzten Importvorbereitung gelöscht.');
        $tblIndiwareImportLectureshipList = Import::useService()->getIndiwareImportLectureshipAll(true);
        if (!$tblIndiwareImportLectureshipList) {
            $Stage->setContent(new Warning('Keine Restdaten eines Import\s vorhanden'));
            return $Stage.new Redirect('/Transfer/Indiware/Import', Redirect::TIMEOUT_ERROR);
        }
        if (!$Confirm) {

            $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import', new ChevronLeft()));
            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(new Question().'Vorhandene Importvorbereitung der Lehraufträge wirklich löschen? '
                        .new Muted(new Small('Anzahl Datensätze: "<b>'.count($tblIndiwareImportLectureshipList).'</b>"')),
                        '',
                        Panel::PANEL_TYPE_DANGER,
                        new Standard(
                            'Ja', '/Transfer/Indiware/Import/Lectureship/Destroy', new Ok(),
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
                            (Import::useService()->destroyIndiwareImportLectureship()
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
     *
     * @return Stage
     */
    public function frontendIgnoreImport($Id = null)
    {
        $Stage = new Stage('Import', 'Verhindern');
        $tblIndiwareImportLectureship = Import::useService()->getIndiwareImportLectureshipById($Id);
        if ($tblIndiwareImportLectureship) {
            Import::useService()->updateIndiwareImportLectureshipIsIgnore($tblIndiwareImportLectureship);
            $Stage->setContent(new SuccessMessage('Import wird nun manuell verhindert.')
                .new Redirect('/Transfer/Indiware/Import/Lectureship/Show', Redirect::TIMEOUT_SUCCESS));
        } else {
            $Stage->setContent(new DangerMessage('Datensatz nicht gefunden')
                .new Redirect('/Transfer/Indiware/Import/Lectureship/Show', Redirect::TIMEOUT_ERROR));
        }
        return $Stage;
    }

    /**
     * @param $YearId
     *
     * @return Stage
     */
    public function frontendImportLectureShip($YearId = null)
    {
        $Stage = new Stage('Import', 'Ergebnis');
        $tblYear = Term::useService()->getYearById($YearId);
        $Stage->addButton(new Standard('Zurück', '/Transfer/Indiware/Import', new ChevronLeft(), array(),
            'Zurück zum Indiware-Import'));

        $Stage->setMessage(
            new Container('Abgebildet werden alle Lehraufträge aller importierten Klassen für das ausgewählte Jahr '.($tblYear ? $tblYear->getYear() : '').'.')
            .new Container('Lehraufträge anderer Klassen bleiben unangetastet!'));
        $LayoutRowList = Import::useService()->importIndiwareLectureship();
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    $LayoutRowList
                )
            )
        );
        return $Stage;
    }
}