<?php
namespace SPHERE\Application\Transfer\Untis\Import;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\Transfer\Untis\Import\Service\Entity\TblUntisImportLectureship;
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
use SPHERE\Common\Frontend\IFrontendInterface;
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
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Transfer\Untis\Import
 */
class Frontend extends Extension implements IFrontendInterface
{

    public function frontendLectureshipShow($MissingInfo = false)
    {
        $Stage = new Stage('Lehraufträge', 'Übersicht');
        $Stage->addButton(new Standard('Zurück', '/Transfer/Untis/Import', new ChevronLeft()));
        $tblUntisImportLectureshipList = Import::useService()->getUntisImportLectureshipByAccount();
        $TableContent = array();
        $tblYear = false;
        if ($tblUntisImportLectureshipList) {
            array_walk($tblUntisImportLectureshipList, function (TblUntisImportLectureship $tblUntisImportLectureship)
            use (&$TableContent, &$tblYear, $MissingInfo) {

                $ImportError = 0;

                $Item['FileDivision'] = $tblUntisImportLectureship->getSchoolClass();
                $Item['AppDivision'] = new Warning('Keine Klasse hinterlegt');
                $Item['FileTeacher'] = $tblUntisImportLectureship->getTeacherAcronym();
                $Item['AppTeacher'] = new Warning('Kein Lehrer');
                $Item['FileSubject'] = $tblUntisImportLectureship->getSubjectName();
                $Item['AppSubject'] = new Warning('Kein Fach');
                $Item['FileSubjectGroup'] = $tblUntisImportLectureship->getSubjectGroupName();
                $Item['AppSubjectGroup'] = $tblUntisImportLectureship->getSubjectGroup();
                $Item['Option'] = new Standard('', '/Transfer/Untis/Import/Lectureship/Edit'
                    , new Edit(), array('Id' => $tblUntisImportLectureship->getId(), 'MissingInfo' => $MissingInfo), 'Importvorbereitung bearbeiten');

                if (!$tblYear && $tblUntisImportLectureship->getServiceTblYear()) {
                    $tblYear = $tblUntisImportLectureship->getServiceTblYear();
                }
                if (( $tblDivision = $tblUntisImportLectureship->getServiceTblDivision() )) {
                    $Item['AppDivision'] = $tblDivision->getDisplayName();
                } else {
                    $ImportError++;
                }
                if (( $tblTeacher = $tblUntisImportLectureship->getServiceTblTeacher() )) {
                    $Item['AppTeacher'] = $tblTeacher->getAcronym().' - '.
                        ( ( $tblPerson = $tblTeacher->getServiceTblPerson() ) ? $tblPerson->getFullName() : 'Fehlende Person' );
                } else {
                    $ImportError++;
                }
                if (( $tblSubject = $tblUntisImportLectureship->getServiceTblSubject() )) {
                    $Item['AppSubject'] = $tblSubject->getAcronym().' - '.$tblSubject->getName();
                } else {
                    $ImportError++;
                }
//                // not empty SubjectGroup by import file
//                if ($tblUntisImportLectureship->getSubjectGroupName() !== '') {
//                    $Item['AppSubjectGroup'] = new Warning('Keine Gruppe');
//                    $ImportError++;
//                }
//                // found SubjectGroup
//                if (( $tblSubjectGroup = $tblUntisImportLectureship->getSubjectGroup() )) {
//                    $Item['AppSubjectGroup'] = $tblSubjectGroup->getName().' '.new Small(new Muted($tblSubjectGroup->getDescription()));
//                    $ImportError--;
//                }
                // no import by Warning
                if ($ImportError >= 1) {
                    $Item['Ignore'] = new Danger(new Ban());
                } else {
                    // manual no Import
                    if ($tblUntisImportLectureship->getIsIgnore()) {
                        $Item['Ignore'] = new Danger(new Disable());
                        $ImportError++;
                    } else {
                        $Item['Ignore'] = new Success(new SuccessIcon());
                    }
                }
                if ($Item['Ignore'] == new Success(new SuccessIcon())) {
                    $Item['Option'] .= new Standard('', '/Transfer/Untis/Import/Lectureship/Ignore', new Disable(),
                        array('Id' => $tblUntisImportLectureship->getId()), 'Manuell sperren');
                }

                if (!$MissingInfo) {
                    array_push($TableContent, $Item);
                } else {
                    // only rows they need update
                    if ($ImportError >= 1) {
                        array_push($TableContent, $Item);
                    }
                }
            });
        }

        $HeaderPanel = '';
        /** @var TblYear $tblYear */
        if ($tblYear) {
            $HeaderPanel = new Panel('Importvorbereitung', 'für das Schuljahr: '.$tblYear->getDisplayName(), Panel::PANEL_TYPE_SUCCESS);
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $HeaderPanel
                        ),
                        new LayoutColumn(
                            new Standard(( !$MissingInfo ? new Info(new Bold('Alle')) : 'Alle' ),
                                '/Transfer/Untis/Import/Lectureship/Show', new Listing())
                            .new Standard(( $MissingInfo ? new Info(new Bold('Fehler')) : 'Fehler' ),
                                '/Transfer/Untis/Import/Lectureship/Show', new EyeOpen()
                                , array('MissingInfo' => true))
                        ),
                        new LayoutColumn(
                            new TableData($TableContent, new Title(( !$MissingInfo ? 'Alle Datensätze' : 'korrekturbedürftige Datensätze' )),
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
                                )
                            )
                        ),
                        new LayoutColumn(
                            new PrimaryLink('Import', '', new Save())
                        )
                    ))
                )
            )
        );

        return $Stage;
    }

    public function frontendLectureshipEdit($Id = null, $Data = null, $MissingInfo = false)
    {
        $Stage = new Stage('Lehrauftrag', 'Bearbeiten');
        $tblUntisImportLectureship = ( $Id !== null ? Import::useService()->getUntisImportLectureshipById($Id) : false );
        if (!$tblUntisImportLectureship) {
            $Stage->setContent(new WarningMessage('Lehrauftrag nicht gefunden.')
                .new Redirect('/Transfer/Untis/Import/Lectureship/Show', Redirect::TIMEOUT_ERROR));
            return $Stage;
        }

        $tblYear = $tblUntisImportLectureship->getServiceTblYear();
        if (!$tblYear) {
            $Stage->setContent(new WarningMessage('Schuljahr nicht gefunden. Dies erfordert einen erneuten Import')
                .new Redirect('/Transfer/Untis/Import/Lectureship/Show', Redirect::TIMEOUT_ERROR));
            return $Stage;
        }

        $Stage->addButton(new Standard('Zurück', '/Transfer/Untis/Import/Lectureship/Show', new ChevronLeft(),
            array('MissingInfo' => $MissingInfo)));

        $Global = $this->getGlobal();
        if ($Data === null) {
            if (( $tblDivision = $tblUntisImportLectureship->getServiceTblDivision() )) {
                $Global->POST['Data']['DivisionId'] = $tblDivision->getId();
            }
            if (( $tblTeacher = $tblUntisImportLectureship->getServiceTblTeacher() )) {
                $Global->POST['Data']['TeacherId'] = $tblDivision->getId();
            }
            if (( $tblSubject = $tblUntisImportLectureship->getServiceTblSubject() )) {
                $Global->POST['Data']['SubjectId'] = $tblSubject->getId();
            }
            if (( $SubjectGroup = $tblUntisImportLectureship->getSubjectGroup() )) {
                $Global->POST['Data']['SubjectGroup'] = $SubjectGroup;
            }
            if (( $IsIgnore = $tblUntisImportLectureship->getIsIgnore() )) {
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
                                'Die verfügbare Klassenauswahl begrenzt sich auf dieses Schuljahr', Panel::PANEL_TYPE_SUCCESS)
                        ),
                        new LayoutColumn(
                            new TitleLayout('Daten', 'aus dem Import:')
                        ),
                        new LayoutColumn(
                            new Panel('Klasse:',
                                $tblUntisImportLectureship->getSchoolClass(), Panel::PANEL_TYPE_SUCCESS)
                            , 3),
                        new LayoutColumn(
                            new Panel('Lehrer:',
                                $tblUntisImportLectureship->getTeacherAcronym(), Panel::PANEL_TYPE_SUCCESS)
                            , 3),
                        new LayoutColumn(
                            new Panel('Fach:',
                                $tblUntisImportLectureship->getSubjectName(), Panel::PANEL_TYPE_SUCCESS)
                            , 3),
                        new LayoutColumn(
                            new Panel('Gruppe:',
                                $tblUntisImportLectureship->getSubjectGroupName(), Panel::PANEL_TYPE_SUCCESS)
                            , 3),
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Import::useService()->updateUntisImportLectureship(
                                    $Form, $tblUntisImportLectureship, $Data, $MissingInfo
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
        $tblDivisionList = ( $tblDivisionList ? $tblDivisionList : array() );
        $tblTeacherList = Teacher::useService()->getTeacherAll();
        $tblTeacherList = ( $tblTeacherList ? $tblTeacherList : array() );
        $tblSubjectList = Subject::useService()->getSubjectAll();
        $tblSubjectList = ( $tblSubjectList ? $tblSubjectList : array() );
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
                            new SelectBox('Data[SubjectId]', '', array('{{ Acronym }} - {{ Name }}' => $tblSubjectList)),
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

    public function frontendLectureshipDestroy($Confirm = false)
    {

        $Stage = new Stage('Importvorbereitung', 'Leeren');
        $Stage->setMessage('Hierbei werden alle noch nicht importierte Daten der letzten Importvorbereitung gelöscht.');
        $tblUntisImportLectureshipList = Import::useService()->getUntisImportLectureshipByAccount();
        if (!$tblUntisImportLectureshipList) {
            $Stage->setContent(new Warning('Keine Restdaten eines Import\s vorhanden'));
            return $Stage.new Redirect('/Transfer/Untis/Import', Redirect::TIMEOUT_ERROR);
        }
        if (!$Confirm) {

            $Stage->addButton(new Standard('Zurück', '/Transfer/Untis/Import', new ChevronLeft()));
            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(new Question().'Verbleibende Importvorbereitung der Lehraufträge wirklich löschen? '
                        .new Muted(new Small('Anzahl Datensätze: "<b>'.count($tblUntisImportLectureshipList).'</b>"')),
                        '',
                        Panel::PANEL_TYPE_DANGER,
                        new Standard(
                            'Ja', '/Transfer/Untis/Import/Lectureship/Destroy', new Ok(),
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
                        new LayoutRow(new LayoutColumn(array(
                            ( Import::useService()->destroyUntisImportLectureship()
                                ? new SuccessMessage('Der Import ist nun leer')
                                .new Redirect('/Transfer/Untis/Import', Redirect::TIMEOUT_SUCCESS)
                                : new WarningMessage('Der Import konnte nicht vollständig gelöscht werden')
                                .new Redirect('/Transfer/Untis/Import', Redirect::TIMEOUT_ERROR)
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
        $tblUntisImportLectureship = Import::useService()->getUntisImportLectureshipById($Id);
        if ($tblUntisImportLectureship) {
            Import::useService()->updateUntisImportLectureshipIsIgnore($tblUntisImportLectureship);
            $Stage->setContent(new SuccessMessage('Import wird nun manuell verhindert.')
                .new Redirect('/Transfer/Untis/Import/Lectureship/Show', Redirect::TIMEOUT_SUCCESS));
        } else {
            $Stage->setContent(new DangerMessage('Datensatz nicht gefunden')
                .new Redirect('/Transfer/Untis/Import/Lectureship/Show', Redirect::TIMEOUT_ERROR));
        }
        return $Stage;
    }
}