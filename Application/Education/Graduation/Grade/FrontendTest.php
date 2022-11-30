<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use DateInterval;
use DateTime;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTestGrade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Comment;
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
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
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
                $Data['Date'] = $tblTest->getDateString();
                $Global->POST['Data']['CorrectionDate'] = $tblTest->getCorrectionDateString();
                $Global->POST['Data']['ReturnDate'] = $tblTest->getReturnDateString();
                if (($tblDivisionCourseList = $tblTest->getDivisionCourses())) {
                    foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                        $Global->POST['Data']['DivisionCourses'][$tblDivisionCourse->getId()] = 1;
                        $Data['DivisionCourses'][$tblDivisionCourse->getId()] = 1;
                    }
                }
            } else {
                $Global->POST['Data']['DivisionCourses'][$DivisionCourseId] = 1;
            }

            $Global->savePost();
        }

        $tblGradeTypeList = Grade::useService()->getGradeTypeList();
        $size = 4;

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
                    new CheckBox('Data[IsContinues]', new Bold('fortlaufendes Datum ' .
                        new ToolTip(new InfoIcon(), "Bei Tests mit 'fortlaufendes Datum' 
                        erfolgt die Freigabe für die Notenübersicht (Eltern, Schüler) automatisch, sobald das Datum der 
                        jeweiligen Note (Prio1) oder das optionale Enddatum (Prio2) erreicht ist.")
                        . '(z.B. für Mündliche Noten)'
                    ), 1,
                        array(
                            'Data[FinishDate]',
                            'Data[Date]',
//                            'Data[CorrectionDate]',
                            'Data[ReturnDate]'
                        ))
                ),
                new FormColumn(
                    (new DatePicker('Data[FinishDate]', '', 'Enddatum (optional für Notendatum)', new Calendar()))->setDisabled(), $size
                ),
                new FormColumn(
                    (new DatePicker('Data[Date]', '', 'Datum', new Calendar()))
                        ->ajaxPipelineOnChange(ApiGradeBook::pipelineLoadTestPlanning())
                    , $size
                ),
