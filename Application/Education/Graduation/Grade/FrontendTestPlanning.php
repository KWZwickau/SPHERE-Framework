<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use DateTime;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;

class FrontendTestPlanning extends FrontendTest
{
    /**
     * @param $Data
     *
     * @return string
     */
    public function loadViewTestPlanningContent($Data = null): string
    {
        if ($Data == null) {
            $global = $this->getGlobal();

            $global->POST['Data']['GradeType'] = -SelectBoxItem::HIGHLIGHTED_IS_HIGHLIGHTED;
            $global->POST['Data']['Option'] = 2;

            $global->savePost();
        }

        $typeSelectBox = new SelectBox('Data[Type]', 'Schulart', array('Name' => Type::useService()->getTypeAll()));
        if (Grade::useService()->getRole() !== 'Teacher') {
            $typeSelectBox->setRequired();
        }

        $divisionTextField = new TextField('Data[DivisionName]', '', 'Klasse/Stammgruppe');

        if (!($tblGradeTypeList = Grade::useService()->getGradeTypeList())) {
            $tblGradeTypeList = array();
        }
        $tblGradeTypeList[] = new SelectBoxItem(-SelectBoxItem::HIGHLIGHTED_IS_HIGHLIGHTED,
            'Nur große Zensuren-Typen (Fett markiert)');
        $gradeTypeSelectBox = (new SelectBox('Data[GradeType]', 'Zensuren-Typ', array('{{ Code }} - {{ Name }}' => $tblGradeTypeList)));

        $optionList[] = new SelectBoxItem(1, 'komplettes Schuljahr');
        $optionList[] = new SelectBoxItem(2, 'ab der aktuellen Woche');
        $option = (new SelectBox('Data[Option]', 'Option', array('Name' => $optionList)))->setRequired();

        $button = (new Primary('Filtern', '', new Filter()))
            ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewTestPlanningContent());

