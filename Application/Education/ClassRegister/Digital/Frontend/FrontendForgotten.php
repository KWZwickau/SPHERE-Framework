<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Frontend;

use DateTime;
use SPHERE\Application\Api\Education\ClassRegister\ApiForgotten;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Person;
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
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\History;
use SPHERE\Common\Frontend\Icon\Repository\Pen;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Strikethrough;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

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
     * @param null $LessonContentId
     * @param null $CourseContentId
     *
     * @return string
     */
    public function loadHomeworkSelectBox(
        TblDivisionCourse $tblDivisionCourse,
        ?TblSubject $tblSubject,
        ?DateTime $dateTime = null,
        $LessonContentId = null,
        $CourseContentId = null,
    ): string {
        if (($list = Digital::useService()->getDueDateHomeworkListBySubject($tblDivisionCourse, $tblSubject ?: null, $dateTime))) {
            if ($LessonContentId || $CourseContentId) {
                $Global = $this->getGlobal();
                $Global->POST['Data']['LessonContentId'] = $LessonContentId;
                $Global->POST['Data']['CourseContentId'] = $CourseContentId;
                $Global->savePost();
            }

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
     * @param null $Filter
     * @param null $ForgottenId
     * @param bool $setPost
     * @param string|null $Date
     * @param string|null $SubjectId
     * @param string|null $LessonContentId
     * @param string|null $CourseContentId
     *
     * @return Form
     */
    public function formForgotten(TblDivisionCourse $tblDivisionCourse, $Filter = null, $ForgottenId = null, bool $setPost = false,
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

            //  muss direkt an der Select-Box gepostet werden
            $LessonContentId = ($tblLessonContent = $tblForgotten->getTblLessonContent()) ? $tblLessonContent->getId() : null;
            $CourseContentId = ($tblCourseContent = $tblForgotten->getTblCourseContent()) ? $tblCourseContent->getId() : null;

            $Global->POST['Data']['Remark'] = $tblForgotten->getRemark();
            if (($tblForgottenStudentList = $tblForgotten->getForgottenStudents())) {
                foreach ($tblForgottenStudentList as $tblForgottenStudent) {
                    if (($tblPersonTemp = $tblForgottenStudent->getServiceTblPerson())) {
                        $Global->POST['Data']['Students'][$tblPersonTemp->getId()] = 1;
                    }
                }
            }

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
                ->ajaxPipelineOnClick(ApiForgotten::pipelineEditForgottenSave($ForgottenId, $Filter));
        } else {
            $saveButton = (new Primary('Speichern', ApiForgotten::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiForgotten::pipelineCreateForgottenSave($tblDivisionCourse->getId(), $Filter));
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
            ->ajaxPipelineOnChange(ApiForgotten::pipelineLoadHomeworkSelectBox($tblDivisionCourse->getId(), $SubjectId, $Date, $LessonContentId, $CourseContentId));

        // Kursheft hat bereits ein Fach
        if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
            $formRow = new FormRow(new FormColumn($datePicker));
            $contentSelectBox = $this->loadHomeworkSelectBox($tblDivisionCourse, null, $Date ? new DateTime($Date) : null, $LessonContentId, $CourseContentId);
        } else {
            $formRow = new FormRow(array(
                new FormColumn(
                    $datePicker
                    , 6),
                new FormColumn(
                    (new SelectBox('Data[serviceTblSubject]', 'Fach', array('{{ Acronym }} - {{ Name }}' => $tblSubjectList)))
                        ->setRequired()
                        ->ajaxPipelineOnChange(ApiForgotten::pipelineLoadHomeworkSelectBox($tblDivisionCourse->getId(), $SubjectId, $Date, $LessonContentId, $CourseContentId))
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

    /**
     * @param null $DivisionCourseId
     * @param null $BackDivisionCourseId
     * @param string $BasicRoute
     * @param string $View
     * @param null $Filter
     *
     * @return Stage|string
     */
    public function frontendForgotten(
        $DivisionCourseId = null,
        $BackDivisionCourseId = null,
        string $BasicRoute = '/Education/ClassRegister/Digital/Teacher',
        string $View = 'ForgottenOverview',
        $Filter = null
    ): string|Stage {
        $stage = new Stage('Digitales Klassenbuch', 'Vergessene Arbeitsmittel/Hausaufgaben');

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $stage->addButton(Digital::useFrontend()->getBackButton($tblDivisionCourse, $BackDivisionCourseId, $BasicRoute));

            if ($View == 'ForgottenOverview') {
                $content = ApiForgotten::receiverModal()
                    . new Panel(new Filter() . ' Filter', $this->formFilter($tblDivisionCourse), Panel::PANEL_TYPE_INFO)
                    . ApiForgotten::receiverBlock($this->loadForgottenTable($tblDivisionCourse, $Filter), 'ForgottenContent');

                $button = new Standard('Zur Schüleransicht wechseln', '/Education/ClassRegister/Digital/Forgotten', new PersonGroup(), array(
                    'DivisionCourseId' => $DivisionCourseId,
                    'BachDivisionCourseId' => $BackDivisionCourseId,
                    'BasicRoute' => $BasicRoute,
                    'View' => 'Student',
                    'Filter' => $Filter
                ));
            } else {
                $content = $this->loadForgottenStudentOverviewTable($tblDivisionCourse);

                $button = new Standard('Zur Vergessene Arbeitsmittel/Hausaufgaben-Übersicht wechseln', '/Education/ClassRegister/Digital/Forgotten', new History(), array(
                    'DivisionCourseId' => $DivisionCourseId,
                    'BachDivisionCourseId' => $BackDivisionCourseId,
                    'BasicRoute' => $BasicRoute,
                    'View' => 'ForgottenOverview',
                    'Filter' => $Filter
                ));
            }

            $stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        Digital::useService()->getHeadLayoutRow($tblDivisionCourse),
                        $tblDivisionCourse->getType()->getIsCourseSystem()
                            ? Digital::useService()->getHeadButtonListLayoutRowForCourseSystem($tblDivisionCourse, '/Education/ClassRegister/Digital/Forgotten',
                            $BasicRoute, $BackDivisionCourseId)
                            : Digital::useService()->getHeadButtonListLayoutRow($tblDivisionCourse, '/Education/ClassRegister/Digital/Forgotten', $BasicRoute)
                    )),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        $content
                    )), new \SPHERE\Common\Frontend\Layout\Repository\Title(new PullClear(new History() . ' Vergessene Arbeitsmittel/Hausaufgaben' . new PullRight($button))))
                ))
            );
        } else {
            return new Danger('Klasse oder Gruppe nicht gefunden', new Exclamation())
                . new Redirect($BasicRoute, Redirect::TIMEOUT_ERROR);
        }

        return $stage;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return Form
     */
    public function formFilter(TblDivisionCourse $tblDivisionCourse): Form
    {
        $tblSubjectList = Subject::useService()->getSubjectAll();
        $tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses(true);

        $selectBoxStudents =  (new SelectBox('Filter[PersonId]', 'Schüler', array('{{ LastFirstName }}' => $tblPersonList)))
            ->ajaxPipelineOnChange(ApiForgotten::pipelineLoadForgottenContent($tblDivisionCourse->getId()));

        // Kursheft hat bereits ein Fach
        if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
            $formRow = new FormRow(array(
                new FormColumn(
                    $selectBoxStudents
                ),
            ));
        } else {
            $formRow = new FormRow(array(
                new FormColumn(
                    (new SelectBox('Filter[SubjectId]', 'Fach', array('{{ DisplayName }}' => $tblSubjectList)))
                        ->ajaxPipelineOnChange(ApiForgotten::pipelineLoadForgottenContent($tblDivisionCourse->getId()))
                    , 6),
                new FormColumn(
                    $selectBoxStudents
                    , 6),
            ));
        }

        return new Form(new FormGroup($formRow));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param null $Filter
     *
     * @return string
     */
    public function loadForgottenTable(TblDivisionCourse $tblDivisionCourse, $Filter = null): string
    {
        $tblSubject = isset($Filter['SubjectId']) ? Subject::useService()->getSubjectById($Filter['SubjectId']) : null;
        $tblPerson = isset($Filter['PersonId']) ? Person::useService()->getPersonById($Filter['PersonId']) : null;
        $tblForgottenList = Digital::useService()->getForgottenListBy($tblDivisionCourse, $tblSubject ?: null, $tblPerson ?: null);

        $addLink = (new Primary('Vergessene Arbeitsmittel/Hausaufgaben hinzufügen', ApiForgotten::getEndpoint(), new Plus()))
            ->ajaxPipelineOnClick(ApiForgotten::pipelineOpenCreateForgottenModal($tblDivisionCourse->getId(), (new DateTime('today'))->format('d.m.Y'), $Filter, $tblSubject ? $tblSubject->getId() : null));

        if ($tblForgottenList) {
            $dataList = array();
            foreach ($tblForgottenList as $tblForgotten) {
                $item = array(
                    'Date' => $tblForgotten->getDate(),
                    'Subject' => ($tblSubject = $tblForgotten->getServiceTblSubject()) ? $tblSubject->getAcronym() : '',
                    'Type' => $tblForgotten->getDisplayType(),
                    'Remark' => $tblForgotten->getRemark(),
                    'Students' => $tblForgotten->getDisplayForgottenStudents(),
                    'Option' =>
                        (new Standard('', ApiForgotten::getEndpoint(), new Pen(), array(), 'Vergessene Arbeitsmittel/Hausaufgaben bearbeiten'))
                            ->ajaxPipelineOnClick(ApiForgotten::pipelineOpenEditForgottenModal($tblForgotten->getId(), $Filter))
                        . (new Standard('', ApiForgotten::getEndpoint(), new Remove(), array(), 'Vergessene Arbeitsmittel/Hausaufgaben löschen'))
                            ->ajaxPipelineOnClick(ApiForgotten::pipelineOpenDeleteForgottenModal($tblForgotten->getId(), $Filter))
                );

                $dataList[] = $item;
            }

            $columns = array(
                'Date' => 'Datum',
                'Subject' => 'Fach',
                'Type' => 'Typ',
                'Remark' => 'Bemerkung',
                'Students' => 'Schüler',
                'Option' => '&nbsp;'
            );

            if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
                unset($columns['Subject']);
            }

            return $addLink . new TableData(
                    $dataList,
                    null,
                    $columns,
                    array(
                        'columnDefs' => array(
                            array('type'        => 'de_date', 'targets' => 1),
                            array('searchable'  => false, 'targets' => array(-1, -2)),
                            array('orderable'   => false, 'width' => '60px', 'targets' => -1),
                        ),
                        'order'      => array(array(0, 'desc'), array(1, 'asc')),
                        'responsive' => false,
                        'destroy'    => true
                    )
                );
        }

        return $addLink . '';
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TableData
     */
    public function loadForgottenStudentOverviewTable(TblDivisionCourse $tblDivisionCourse): TableData
    {
        $dataList = [];
        if (($tblDivisionCourseMemberList = $tblDivisionCourse->getStudentsWithSubCourses(true, false))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $count = 0;
            foreach ($tblDivisionCourseMemberList as $tblDivisionCourseMember) {
                if (($tblPerson = $tblDivisionCourseMember->getServiceTblPerson())) {
                    $count++;
                    $sumHomework = Digital::useService()->getForgottenSumByPersonAndYear($tblPerson, $tblYear, true);
                    $sumEquipment = Digital::useService()->getForgottenSumByPersonAndYear($tblPerson, $tblYear, false);
                    $sumTotal = $sumHomework + $sumEquipment; //Digital::useService()->getForgottenSumByPersonAndYear($tblPerson, $tblYear, null);
                    $dataList[] = array(
                        'Number' => $tblDivisionCourseMember->isInActive() ? new Strikethrough($count) : $count,
                        'Name' => $tblDivisionCourseMember->isInActive() ? new Strikethrough($tblPerson->getLastFirstNameWithCallNameUnderline()) : $tblPerson->getLastFirstNameWithCallNameUnderline(),
                        'SumHomework' => $tblDivisionCourseMember->isInActive() ? new Strikethrough( $sumHomework . ' ') :  $sumHomework . ' ',
                        'SumEquipment' => $tblDivisionCourseMember->isInActive() ? new Strikethrough( $sumEquipment . ' ') :  $sumEquipment . ' ',
                        'SumTotal' => $tblDivisionCourseMember->isInActive() ? new Strikethrough( $sumTotal . ' ') :  $sumTotal . ' ',
                    );
                }
            }
        }

        $columns = array(
            'Number' => '#',
            'Name' => 'Name',
            'SumHomework' => 'Summe Vergessene Hausaufgaben',
            'SumEquipment' => 'Summe Vergessene Arbeitsmittel',
            'SumTotal' => 'Gesamtsumme'
        );

        return new TableData(
            $dataList,
            null,
            $columns,
            array(
                'order'      => array(array(0, 'asc')),
                'responsive' => false,
            )
        );
    }
}