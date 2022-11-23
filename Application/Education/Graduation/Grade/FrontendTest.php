<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use DateInterval;
use DateTime;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTest;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
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
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

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
                    (new DatePicker('Data[Date]', '', 'Datum', new Calendar()))
                        ->ajaxPipelineOnChange(ApiGradeBook::pipelineLoadTestPlanning())
                    , 3
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
                    ApiGradeBook::receiverBlock($this->loadTestPlanning($Data), 'TestPlanningContent')
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
                        = (new CheckBox("Data[DivisionCourses][{$tblDivisionCourse->getId()}]", $tblDivisionCourse->getDisplayName(), 1))
                            ->ajaxPipelineOnChange(ApiGradeBook::pipelineLoadTestPlanning());
                }
            }

            // eigene Lerngruppen
            if (($teacherGroupList = DivisionCourse::useService()->getTeacherGroupListByTeacherAndYear($tblPerson, $tblYear, $tblSubject))) {
                foreach ($teacherGroupList as $tblDivisionCourse) {
                    $contentPanelList[$tblDivisionCourse->getType()->getId()][]
                        = (new CheckBox("Data[DivisionCourses][{$tblDivisionCourse->getId()}]", $tblDivisionCourse->getDisplayName(), 1))
                            ->ajaxPipelineOnChange(ApiGradeBook::pipelineLoadTestPlanning());
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
     * @param $Data
     *
     * @return string
     */
    public function loadTestPlanning($Data = null): string
    {
        if (isset($Data['Date']) && $Data['Date']
            && isset($Data['DivisionCourses'])
        ) {
            $selectDate = new DateTime($Data['Date']);
            $fromDate = Timetable::useService()->getStartDateOfWeek((new DateTime($selectDate->format('d.m.Y')))->sub(new DateInterval('P7D')));
            $toDate = new DateTime($fromDate->format('d.m.Y'));
            $toDate = $toDate->add(new DateInterval('P20D'));

            $tblDivisionCourseList = array();
            foreach ($Data['DivisionCourses'] as $divisionCourseId => $value) {
                if (($temp = DivisionCourse::useService()->getDivisionCourseById($divisionCourseId))) {
                    $tblDivisionCourseList[$temp->getId()] = $temp;
                    // weitere Kurse der Schüler im Kurs
                    if (($tempList = DivisionCourse::useService()->getDivisionCourseListByStudentsInDivisionCourse($temp))) {
                        foreach ($tempList as $item) {
                            if (!isset($tblDivisionCourseList[$item->getId()])) {
                                $tblDivisionCourseList[$item->getId()] = $item;
                            }
                        }
                    }
                }
            }

            $tblTestList = array();
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                if (($tempTestList = Grade::useService()->getTestListBetween($tblDivisionCourse, $fromDate, $toDate))) {
                    $tblTestList = array_merge($tblTestList, $tempTestList);
                }
            }

            $panelContentList = array();
            if (!empty($tblTestList)) {
                // doppelte bei mehreren Kursen am Test entfernen
                $tblTestList = array_unique($tblTestList);
                $tblTestList = $this->getSorter($tblTestList)->sortObjectBy('Date', new DateTimeSorter());
                /** @var TblTest $tblTest */
                foreach ($tblTestList as $tblTest) {
                    if (($date = $tblTest->getDate())
                        && ($tblSubject = $tblTest->getServiceTblSubject())
                        && ($tblGradeType = $tblTest->getTblGradeType())
                    ) {
                        $week = $date->format('W');
                        if (!isset($panelContentList[$week])) {
                            $panelContentList[$week]['Header'] = $this->getTestPlaningHeader($date, $week);
                        }
                        $panelContentList[$week]['Content'][] = $this->getTestPlaningContent($tblTest, $tblSubject, $tblGradeType);
                    }
                }
            }

            if (!empty($panelContentList)) {
                $columnList = array();
                $size = 4;
                foreach ($panelContentList as $data) {
                    $columnList[] = new LayoutColumn(new Panel(
                        $data['Header'],
                        $data ['Content'],
                        Panel::PANEL_TYPE_DEFAULT
                    ), $size);
                }

                return new Layout(new LayoutGroup(
                    Grade::useService()->getLayoutRowsByLayoutColumnList($columnList, $size),
                    new Title(new History() . ' Planung')
                ));
            }
        }

        return '';
    }

    /**
     * @param DateTime $date
     * @param $week
     *
     * @return string
     */
    private function getTestPlaningHeader(DateTime $date, $week): string
    {
        $year = $date->format('Y');
        $monday = date('d.m.y', strtotime("$year-W{$week}"));
        $friday = date('d.m.y', strtotime("$year-W{$week}-5"));

        return new Bold('KW: ' . $week) . new Muted(' &nbsp;&nbsp;&nbsp;(' . $monday . ' - ' . $friday . ')');
    }

    /**
     * @param TblTest $tblTest
     * @param TblSubject $tblSubject
     * @param TblGradeType $tblGradeType
     *
     * @return string
     */
    private function getTestPlaningContent(TblTest $tblTest, TblSubject $tblSubject, TblGradeType $tblGradeType): string
    {
        $trans = array(
            'Mon' => 'Mo',
            'Tue' => 'Di',
            'Wed' => 'Mi',
            'Thu' => 'Do',
            'Fri' => 'Fr',
            'Sat' => 'Sa',
            'Sun' => 'So',
        );

        $divisionCourseNameList = array();
        $teachers = array();
        if (($tblDivisionCourseList = $tblTest->getDivisionCourses())) {
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                $divisionCourseNameList[] = $tblDivisionCourse->getName();
                // Lerngruppe
                if ($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_TEACHER_GROUP) {
                    if (($tblTeacherList = DivisionCourse::useService()->getDivisionCourseMemberListBy(
                        $tblDivisionCourse, TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER
                    ))) {
                        foreach ($tblTeacherList as $tblPerson) {
                            $teachers[] = $this->getTeacherName($tblPerson);
                        }
                    }
                // Lehraufträge
                } else {
                    if (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy(null, null, $tblDivisionCourse, $tblSubject))) {
                        foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                            if (($tblPerson = $tblTeacherLectureship->getServiceTblPerson())) {
                                $teachers[] = $this->getTeacherName($tblPerson);
                            }
                        }
                    }
                }
            }
        }

        $content = implode(', ', $divisionCourseNameList) . ' ' . $tblSubject->getAcronym() . ' ' . $tblGradeType->getCode() . ' ' . $tblTest->getDescription()
            . '<br>' . strtr(date('D', strtotime($tblTest->getDateString())), $trans) . ' ' . $tblTest->getDateString() . ' - ' . implode(', ', $teachers);

        return new ToolTip($tblGradeType->getIsHighlighted()
            ? new Bold($content)
            : $content, 'Erstellt am: ' . $tblTest->getEntityCreate()->format('d.m.Y H:i'));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    private function getTeacherName(TblPerson $tblPerson): string
    {
        if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))
            && ($acronym = $tblTeacher->getAcronym())
        ) {
            return $acronym;
        } else {
            return $tblPerson->getLastName();
        }
    }
}