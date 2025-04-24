<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Frontend;

use DateTime;
use SPHERE\Application\Api\Education\ClassRegister\ApiForgotten;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Text\Repository\Bold;

class FrontendForgotten extends FrontendCourseContent
{
    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param ?TblSubject $tblSubject
     * @param DateTime|null $date
     *
     * @return string
     */
    public function loadDueDateHomeworkListBySubject(
        TblDivisionCourse $tblDivisionCourse,
        ?TblSubject $tblSubject,
        ?DateTime $date = null,
    ): string {
        $limit = 3;
        $content = '';
        if (($list = Digital::useService()->getDueDateHomeworkListBySubject($tblDivisionCourse, $tblSubject, $date, $limit))) {
            $contentList = [];
            foreach ($list as $item) {
                $dueDate = new DateTime($item['DueDateHomework']);
                $isBold = $date == $dueDate;
                $contentList[] = new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        $isBold ? new Bold($item['DueDateHomework']) : $item['DueDateHomework']
                        , 2),
                    new LayoutColumn(
                        $isBold ? new Bold($item['Homework']) : $item['Homework']
                        , 10)
                ))));
            }
            $content = new Panel('Letzte fällige Hausaufgaben (Maximal: ' . $limit . ')', $contentList);
        }

        return $content;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject|null $tblSubject
     * @param DateTime|null $dateTime
     *
     * @return string
     */
    public function loadHomeworkSelectBox(
        TblDivisionCourse $tblDivisionCourse,
        ?TblSubject $tblSubject,
        ?DateTime $dateTime = null,
    ): string {
        if (($list = Digital::useService()->getDueDateHomeworkListBySubject($tblDivisionCourse, $tblSubject ?: null, $dateTime))) {
            $homeworks[] = new SelectBoxItem(0, '');
            foreach ($list as $item) {
                $text = ($item['DueDateHomework'] ? $item['DueDateHomework'] . ' - ' : '') . $item['Homework'];
                $homeworks[] = new SelectBoxItem($item['Id'], $text);
            }

            $name = $tblDivisionCourse->getType()->getIsCourseSystem() ? 'Data[CourseContentId]' : 'Data[LessonContentId]';

            return new SelectBox($name, 'Optional Hausaufgabe auswählen', array('{{ Name }}' => $homeworks), null, true, null);
        }

        return '';
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param null $ForgottenId
     * @param bool $setPost
     * @param string|null $Date
     * @param string|null $SubjectId
     * @param string|null $LessonContentId
     * @param string|null $CourseContentId
     *
     * @return Form
     */
    public function formForgotten(TblDivisionCourse $tblDivisionCourse, $ForgottenId = null, bool $setPost = false,
        string $Date = null, string $SubjectId = null, string $LessonContentId = null, string $CourseContentId = null): Form
    {
        $tblSubjectList = Subject::useService()->getSubjectAll();

        // beim Checken der Input-Felder darf der Post nicht gesetzt werden
        if ($setPost && $ForgottenId
            && ($tblForgotten = Digital::useService()->getForgottenById($ForgottenId))
        ) {
            $Global = $this->getGlobal();
            $Global->POST['Data']['Date'] = $tblForgotten->getDate();
            $Global->POST['Data']['serviceTblSubject'] = ($tblSubject = $tblForgotten->getServiceTblSubject()) ? $tblSubject->getId() : 0;
            $Global->POST['Data']['Remark'] = $tblForgotten->getRemark();
            $Global->savePost();

            // deaktiviertes Fach hinzufügen
            if ($tblSubject && !$tblSubject->getIsActive()) {
                $tblSubjectList[] = $tblSubject;
            }
            // Datum
            $Date = $tblForgotten->getDate();
        } elseif ($Date || $SubjectId || $LessonContentId || $CourseContentId) {
            // hinzufügen mit Startwerten
            $Global = $this->getGlobal();
            $Global->POST['Data']['Date'] = $Date;
            $Global->POST['Data']['serviceTblSubject'] = $SubjectId;
            $Global->POST['Data']['LessonContentId'] = $LessonContentId;
            $Global->POST['Data']['CourseContentId'] = $CourseContentId;
            $Global->savePost();
        }

        if ($ForgottenId) {
            $saveButton = (new Primary('Speichern', ApiForgotten::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiForgotten::pipelineEditForgottenSave($ForgottenId));
        } else {
            $saveButton = (new Primary('Speichern', ApiForgotten::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiForgotten::pipelineCreateForgottenSave($tblDivisionCourse->getId()));
        }
        $buttonList[] = $saveButton;

        // Schüler panel abhängig vom Fach laden?
        $columns = array();
        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
            foreach ($tblPersonList as $tblPerson) {
                $columns[$tblPerson->getId()] = new FormColumn(new CheckBox('Data[Students][' . $tblPerson->getId() . ']',
                    $tblPerson->getLastFirstNameWithCallNameUnderline(), 1), 4);
            }
        }

        $datePicker = (new DatePicker('Data[Date]', '', 'Datum', new Calendar()))
            ->setRequired()
            ->ajaxPipelineOnChange(ApiForgotten::pipelineLoadHomeworkSelectBox($tblDivisionCourse->getId(), $SubjectId, $Date));

        // Kursheft hat bereits ein Fach
        if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
            $formRow = new FormRow(new FormColumn($datePicker));
            $contentSelectBox = $this->loadHomeworkSelectBox($tblDivisionCourse, null, $Date ? new DateTime($Date) : null);
        } else {
            $formRow = new FormRow(array(
                new FormColumn(
                    $datePicker
                    , 6),
                new FormColumn(
                    (new SelectBox('Data[serviceTblSubject]', 'Fach', array('{{ Acronym }} - {{ Name }}' => $tblSubjectList)))
                        ->setRequired()
                        ->ajaxPipelineOnChange(ApiForgotten::pipelineLoadHomeworkSelectBox($tblDivisionCourse->getId(), $SubjectId, $Date))
                    , 6),
            ));
            $contentSelectBox = '';
        }

        return (new Form(array(
            new FormGroup(array(
                $formRow,
                // Hausaufgaben laden zur Auswahl als selectBox abhängig vom ausgewählten Fach
                new FormRow(array(
                    new FormColumn(
                        ApiForgotten::receiverBlock($contentSelectBox, 'HomeworkSelectBox')
                    ),
                )),
                new FormRow(array(
                    new FormColumn(
                        new TextArea('Data[Remark]', 'Bemerkung', 'Bemerkung', new Edit())
                    ),
                )),
            )),
            new FormGroup(array(
                new FormRow(
                    $columns,
                ),
                new FormRow(array(
                    new FormColumn(
                        $buttonList
                    )
                )),
            ), new Title('Schüler mit vergessenen Arbeitsmittel/Hausaufgaben'))
        )))->disableSubmitAction();
    }
}