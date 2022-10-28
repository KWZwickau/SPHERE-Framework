<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Frontend;

use SPHERE\Application\Api\Education\DivisionCourse\ApiYearChange;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Blackboard;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Window\Stage;

class FrontendYearChange extends FrontendTeacher
{
    /**
     * @return Stage
     */
    public function frontendYearChange(): Stage
    {
        $stage = new Stage('Schuljahreswechsel', '');
        $stage->setContent(
            new Panel(new Calendar() . ' Schuljahreswechsel', $this->formYearChange(), Panel::PANEL_TYPE_INFO)
            . ApiYearChange::receiverBlock($this->loadYearChangeContent(null), 'YearChangeContent')
        );

        return $stage;
    }

    /**
     * @return Form
     */
    public function formYearChange(): Form
    {
        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Data[SchoolType]', 'Schulart', array('{{ Name }}' => Type::useService()->getTypeAll())))
                        ->ajaxPipelineOnChange(ApiYearChange::pipelineLoadYearChangeContent())
                        ->setRequired()
                    , 6),
            )),
            new FormRow(array(
                new FormColumn(
                    (new SelectBox('Data[YearSource]', 'von Schuljahr', array('{{ Name }} {{ Description }}' => Term::useService()->getYearAllSinceYears(1))))
                        ->ajaxPipelineOnChange(ApiYearChange::pipelineLoadYearChangeContent())
                        ->setRequired()
                    , 6),
                new FormColumn(
                    (new SelectBox('Data[YearTarget]', 'nach Schuljahr', array('{{ Name }} {{ Description }}' => Term::useService()->getYearAllSinceYears(0))))
                        ->ajaxPipelineOnChange(ApiYearChange::pipelineLoadYearChangeContent())
                        ->setRequired()
                    , 6),
            )),
        )));
    }

    /**
     * @param $Data
     *
     * @return string
     */
    public function loadYearChangeContent($Data): string
    {
        $content = '';
        $tblSchoolType = false;
        $tblYearSource = false;
        $tblYearTarget = false;
        if (!isset($Data['SchoolType']) || !($tblSchoolType = Type::useService()->getTypeById($Data['SchoolType']))) {
            $content .= new Warning('Bitte wählen Sie eine Schulart aus.', new Exclamation());
        }
        if (!isset($Data['YearSource']) || !($tblYearSource = Term::useService()->getYearById($Data['YearSource']))) {
            $content .= new Warning('Bitte wählen Sie ein Quell-Schuljahr aus.', new Exclamation());
        }
        if (!isset($Data['YearTarget']) || !($tblYearTarget = Term::useService()->getYearById($Data['YearTarget']))) {
            $content .= new Warning('Bitte wählen Sie ein Ziel-Schuljahr aus.', new Exclamation());
        }

        if ($tblSchoolType && $tblYearSource && $tblYearTarget) {
            if ($tblYearTarget->getName() <= $tblYearSource->getName()) {
                return new Warning('Bitte wählen Sie neueres Ziel-Schuljahr aus.', new Exclamation());
            }

            $left = '';
            $right = '';
            $dataSourceList = array();
            $dataTargetList = array();
            $courseSourceList = array();
            $hasAddStudentEducationList = array();
            $tblMemberTypeStudent = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT);
            if (($tblStudentEducationList = DivisionCourse::useService()->getStudentEducationListBy($tblYearSource, $tblSchoolType))) {
                $tblStudentEducationList = $this->getSorter($tblStudentEducationList)->sortObjectBy('Sort');
                /** @var TblStudentEducation $tblStudentEducationSource */
                foreach ($tblStudentEducationList as $tblStudentEducationSource) {
                    if (($tblPerson = $tblStudentEducationSource->getServiceTblPerson())
                        && !$tblStudentEducationSource->isInActive()
                        && ($level = $tblStudentEducationSource->getLevel())
                    ) {
                        $dataSourceList[$level][$tblPerson->getId()] = $tblPerson->getLastFirstName();
                        if (($tblStudentEducationTarget = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYearTarget))) {
                            $dataTargetList[$tblStudentEducationTarget->getLevel() ?: 'keine'][$tblPerson->getId()] = $tblPerson->getLastFirstName();
                        } elseif (!$tblSchoolType->getMaxLevel() || $level < $tblSchoolType->getMaxLevel()) {
                            $hasAddStudentEducationList[$level + 1] = 1;
                            $dataTargetList[$level + 1][$tblPerson->getId()] = new Success(new Plus() . ' ' . $tblPerson->getLastFirstName());
                            if ((!$tblStudentEducationTarget || !$tblStudentEducationTarget->getTblDivision())
                                && ($tblDivision = $tblStudentEducationSource->getTblDivision())
                                && !isset($courseSourceList[$tblDivision->getId()])
                            ) {
                                $courseSourceList[$tblDivision->getId()] = $tblDivision->getName();
                            }
                            if ((!$tblStudentEducationTarget || !$tblStudentEducationTarget->getTblCoreGroup())
                                && ($tblCoreGroup = $tblStudentEducationSource->getTblCoreGroup())
                                && !isset($courseSourceList[$tblCoreGroup->getId()])
                            ) {
                                $courseSourceList[$tblCoreGroup->getId()] = $tblCoreGroup->getName();
                            }
                            if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseMemberListByPersonAndYearAndMemberType($tblPerson, $tblYearSource, $tblMemberTypeStudent))) {
                                foreach ($tblDivisionCourseList as $tblDivisionCourseMember) {
                                    if (($temp = $tblDivisionCourseMember->getTblDivisionCourse())
                                        && !isset($courseSourceList[$temp->getId()])
                                    ) {
                                        $courseSourceList[$temp->getId()] = $temp->getName();
                                    }
                                }
                            }


                            // todo Fächer

                            // todo sekII?
                        }
                    }
                }
            }

            /**
             * Schüler-Bildung anzeigen
             */
            ksort($dataSourceList);
            foreach ($dataSourceList as $levelKey => $nameList) {
                $left .= new Panel('Klassenstufe: ' . $levelKey, $nameList, Panel::PANEL_TYPE_DEFAULT);
            }
            ksort($dataTargetList);
            foreach ($dataTargetList as $levelKey => $nameList) {
                $right .= new Panel('Klassenstufe: ' . $levelKey, $nameList,
                    isset($hasAddStudentEducationList[$levelKey]) ? Panel::PANEL_TYPE_SUCCESS : Panel::PANEL_TYPE_DEFAULT);
            }
            $content .= new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn($left, 6),
                new LayoutColumn($right, 6)
            )), new Title(new PersonGroup() . ' Schüler-Bildung')));

            /**
             * Kurse und Lehraufträge aufbereiten
             */
            asort($courseSourceList, SORT_NATURAL);
            $dataCourseLeft = array();
            $dataCourseRight = array();
            $hasAddCoursesList = array();
            $hasAddTeacherLectureshipList = array();
            $dataTeacherLectureshipLeft = array();
            $dataTeacherLectureshipRight = array();
            foreach ($courseSourceList as $divisionCourseId => $name) {
                if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($divisionCourseId))) {
                    $dataCourseLeft[$tblDivisionCourse->getTypeIdentifier()][$tblDivisionCourse->getId()] = $tblDivisionCourse->getName();
                    $newName = $tblDivisionCourse->getName();
                    if (preg_match_all('!\d+!', $tblDivisionCourse->getName(), $matches)) {
                        $pos = strpos($tblDivisionCourse->getName(), $matches[0][0]);
                        if ($pos === 0) {
                            $level = $matches[0][0];
                            $newName = ($level + 1) . substr($newName, strlen($level));
                        }
                    }

                    // prüfen, ob es den kurs im neuen schuljahr schon gibt
                    if (($tblDivisionCourseFuture = DivisionCourse::useService()->getDivisionCourseByNameAndYear($newName, $tblYearTarget))) {
                        $newName = $tblDivisionCourseFuture->getName();
                        $isAdd = false;
                    } else {
                        $hasAddCoursesList[$tblDivisionCourse->getTypeIdentifier()] = 1;
                        $isAdd = true;
                    }
                    $dataCourseRight[$tblDivisionCourse->getTypeIdentifier()][] = $isAdd ? new Success(new Plus() . ' ' . $newName) : $newName;

                    // Lehraufträge
                    if (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYearSource, null, $tblDivisionCourse))) {
                        foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                            if (($tblTeacher = $tblTeacherLectureship->getServiceTblPerson())
                                && ($tblSubject = $tblTeacherLectureship->getServiceTblSubject())
                            ) {
                                $dataTeacherLectureshipLeft[$tblTeacher->getId()][$tblSubject->getId()][$tblDivisionCourse->getId()] = $tblDivisionCourse->getName();
                                // prüfen, ob der Lehrauftrag schon existiert
                                if ($tblDivisionCourseFuture
                                    && DivisionCourse::useService()->getTeacherLectureshipListBy($tblYearTarget, $tblTeacher, $tblDivisionCourseFuture, $tblSubject)
                                ) {
                                    $dataTeacherLectureshipRight[$tblTeacher->getId()][$tblSubject->getId()][$tblDivisionCourse->getId()] = $newName;
                                } else {
                                    $hasAddTeacherLectureshipList[$tblTeacher->getId()] = 1;
                                    $dataTeacherLectureshipRight[$tblTeacher->getId()][$tblSubject->getId()][$tblDivisionCourse->getId()] = new Success(new Plus()  . $newName);
                                }
                            }
                        }
                    }
                }
            }

            /*
             * Kurse anzeigen
             */
            $contentCourseLeft = '';
            $contentCourseRight = '';
            $identifier = TblDivisionCourseType::TYPE_DIVISION;
            if (isset($dataCourseLeft[$identifier])) {
                $contentCourseLeft .= new Panel('Klassen', $dataCourseLeft[$identifier], Panel::PANEL_TYPE_DEFAULT);
                $contentCourseRight .= new Panel('Klassen', $dataCourseRight[$identifier],
                    isset($hasAddCoursesList[$identifier]) ? Panel::PANEL_TYPE_SUCCESS : Panel::PANEL_TYPE_DEFAULT);
            }
            $identifier = TblDivisionCourseType::TYPE_CORE_GROUP;
            if (isset($dataCourseLeft[$identifier])) {
                $contentCourseLeft .= new Panel('Stammgruppen', $dataCourseLeft[$identifier], Panel::PANEL_TYPE_DEFAULT);
                $contentCourseRight .= new Panel('Stammgruppen', $dataCourseRight[$identifier],
                    isset($hasAddCoursesList[$identifier]) ? Panel::PANEL_TYPE_SUCCESS : Panel::PANEL_TYPE_DEFAULT);
            }
            $identifier = TblDivisionCourseType::TYPE_TEACHING_GROUP;
            if (isset($dataCourseLeft[$identifier])) {
                $contentCourseLeft .= new Panel('Unterrichtsgruppen', $dataCourseLeft[$identifier], Panel::PANEL_TYPE_DEFAULT);
                $contentCourseRight .= new Panel('Unterrichtsgruppen', $dataCourseRight[$identifier],
                    isset($hasAddCoursesList[$identifier]) ? Panel::PANEL_TYPE_SUCCESS : Panel::PANEL_TYPE_DEFAULT);
            }
            $content .= new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn($contentCourseLeft, 6),
                new LayoutColumn($contentCourseRight, 6)
            )), new Title(new Blackboard() . ' Kurse der Schüler')));

            /*
             * Lehraufträge anzeigen
             */
            $panelTeacherListLeft = array();
            foreach ($dataTeacherLectureshipLeft as $teacherId => $subjectList) {
                if (($tblTeacherPerson = Person::useService()->getPersonById($teacherId))) {
                    $panelName = $tblTeacherPerson->getLastFirstName()
                        . (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblTeacherPerson)) ? ' (' . $tblTeacher->getAcronym() . ')' : '');
                    $panelData = array();
                    foreach ($subjectList as $subjectId => $courseList) {
                        if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                            $panelData[] = new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn($tblSubject->getDisplayName() . ':' , 6),
                                new LayoutColumn(implode(', ', $courseList), 6),
                            ))));
                        }
                    }
                    asort($panelData);
                    $panelTeacherListLeft[] = new Panel($panelName, $panelData, Panel::PANEL_TYPE_DEFAULT);
                }
            }
            $panelTeacherListRight = array();
            foreach ($dataTeacherLectureshipRight as $teacherId => $subjectList) {
                if (($tblTeacherPerson = Person::useService()->getPersonById($teacherId))) {
                    $panelName = $tblTeacherPerson->getLastFirstName()
                        . (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblTeacherPerson)) ? ' (' . $tblTeacher->getAcronym() . ')' : '');
                    $panelData = array();
                    foreach ($subjectList as $subjectId => $courseList) {
                        if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                            $panelData[] = new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn($tblSubject->getDisplayName() . ':' , 6),
                                new LayoutColumn(implode(', ', $courseList), 6),
                            ))));
                        }
                    }
                    asort($panelData);
                    $panelTeacherListRight[] = new Panel($panelName, $panelData,
                        isset($hasAddTeacherLectureshipList[$teacherId]) ? Panel::PANEL_TYPE_SUCCESS : Panel::PANEL_TYPE_DEFAULT);
                }
            }
            asort($panelTeacherListLeft);
            asort($panelTeacherListRight);
            $content .= new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(implode('<br/>', $panelTeacherListLeft), 6),
                new LayoutColumn(implode('<br/>', $panelTeacherListRight), 6)
            )), new Title(new Education() . ' Lehraufräge')));
        }

        return $content;
    }
}