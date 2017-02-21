<?php
namespace SPHERE\Application\Transfer\Untis\Import;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Search\Group\Group;
use SPHERE\Application\Transfer\Untis\Import\Service\Entity\TblUntisImportLectureship;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
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
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
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
                $Item['AppTeacher'] = new Warning('Kein Lehrer Gefunden/Ausgewählt');
                $Item['FileSubject'] = $tblUntisImportLectureship->getSubjectName();
                $Item['AppSubject'] = new Warning('Kein Fach Gefunden/Ausgewählt');
                $Item['FileSubjectGroup'] = $tblUntisImportLectureship->getGroupName();
                $Item['AppSubjectGroup'] = '';
                $Item['Option'] = new Standard('', '/Transfer/Untis/Import/Lectureship/Edit'
                    , new Edit(), array('Id' => $tblUntisImportLectureship->getId()));

                if (!$tblYear && $tblUntisImportLectureship->getServiceTblYear()) {
                    $tblYear = $tblUntisImportLectureship->getServiceTblYear();
                }
                if (( $tblDivision = $tblUntisImportLectureship->getServiceTblDivision() )) {
                    $Item['AppDivision'] = $tblDivision->getDisplayName();
                } else {
                    $ImportError++;
                }
                if (( $tblPerson = $tblUntisImportLectureship->getServiceTblPerson() )) {
                    $Item['AppTeacher'] = $tblPerson->getFullName();
                } else {
                    $ImportError++;
                }
                if (( $tblSubject = $tblUntisImportLectureship->getServiceTblSubject() )) {
                    $Item['AppSubject'] = $tblSubject->getAcronym().' - '.$tblSubject->getName();
                } else {
                    $ImportError++;
                }
                // not empty SubjectGroup by import file
                if ($tblUntisImportLectureship->getGroupName() !== '') {
                    $Item['AppSubjectGroup'] = new Warning('Keine Gruppe Gefunden/Ausgewählt');
                    $ImportError++;
                }
                // found SubjectGroup
                if (( $tblSubjectGroup = $tblUntisImportLectureship->getServiceTblSubjectGroup() )) {
                    $Item['AppSubjectGroup'] = $tblSubjectGroup->getName().' '.new Small(new Muted($tblSubjectGroup->getDescription()));
                    $ImportError--;
                }
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
            $HeaderPanel = new Panel('Import', 'für das Schuljahr: '.$tblYear->getDisplayName(), Panel::PANEL_TYPE_SUCCESS);
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
                        )
                    ))
                )
            )
        );

        return $Stage;
    }

    public function frontendLectureshipEdit($Id = null, $Data = null)
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

        $Global = $this->getGlobal();
        if ($Data === null) {
            if (( $tblDivision = $tblUntisImportLectureship->getServiceTblDivision() )) {
                $Global->POST['Data']['DivisionId'] = $tblDivision->getId();
            }
            if (( $tblPerson = $tblUntisImportLectureship->getServiceTblPerson() )) {
                $Global->POST['Data']['PersonId'] = $tblDivision->getId();
            }
            if (( $tblSubject = $tblUntisImportLectureship->getServiceTblSubject() )) {
                $Global->POST['Data']['SubjectId'] = $tblSubject->getId();
            }
            if (( $tblSubjectGroup = $tblUntisImportLectureship->getServiceTblSubjectGroup() )) {
                $Global->POST['Data']['SubjectGroupId'] = $tblSubjectGroup->getId();
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
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Schuljahr: '.$tblYear->getDisplayName(),
                                'Die verfügbare Klassenauswahl begrenzt sich auf dieses Schuljahr', Panel::PANEL_TYPE_SUCCESS)
                        )
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                $Form
                            )
                        )
                    )
                ))
            )
        );

        return $Stage;
    }

    public function formImport($tblYear)
    {

        $tblDivisionList = Division::useService()->getDivisionByYear($tblYear);
        $tblDivisionList = ( $tblDivisionList ? $tblDivisionList : array() );
        $tblGroup = Group::useService()->getGroupByMetaTable('TEACHER');
        $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
        $tblPersonList = ( $tblPersonList ? $tblPersonList : array() );
        $tblSubjectList = Subject::useService()->getSubjectAll();
//        $tblSubjectList = ($tblSubjectList ? $tblSubjectList : array());
//        $tblSubjectGroup = Division::useService()->($tblDivision, $tblSubject);

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Klasse', new SelectBox('Data[DivisionId]', 'Klasse',
                            array('{{ DisplayName }} - {{ tblLevel.serviceTblType.Name }}' => $tblDivisionList)),
                            Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Klasse', new SelectBox('Data[PersonId]', 'Lehrer', array('FullName' => $tblPersonList)),
                            Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Klasse',
                            new SelectBox('Data[SubjectId]', 'Fach', array('{{ Acronym }} - {{ Name }}' => $tblSubjectList)),
                            Panel::PANEL_TYPE_INFO)
                        , 4),
//                    new FormColumn(
//                        ''
//                    , 4),
//                    new FormColumn(
//                        ''
//                    , 2)
                ))
            )
        );
    }

    public function frontendLectureshipDestroy($Confirm = false)
    {

        $Stage = new Stage('"Restdaten" Import der Lehraufträge', 'Löschen');
        $tblUntisImportLectureshipList = Import::useService()->getUntisImportLectureshipByAccount();
        if (!$tblUntisImportLectureshipList) {
            $Stage->setContent(new Warning('Keine Restdaten eines Import\s vorhanden'));
            return $Stage.new Redirect('/Transfer/Untis', Redirect::TIMEOUT_ERROR);
        }
        if (!$Confirm) {

            $Stage->addButton(new Standard('Zurück', '/Transfer/Untis', new ChevronLeft()));
            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(new Question().' "Restdaten" Import der Lehraufträge (Anzahl Datensätze: "<b>'.
                        count($tblUntisImportLectureshipList).'</b>" wirklich löschen?',
                        '',
                        Panel::PANEL_TYPE_DANGER,
                        new Standard(
                            'Ja', '/Transfer/Untis/Import/Lectureship/Destroy', new Ok(),
                            array('Confirm' => true)
                        )
                        .new Standard(
                            'Nein', '/Transfer/Untis', new Disable()
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
                                .new Redirect('/Transfer/Untis', Redirect::TIMEOUT_SUCCESS)
                                : new WarningMessage('Der Import konnte nicht vollständig gelöscht werden')
                                .new Redirect('/Transfer/Untis', Redirect::TIMEOUT_ERROR)
                            )
                        )))
                    ))
                )
            );
        }
        return $Stage;
    }
}