//                new FormColumn(
//                    new DatePicker('Data[CorrectionDate]', '', 'Korrekturdatum', new Calendar()), $size
//                ),
                new FormColumn(
                    new DatePicker('Data[ReturnDate]', '', 'Bekanntgabedatum für Notenübersicht (Eltern, Schüler)',
                        new Calendar()), $size
                ),
            )),
            new FormRow(array(
                new FormColumn(
                    $this->getDivisionCoursesSelectContent($SubjectId, $Filter)
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
     * @param $Filter
     *
     * @return Layout|Warning
     */
    public function getDivisionCoursesSelectContent($SubjectId, $Filter)
    {
        if (!($tblSubject = Subject::useService()->getSubjectById($SubjectId))) {
            return new Warning('Fach wurde nicht gefunden.', new Exclamation());
        }
        if (!($tblYear = Grade::useService()->getYear())) {
            return new Warning('Schuljahr wurde nicht gefunden.', new Exclamation());
        }

        $size = 3;
        $columnList = array();
        $contentPanelList = array();

        // Schulleitung
        if (($role = Grade::useService()->getRole()) && $role == 'Headmaster') {
            $tblSchoolType = isset($Filter["SchoolType"]) ? Type::useService()->getTypeById($Filter["SchoolType"]) : false;
            if ($tblSchoolType
                && ($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, null, null, $tblSubject))
            ) {
                // Lehraufträge
                $tblTeacherLectureshipList = $this->getSorter($tblTeacherLectureshipList)->sortObjectBy('SortCourseName');
                /** @var TblTeacherLectureship $tblTeacherLectureship */
                foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                    if (($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())
                        && (!isset($contentPanelList[$tblDivisionCourse->getType()->getId()][$tblDivisionCourse->getId()]))
                    ) {
                        if (!($tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents())
                            || !isset($tblSchoolTypeList[$tblSchoolType->getId()])
                        ) {
                            continue;
                        }

                        $contentPanelList[$tblDivisionCourse->getType()->getId()][$tblDivisionCourse->getId()]
                            = (new CheckBox("Data[DivisionCourses][{$tblDivisionCourse->getId()}]", $tblDivisionCourse->getDisplayName(), 1))
                                ->ajaxPipelineOnChange(ApiGradeBook::pipelineLoadTestPlanning());
                    }
                }
            }
        // Lehrer
        } else {
            if (($tblPerson = Account::useService()->getPersonByLogin())
                && ($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPerson, null, $tblSubject))
            ) {
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
            }
        }

        if (!empty($contentPanelList)) {
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
        } else {
            return new Warning('Keine entsprechenden Lehraufträge gefunden.', new Exclamation());
        }
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

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     * @param $TestId
     *
     * @return string
     */
    public function loadViewTestGradeEditContent($DivisionCourseId, $SubjectId, $Filter, $TestId): string
    {
        if (!($tblTest = Grade::useService()->getTestById($TestId))) {
            return (new Danger("Leistungsüberprüfung wurde nicht gefunden!", new Exclamation()));
        }
        if (!($tblYear = $tblTest->getServiceTblYear())) {
            return (new Danger("Schuljahr wurde nicht gefunden!", new Exclamation()));
        }
        if (!($tblSubject = Subject::useService()->getSubjectById($SubjectId))) {
            return (new Danger("Fach wurde nicht gefunden!", new Exclamation()));
        }

        $form = $this->formTestGrades($tblTest, $tblYear, $tblSubject, $DivisionCourseId, $Filter, true);

        return $this->getTestGradesEdit($form, $DivisionCourseId, $SubjectId, $Filter, $TestId);
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
    public function getTestGradesEdit($form, $DivisionCourseId, $SubjectId, $Filter, $TestId): string
    {
        $textSubject = '';
        if (($tblSubject = Subject::useService()->getSubjectById($SubjectId))) {
            $textSubject = new Bold($tblSubject->getDisplayName());
        }

        if (($tblTest = Grade::useService()->getTestById($TestId))
            && $tblSubject
        ) {
            $content = new Layout(new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Zensuren-Typ', $tblTest->getGradeTypeDisplayName(), Panel::PANEL_TYPE_INFO)
                            , 3),
                        new LayoutColumn(
                            new Panel('Beschreibung', $tblTest->getDescription(), Panel::PANEL_TYPE_INFO)
                            , 9),
                    ))
                )))
                . $form;
        } else {
            $content = new Danger("Leistungsüberprüfung nicht gefunden.", new Exclamation());
        }

        return new Title(
                (new Standard("Zurück", ApiGradeBook::getEndpoint(), new ChevronLeft()))
                    ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewGradeBookContent($DivisionCourseId, $SubjectId, $Filter))
                . "&nbsp;&nbsp;&nbsp;&nbsp; Leistungsüberprüfung" . new Muted(new Small(" Zensuren eintragen im Fach: ")) . $textSubject
            )
            . ApiSupportReadOnly::receiverOverViewModal()
            . ApiPersonPicture::receiverModal()
            . $content;
    }

    /**
     * @param TblTest $tblTest
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     * @param $DivisionCourseId
     * @param $Filter
     * @param bool $setPost
     * @param null $Errors
     *
     * @return Form
     */
    public function formTestGrades(TblTest $tblTest, TblYear $tblYear, TblSubject $tblSubject, $DivisionCourseId, $Filter, bool $setPost = false, $Errors = null): Form
    {
        $headerList = array();
        $bodyList = array();

        $tblPersonList = array();
        $integrationList = array();
        $pictureList = array();
        $courseList = array();
        if (($tblDivisionCourseList = $tblTest->getDivisionCourses())) {
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                if (($tempPersons = $tblDivisionCourse->getStudentsWithSubCourses())) {
                    foreach ($tempPersons as $tblPersonTemp) {
                        if (($tblVirtualSubject = DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject(
                                $tblPersonTemp, $tblYear, $tblSubject
                            ))
                            && $tblVirtualSubject->getHasGrading()
                            && !isset($tblPersonList[$tblPersonTemp->getId()])
                        ) {
                            Grade::useService()->setStudentInfo($tblPersonTemp, $tblYear, $integrationList, $pictureList, $courseList);
                            $tblPersonList[$tblPersonTemp->getId()] = $tblPersonTemp;
                        }
                    }
                }
            }
        }

        $tblGradeList = array();
        if ($setPost) {
            if (($tempGrades = $tblTest->getGrades())) {
                $global = $this->getGlobal();
                foreach ($tempGrades as $tblTestGrade) {
                    if (($tblPersonGrade = $tblTestGrade->getServiceTblPerson())) {
                        if ($tblTestGrade->getGrade() === null) {
                            $global->POST['Data'][$tblPersonGrade->getId()]['Attendance'] = 1;
                        } else {
                            $gradeValue = str_replace('.', ',', $tblTestGrade->getGrade());
                            $global->POST['Data'][$tblPersonGrade->getId()]['Grade'] = $gradeValue;
                        }
                        $global->POST['Data'][$tblPersonGrade->getId()]['Comment'] = $tblTestGrade->getComment();
                        $global->POST['Data'][$tblPersonGrade->getId()]['PublicComment'] = $tblTestGrade->getPublicComment();
                        if ($tblTest->getIsContinues()) {
                            $global->POST['Data'][$tblPersonGrade->getId()]['Date'] = $tblTestGrade->getDateString();
                        }

                        // weitere Zensuren von Schüler welche nicht mehr im Kurs sind
                        if (!isset($tblPersonList[$tblPersonGrade->getId()])) {
                            Grade::useService()->setStudentInfo($tblPersonGrade, $tblYear, $integrationList, $pictureList, $courseList);
                            $tblPersonList[$tblPersonGrade->getId()] = $tblPersonGrade;
                        }

                        // für Lehrer, welcher die Note gespeichert hat
                        $tblGradeList[$tblPersonGrade->getId()] = $tblTestGrade;
                    }
                }
                $global->savePost();
            }
        }

        $headerList['Number'] = '#';
        $headerList['Person'] = 'Schüler';
        if (($hasPicture = !empty($pictureList))) {
            $headerList['Picture'] = 'Fo&shy;to';
        }
        if (($hasIntegration = !empty($integrationList))) {
            $headerList['Integration'] = 'Inte&shy;gra&shy;tion';
        }
        if (($hasCourse = !empty($courseList))) {
            $headerList['Course'] = new ToolTip('BG', 'Bildungsgang');
        }
        $headerList['Grade'] = 'Zensur';
        if ($tblTest->getIsContinues()) {
            $headerList['Date'] = 'Datum' . ($tblTest->getFinishDateString() ? ' (' . $tblTest->getFinishDateString() . ')' : '');
        }
        $headerList['Comment'] = 'Vermerk Noten&shy;änderung';
        $headerList['Attendance'] = 'Nicht teil&shy;genommen';
        $headerList['PublicComment'] = 'Kommentar für Eltern-/Schülerzugang';

        if ($tblPersonList) {
            $count = 0;
            $tabIndex = 1;

            // todo bewertungssystem abhängig vom Schüler
            $selectList[-1] = '';
            for ($i = 1; $i < 6; $i++) {
                $selectList[$i . '+'] = (string)($i . '+');
                $selectList[$i] = (string)($i);
                $selectList[$i . '-'] = (string)($i . '-');
            }
            $selectList[6] = 6;

            foreach ($tblPersonList as $tblPerson) {
                /** @var TblTestGrade $tblGrade */
                $tblGrade = $tblGradeList[$tblPerson->getId()] ?? false;

                $bodyList[$tblPerson->getId()]['Number'] = $this->getTableColumnBody(++$count);
                $bodyList[$tblPerson->getId()]['Person'] = $this->getTableColumnBody($tblPerson->getLastFirstNameWithCallNameUnderline());

                if ($hasPicture) {
                    $bodyList[$tblPerson->getId()]['Picture'] = $this->getTableColumnBody($pictureList[$tblPerson->getId()] ?? '&nbsp;');
                }
                if ($hasIntegration) {
                    $bodyList[$tblPerson->getId()]['Integration'] = $this->getTableColumnBody($integrationList[$tblPerson->getId()] ?? '&nbsp;');
                }
                if ($hasCourse) {
                    $bodyList[$tblPerson->getId()]['Course'] = $this->getTableColumnBody($courseList[$tblPerson->getId()] ?? '&nbsp;');
                }

                // todo verschiedene Bewertungssysteme
                $selectComplete = (new SelectCompleter('Data[' . $tblPerson->getId() . '][Grade]', '', '', $selectList))
                    ->setTabIndex($tabIndex++);
                $bodyList[$tblPerson->getId()]['Grade'] = $selectComplete;

                if ($tblTest->getIsContinues()) {
                    $datePicker = (new DatePicker('Data[' . $tblPerson->getId() . '][Date]', '', '', null, array('widgetPositioning' => array('vertical' => 'bottom'))))
                        ->setTabIndex($tabIndex++);
                    if (isset($Errors[$tblPerson->getId()]['Date'])) {
                        $datePicker->setError('Bitte geben Sie ein Datum an');
                    }
                    $bodyList[$tblPerson->getId()]['Date'] = $datePicker;
                }
                $textFieldComment = (new TextField('Data[' . $tblPerson->getId() . '][Comment]', '', '', new Comment()))
                    ->setTabIndex(1000 + $tabIndex)
                    ->setPrefixValue($tblGrade ? $tblGrade->getDisplayTeacher() : '');
                if (isset($Errors[$tblPerson->getId()]['Comment'])) {
                    $textFieldComment->setError('Bitte geben Sie einen Änderungsgrund an');
                }
                $bodyList[$tblPerson->getId()]['Comment'] = $textFieldComment;
                $bodyList[$tblPerson->getId()]['Attendance'] = (new CheckBox('Data[' . $tblPerson->getId() . '][Attendance]', ' ', 1))->setTabIndex(2000 + $tabIndex);
                $bodyList[$tblPerson->getId()]['PublicComment'] =
                    (new TextField('Data[' . $tblPerson->getId() . '][PublicComment]', 'z.B.: für Betrugsversuch', '', new Comment()))->setTabIndex(1000 + $tabIndex);
            }
        }

        $formRows[] = new FormRow(new FormColumn(
            new TableData($bodyList, null, $headerList,
                array(
                    "paging"         => false, // Deaktivieren Blättern
                    "iDisplayLength" => -1,    // Alle Einträge zeigen
                    "searching"      => false, // Deaktivieren Suchen
                    "info"           => false,  // Deaktivieren Such-Info
                    "responsive"   => false,
                    'order'      => array(
                        array('0', 'asc'),
                    ),
                    'columnDefs' => array(
                        array('orderable' => false, 'targets' => '_all'),
                    ),
                )
            )
        ));
        if ($Errors) {
            $formRows[] = new FormRow(new FormColumn(
                new Danger("Die Zensuren wurden nicht gespeichert. Bitte überprüfen Sie die Fehlermeldungen oben.", new Exclamation())
            ));
        }
        $formRows[] = new FormRow(new FormColumn(array(
            (new Primary('Speichern', ApiGradeBook::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiGradeBook::pipelineSaveTestGradeEdit($DivisionCourseId, $tblSubject->getId(), $Filter, $tblTest->getId())),
            (new Standard('Abbrechen', ApiGradeBook::getEndpoint(), new Disable()))
                ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewGradeBookContent($DivisionCourseId, $tblSubject->getId(), $Filter))
        )));

        return (new Form(new FormGroup($formRows)))->disableSubmitAction();
    }
}