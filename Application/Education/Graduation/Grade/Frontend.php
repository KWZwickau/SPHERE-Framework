<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTest;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\Table;
use SPHERE\Common\Frontend\Table\Structure\TableBody;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

class Frontend extends FrontendTeacherGroup
{
    /**
     * @return Stage
     */
    public function frontendGradeBook(): Stage
    {
        $stage = new Stage();

        $hasHeadmasterRole = Access::useService()->hasAuthorization('/Education/Graduation/Grade/GradeBook/Headmaster');
        $hasTeacherRole = Access::useService()->hasAuthorization('/Education/Graduation/Grade/GradeBook/Teacher');
        $hasAllReadonlyRole = Access::useService()->hasAuthorization('/Education/Graduation/Grade/GradeBook/AllReadOnly');

        if (($roleValue = Grade::useService()->getRole())) {
            if ($roleValue == "Headmaster") {
                $global = $this->getGlobal();
                $global->POST["Data"]["IsHeadmaster"] = 1;
                $global->savePost();
            }
            if ($roleValue == "AllReadonly") {
                $global = $this->getGlobal();
                $global->POST["Data"]["IsAllReadonly"] = 1;
                $global->savePost();
            }
        }

        $roleChange = "";
        if ($hasHeadmasterRole && $hasTeacherRole) {
            $roleChange =
                (new Form(new FormGroup(new FormRow(new FormColumn(
                    (new CheckBox('Data[IsHeadmaster]', new Bold('Schulleitung'), 1))
                        ->ajaxPipelineOnChange(array(ApiGradeBook::pipelineChangeRole()))
                )))))->disableSubmitAction();
        } elseif ($hasAllReadonlyRole && $hasTeacherRole) {
            $roleChange =
                (new Form(new FormGroup(new FormRow(new FormColumn(
                    (new CheckBox('Data[IsAllReadonly]', new Bold('Integrationsbeauftragter'), 1))
                        ->ajaxPipelineOnChange(array(ApiGradeBook::pipelineChangeRole()))
                )))))->disableSubmitAction();
        }

        if (($tblYear =Grade::useService()->getYear())) {
            $global = $this->getGlobal();
            $global->POST["Data"]["Year"] = $tblYear->getId();
            $global->savePost();
        }

        $stage->setContent(
            new Container("&nbsp;")
            . new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        ApiGradeBook::receiverBlock($this->getHeader(self::VIEW_GRADE_BOOK_SELECT), 'Header')
                    , 8),
                    new LayoutColumn(
                        new PullRight(ApiGradeBook::receiverBlock("", "ChangeRole") . $roleChange)
                    , 2),
                    new LayoutColumn(array(
                        ApiGradeBook::receiverBlock("", "ChangeYear"),
                        (new Form(new FormGroup(new FormRow(new FormColumn(
                            (new SelectBox('Data[Year]', '', array("{{ DisplayName }}" => Term::useService()->getYearAll())))
                                ->ajaxPipelineOnChange(array(ApiGradeBook::pipelineChangeYear()))
                        )))))->disableSubmitAction()
                    ), 2)
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        ApiGradeBook::receiverBlock($this->loadViewGradeBookSelect(), 'Content')
                    )
                ))
            )))
        );

        return $stage;
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     *
     * @return string
     */
    public function loadViewGradeBookContent($DivisionCourseId, $SubjectId, $Filter): string
    {
        $role = Grade::useService()->getRole();
        $isReadonly = false;
        $textKurs = "";
        $textSubject = "";
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblSubject = Subject::useService()->getSubjectById($SubjectId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $textKurs = new Bold($tblDivisionCourse->getDisplayName());
            $textSubject = new Bold($tblSubject->getDisplayName());
            $tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses($tblDivisionCourse);

            $headerList = array();
            $bodyList = array();

            $headerList['Number'] = $this->getTableColumnHead('#');
            $headerList['Person'] = $this->getTableColumnHead('Schüler');

            list($gradeList, $tblTestList) = $this->getTestGradeListAndTestListByPersonListAndSubject($tblPersonList, $tblYear, $tblSubject);
            if (($tblTempList = Grade::useService()->getTestListByDivisionCourseAndSubject($tblDivisionCourse, $tblSubject))) {
                $tblTestList = array_merge($tblTestList, $tblTempList);
            }

            $tblTestList = $this->getSorter($tblTestList)->sortObjectBy('SortDate', new DateTimeSorter());
            foreach ($tblTestList as $tblTest) {
                $headerList['Test' . $tblTest->getId()] = $this->getTableColumnHeadByTest($tblTest);
            }

            $count = 0;
            if (($tblPersonList)) {
                foreach ($tblPersonList as $tblPerson) {
                    if (($tblVirtualSubject = DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))
                        && $tblVirtualSubject->getHasGrading()
                    ) {
//                        $bodyList[$tblPerson->getId()]['Number'] = ($this->getTableColumnBody(++$count))->setClass("tableFixFirstColumn");
                        $bodyList[$tblPerson->getId()]['Number'] = $this->getTableColumnBody(++$count);
                        $bodyList[$tblPerson->getId()]['Person'] = $this->getTableColumnBody($tblPerson->getLastFirstNameWithCallNameUnderline());

                        foreach ($headerList as $key => $value) {
                            if (strpos($key, 'Test') !== false) {
                                $testId = str_replace('Test', '', $key);
                                $bodyList[$tblPerson->getId()][$key] = $this->getTableColumnBody(
                                    isset($gradeList[$tblPerson->getId()][$testId]) ? $gradeList[$tblPerson->getId()][$testId] : '&nbsp;'
                                );
                            }
                        }
                    }
                }
            }

            // table float
            $tableHead = new TableHead(new TableRow($headerList));
            $rows = array();
            foreach ($bodyList as $columnList) {
                $rows[] = new TableRow($columnList);
            }
            $tableBody = new TableBody($rows);
            $table = new Table($tableHead, $tableBody, null, false, null, 'TableCustom');

//            $content = (new Container($table))->setStyle(array(
////                'max-width: 2000px;',
////                'max-height: 2000px;',
//                'overflow: scroll;'
//            ));
            $content = $table;
        } else {
            $content = new Danger("Kurse oder Fach nicht gefunden.", new Exclamation());
        }

        return new Title(
            (new Standard("Zurück", ApiGradeBook::getEndpoint(), new ChevronLeft()))
                ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewGradeBookSelect($Filter))
                . "&nbsp;&nbsp;&nbsp;&nbsp;Notenbuch" . new Muted(new Small(" für Kurs: ")) . $textKurs . new Muted(new Small(" im Fach: ")) . $textSubject)
            . $content;
    }

    /**
     * @param $tblPersonList
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     *
     * @return array[]
     */
    private function getTestGradeListAndTestListByPersonListAndSubject($tblPersonList, TblYear $tblYear, TblSubject $tblSubject): array
    {
        $gradeList = array();
        $testList = array();
        if ($tblPersonList) {
            foreach ($tblPersonList as $tblPerson) {
                if (($tblTestGradeList = Grade::useService()->getTestGradeListByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))) {
                    foreach ($tblTestGradeList as $tblTestGrade) {
                        if ($tblTestGrade->getGrade() !== null) {
                            $tblTest = $tblTestGrade->getTblTest();
                            $gradeList[$tblPerson->getId()][$tblTest->getId()] = $tblTest->getTblGradeType()->getIsHighlighted()
                                ? new Bold($tblTestGrade->getGrade()) : $tblTestGrade->getGrade();
                            $testList[$tblTest->getId()] = $tblTest;
                        }
                    }
                }
            }
        }

        return array($gradeList, $testList);
    }

    /**
     * @param TblTest $tblTest
     * @return TableColumn
     */
    private function getTableColumnHeadByTest(TblTest $tblTest): TableColumn
    {
        $date = $tblTest->getDateString();
        if (strlen($date) > 6) {
            $date = substr($date, 0, 6);
        }

        $tblGradeType = $tblTest->getTblGradeType();
        $text = new Small(new Muted($date)) . '<br>' . ($tblGradeType->getIsHighlighted() ? $tblGradeType->getCode() : new Muted($tblGradeType->getCode()));

        return $this->getTableColumnHead($tblTest->getDescription() ? (new ToolTip($text, htmlspecialchars($tblTest->getDescription())))->enableHtml() : $text, false);
    }
}