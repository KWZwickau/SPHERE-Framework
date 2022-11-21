<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\History;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;

abstract class FrontendTest extends FrontendTeacherGroup
{
    /**
     * @param $TestId
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     *
     * @return string
     */
    public function loadViewTestEditContent($DivisionCourseId, $SubjectId, $Filter, $TestId): string
    {
        return $this->getTestEdit(
            $this->formTest($DivisionCourseId, $SubjectId, $Filter, $TestId, true),
            $DivisionCourseId, $SubjectId, $Filter, $TestId
        );
    }

    /**
     * @param $form
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     * @param $TestId
     *
     * @return string
     */
    public function getTestEdit($form, $DivisionCourseId, $SubjectId, $Filter, $TestId = null): string
    {
        $textSubject = '';
        if (($tblSubject = Subject::useService()->getSubjectById($SubjectId))) {
            $textSubject = new Bold($tblSubject->getDisplayName());
        }
        $title = $TestId ? new Edit() . " Leistungsüberprüfung bearbeiten" : new Plus() . " Leistungsüberprüfung hinzufügen";

        return new Title(
                (new Standard("Zurück", ApiGradeBook::getEndpoint(), new ChevronLeft()))
                    ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewGradeBookContent($DivisionCourseId, $SubjectId, $Filter))
                . "&nbsp;&nbsp;&nbsp;&nbsp; $title" . new Muted(new Small(" im Fach: ")) . $textSubject
            )
            . new Well($form);
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     * @param null $TestId
     * @param bool $setPost
     * @param null $Data
     *
     * @return Form
     */
    public function formTest($DivisionCourseId, $SubjectId, $Filter, $TestId = null, bool $setPost = false, $Data = null): Form
    {
        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        $tblTest = Grade::useService()->getTestById($TestId);
        if ($setPost) {
            $Global = $this->getGlobal();
            if ($tblTest) {
                $Global->POST['Data']['GradeType'] = ($tblGradeType = $tblTest->getTblGradeType()) ? $tblGradeType->getId() : 0;
                $Global->POST['Data']['Description'] = $tblTest->getDescription();
                $Global->POST['Data']['IsContinues'] = $tblTest->getIsContinues();
                $Global->POST['Data']['FinishDate'] = $tblTest->getFinishDateString();
                $Global->POST['Data']['Date'] = $tblTest->getDateString();
                $Global->POST['Data']['CorrectionDate'] = $tblTest->getCorrectionDateString();
                $Global->POST['Data']['ReturnDate'] = $tblTest->getReturnDateString();
                if (($tblDivisionCourseList = $tblTest->getDivisionCourses())) {
                    foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                        $Global->POST['Data']['DivisionCourses'][$tblDivisionCourse->getId()] = 1;
                    }
                }
            } else {
                $Global->POST['Data']['DivisionCourses'][$DivisionCourseId] = 1;
            }

            $Global->savePost();
        }

        $tblGradeTypeList = Grade::useService()->getGradeTypeList();