        $form = (new Form(new FormGroup(new FormRow(array(
            new FormColumn(
                new Panel(
                    'Filter',
                    new Layout (new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(
                            $typeSelectBox, 3
                        ),
                        new LayoutColumn(
                            $divisionTextField, 3
                        ),
                        new LayoutColumn(
                            $gradeTypeSelectBox, 3
                        ),
                        new LayoutColumn(
                            $option, 3
                        ),
                        new LayoutColumn(
                            $button
                        ),
                    )))),
                    Panel::PANEL_TYPE_INFO
                )
            )
        )))))->disableSubmitAction();

        return new Title('Planungsübersicht', 'Leistungsüberprüfungen')
            . $form
            . $this->loadContent($Data);
    }

    /**
     * @param $Data
     *
     * @return string
     */
    private function loadContent($Data): string
    {
        ini_set('memory_limit', '1G');

        $IsDivisionTeacher = Grade::useService()->getRole() === 'Teacher';
        if ($Data === null) {
            return '';
        }

        if (!($tblYear = Grade::useService()->getYear())) {
            return new Warning('Bitte wählen Sie ein Schuljahr aus!', new Exclamation());
        }
        list($startDateTime, $endDateTime) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);


        $tblType = Type::useService()->getTypeById($Data['Type']);

        if (!$IsDivisionTeacher && !$tblType) {
            return new Warning('Bitte wählen Sie eine Schulart aus!', new Exclamation());
        }

        $tblGradeTypeSelected = false;
        $isHighlighted = false;
        if ($Data['GradeType'] < 0) {
            if ($Data['GradeType'] == -SelectBoxItem::HIGHLIGHTED_IS_HIGHLIGHTED) {
                $isHighlighted = true;
            }
        } elseif (($tblGradeTypeSelected = Grade::useService()->getGradeTypeById($Data['GradeType']))) {

        }

        if ($Data['Option']  == 2) {
            $now = new DateTime('now');
            $yearTemp = $now->format('Y');
            $weekTemp = $now->format('W');
            $fromDateTime = new DateTime(date('d.m.Y', strtotime("$yearTemp-W{$weekTemp}")));
        } elseif ($Data['Option']  == 1) {
            $fromDateTime = $startDateTime;
        } else {
            return new Warning('Bitte wählen Sie eine Option aus!', new Exclamation());
        }

        $warning = '';
        $tblDivisionCourseList = Grade::useService()->getDivisionCourseListForMinimumGradeCountReporting(
            $tblYear,
            $tblType ?: null,
            $IsDivisionTeacher,
            trim($Data['DivisionName']),
            $warning
        );
        if ($warning) {
            return $warning;
        }

        // weitere Test der Schüler aus anderen Kursen oder Lerngruppen, SekII-Kursen
        $tblDivisionCourseList = $this->getDivisionCourseListFromStudentsInDivisionCourseList($tblDivisionCourseList);

        $testArray = array();
        $preview = array();
        $trans = array(
            'Mon' => 'Mo',
            'Tue' => 'Di',
            'Wed' => 'Mi',
            'Thu' => 'Do',
            'Fri' => 'Fr',
            'Sat' => 'Sa',
            'Sun' => 'So',
        );
        if ($tblDivisionCourseList) {
            /** @var TblDivisionCourse $tblDivisionCourse */
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                if (($tblTestList = Grade::useService()->getTestListBetween($tblDivisionCourse, $fromDateTime, $endDateTime))) {
                    foreach ($tblTestList as $tblTest) {
                        if (($tblGradeType = $tblTest->getTblGradeType())
                            && ($dateTest = $tblTest->getDate())
                            && ($tblSubject = $tblTest->getServiceTblSubject())
                            && (($tblGradeTypeSelected && $tblGradeTypeSelected->getId() == $tblGradeType->getId())
                                || ($isHighlighted && $tblGradeType->getIsHighlighted())
                                || (!$tblGradeTypeSelected && !$isHighlighted)
                            )
                        ) {
                            $dateYear = $dateTest->format('Y');
                            $dateWeek = $dateTest->format('W');
                            $content = $tblDivisionCourse->getName() . ' '
                                . $tblSubject->getAcronym() . ' '
                                . $tblGradeType->getCode() . ' '
                                . ' (' . strtr($dateTest->format('D'), $trans) . ' ' . $dateTest->format('d.m.') . ') '
                                . (($teacher = $tblTest->getDisplayTeacher()) ? ' - ' . $teacher : '');
                            $testArray[$dateYear][$dateWeek][$dateTest->format('Y-m-d') . '-' . $tblDivisionCourse->getName() . '-' . $tblSubject->getAcronym()] = (new ToolTip(
                                $tblGradeType->getIsHighlighted() ? new Bold($content) : $content,
                                htmlspecialchars(
                                    'Thema: ' . $tblTest->getDescription() . '<br>'
                                    . 'Erstellt am: ' . $tblTest->getEntityCreate()->format('d.m.Y H:i')
                                )
                            ))->enableHtml();
                        }
                    }
                }
            }
        }

        if ($testArray) {
            $columnCount = 0;
            $row = array();

            ksort($testArray);
            foreach ($testArray as $year => $weekList) {
                ksort($weekList);

                foreach ($weekList as $week => $valueList) {
                    ksort($valueList, SORT_NATURAL);
                    $monday = date('d.m.y', strtotime("$year-W{$week}"));
                    $friday = date('d.m.y', strtotime("$year-W{$week}-5"));;
                    $panel = new Panel(
                        new Bold('KW: ' . $week) . new Muted(' &nbsp;&nbsp;&nbsp;(' . $monday . ' - ' . $friday . ')'),
                        $valueList,
                        $week == date('W') ? Panel::PANEL_TYPE_INFO : Panel::PANEL_TYPE_DEFAULT
                    );
                    $columnCount++;
                    if ($columnCount > 4) {
                        $preview[] = new LayoutRow($row);
                        $row = array();
                        $columnCount = 1;
                    }
                    $row[] = new LayoutColumn($panel, 3);
                }
            }

            if ($row) {
                $preview[] = new LayoutRow($row);
            }
        }

        if (!empty($preview)) {
            return new Layout(new LayoutGroup($preview));
        }

        return new Warning('Keine Leistungsüberprüfungen gefunden', new Exclamation());
    }

    /**
     * @param array $tblDivisionCourseList
     *
     * @return array
     */
    public function getDivisionCourseListFromStudentsInDivisionCourseList(array $tblDivisionCourseList): array
    {
        $resultList = array();
        /** @var TblDivisionCourse $tblDivisionCourse */
        foreach ($tblDivisionCourseList as $tblDivisionCourse) {
            $resultList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
            // Unterrichtsgruppen, Lerngruppen
            if (($tblDivisionCourseListFromStudents = DivisionCourse::useService()->getDivisionCourseListByStudentsInDivisionCourse($tblDivisionCourse))) {
                foreach ($tblDivisionCourseListFromStudents as $tblDivisionCourseStudent) {
                    if (!isset($resultList[$tblDivisionCourseStudent->getId()])) {
                        $resultList[$tblDivisionCourseStudent->getId()] = $tblDivisionCourseStudent;
                    }
                }
            }

            // SekII-Kurse
            if (DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse)) {
                if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
                    && ($tblYear = $tblDivisionCourse->getServiceTblYear())
                ) {
                    foreach ($tblPersonList as $tblPerson) {
                        if (($tblStudentSubjectList = DivisionCourse::useService()->getStudentSubjectListByPersonAndYear($tblPerson, $tblYear))) {
                            foreach ($tblStudentSubjectList as $tblStudentSubject) {
                                if (($tblDivisionCourseTemp = $tblStudentSubject->getTblDivisionCourse())
                                    && !isset($resultList[$tblDivisionCourseTemp->getId()])
                                    && $tblDivisionCourseTemp->getType()->getIsCourseSystem()
                                ) {
                                    $resultList[$tblDivisionCourseTemp->getId()] = $tblDivisionCourseTemp;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $resultList;
    }
}