        return (new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Data[GradeType]', 'Zensuren-Typ', array('DisplayName' => $tblGradeTypeList)))->setRequired()
                    , 3),
                new FormColumn(
                    new TextField('Data[Description]', '', 'Beschreibung', new Pen())
                    , 9),
            )),
            new FormRow(array(
                new FormColumn(
                    new CheckBox('Data[IsContinues]', new Bold('fortlaufendes Datum '.
                        new ToolTip(new InfoIcon(), "Bei Tests mit 'fortlaufendes Datum' 
                        erfolgt die Freigabe für die Notenübersicht (Eltern, Schüler) automatisch, sobald das Datum der 
                        jeweiligen Note (Prio1) oder das optionale Enddatum (Prio2) erreicht ist.")
                        .'(z.B. für Mündliche Noten)'
                    ), 1,
                        array(
                            'Data[FinishDate]',
                            'Data[Date]',
                            'Data[CorrectionDate]',
                            'Data[ReturnDate]'
                        ))
                ),
                new FormColumn(
                    (new DatePicker('Data[FinishDate]', '', 'Enddatum (optional für Notendatum)', new Calendar()))->setDisabled(), 3
                ),
                new FormColumn(
                    new DatePicker('Data[Date]', '', 'Datum', new Calendar()), 3
                ),
                new FormColumn(
                    new DatePicker('Data[CorrectionDate]', '', 'Korrekturdatum', new Calendar()), 3
                ),
                new FormColumn(
                    new DatePicker('Data[ReturnDate]', '', 'Bekanntgabedatum für Notenübersicht (Eltern, Schüler)',
                        new Calendar()), 3
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    $this->getDivisionCoursesSelectContent($SubjectId)
                )
            )),
            new FormRow(array(
                new FormColumn(
                    ApiGradeBook::receiverBlock($this->loadTestPlanning($SubjectId, $TestId, $Data), 'TestPlanningContent')
                )
            )),
            new FormRow(array(
                new FormColumn(array(
                    (new Primary('Speichern', ApiGradeBook::getEndpoint(), new Save()))
                        ->ajaxPipelineOnClick(ApiGradeBook::pipelineSaveTestEdit($DivisionCourseId, $SubjectId, $Filter, $TestId)),
                    (new Standard('Abbrechen', ApiGradeBook::getEndpoint(), new Disable()))
                        ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewGradeBookContent($DivisionCourseId, $SubjectId, $Filter))
                ))
            ))
        ))))->disableSubmitAction();
    }

    /**
     * @param $SubjectId
     *
     * @return Layout|Warning
     */
    public function getDivisionCoursesSelectContent($SubjectId)
    {
        if (($tblSubject = Subject::useService()->getSubjectById($SubjectId))
            && ($tblPerson = Account::useService()->getPersonByLogin())
            && ($tblYear = Grade::useService()->getYear())
            && ($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPerson, null, $tblSubject))
        ) {
            $size = 3;
            $columnList = array();
            $contentPanelList = array();

            // Lehraufträge
            $tblTeacherLectureshipList = $this->getSorter($tblTeacherLectureshipList)->sortObjectBy('Sort');
            /** @var TblTeacherLectureship $tblTeacherLectureship */
            foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                if (($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())) {
                    $contentPanelList[$tblDivisionCourse->getType()->getId()][]
                        = new CheckBox("Data[DivisionCourses][{$tblDivisionCourse->getId()}]", $tblDivisionCourse->getDisplayName(), 1);
                }
            }

            // eigene Lerngruppen
            if (($teacherGroupList = DivisionCourse::useService()->getTeacherGroupListByTeacherAndYear($tblPerson, $tblYear, $tblSubject))) {
                foreach ($teacherGroupList as $tblDivisionCourse) {
                    $contentPanelList[$tblDivisionCourse->getType()->getId()][]
                        = new CheckBox("Data[DivisionCourses][{$tblDivisionCourse->getId()}]", $tblDivisionCourse->getDisplayName(), 1);
                }
            }

            ksort($contentPanelList);
            foreach ($contentPanelList as $typeId => $content) {
                if (($tblDivisionCourseType = DivisionCourse::useService()->getDivisionCourseTypeById($typeId))) {
                    $columnList[] = new LayoutColumn(new Panel($tblDivisionCourseType->getName(), $content, Panel::PANEL_TYPE_INFO), $size);
                }
            }

            return new Layout(new LayoutGroup(
                Grade::useService()->getLayoutRowsByLayoutColumnList($columnList, $size),
                new Title("Kurs-Auswahl")
            ));
        }

        return new Warning('Keine entsprechenden Lehraufträge gefunden', new Exclamation());
    }

    /**
     * @param $SubjectId
     * @param $TestId
     * @param $Data
     *
     * @return string
     */
    public function loadTestPlanning($SubjectId, $TestId, $Data = null): string
    {
        return new Title(new History() . ' Planung');
    }
}