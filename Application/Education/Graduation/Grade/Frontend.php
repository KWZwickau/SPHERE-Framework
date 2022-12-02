<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use DateTime;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Grade\Service\VirtualTestTask;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
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

class Frontend extends FrontendTest
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
        $isEdit = Grade::useService()->getIsEdit($DivisionCourseId, $SubjectId);
        $isCheckTeacherLectureship = $isEdit && (Grade::useService()->getRole() == 'Teacher');

        $textKurs = "";
        $textSubject = "";
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblSubject = Subject::useService()->getSubjectById($SubjectId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $textKurs = new Bold($tblDivisionCourse->getDisplayName());
            $textSubject = new Bold($tblSubject->getDisplayName());
            $tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses();

            $headerList = array();
            $bodyList = array();

            $tblTestList = array();
            if (($tblTempList = Grade::useService()->getTestListByDivisionCourseAndSubject($tblDivisionCourse, $tblSubject))) {
                foreach ($tblTempList as $temp) {
                    $tblTestList[$temp->getId()] = $temp;
                }
            }

            list($testGradeList, $taskGradeList, $tblTestListNoTeacherLectureship, $integrationList, $pictureList, $courseList)
                = $this->getTestGradeListAndTestListByPersonListAndSubject(
                    $tblPersonList, $tblYear, $tblSubject, $DivisionCourseId, $Filter, $tblTestList, $isEdit, $isCheckTeacherLectureship
                );

            $headerList['Number'] = $this->getTableColumnHead('#');
            $headerList['Person'] = $this->getTableColumnHead('Schüler');
            if (($hasPicture = !empty($pictureList))) {
                $headerList['Picture'] = $this->getTableColumnHead('Fo&shy;to');
            }
            if (($hasIntegration = !empty($integrationList))) {
                $headerList['Integration'] = $this->getTableColumnHead('Inte&shy;gra&shy;tion');
            }
            if (($hasCourse = !empty($courseList))) {
                $headerList['Course'] = $this->getTableColumnHead(new ToolTip('BG', 'Bildungsgang'));
            }
            $taskListIsEdit = array();
            $this->setGradeBookHeaderList($headerList, $taskListIsEdit, $tblDivisionCourse, $tblTestList, $isEdit, $isCheckTeacherLectureship, $SubjectId, $Filter);

            $count = 0;
            if ($tblPersonList) {
                foreach ($tblPersonList as $tblPerson) {
                    if (($tblVirtualSubject = DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))
                        && $tblVirtualSubject->getHasGrading()
                    ) {
//                        $bodyList[$tblPerson->getId()]['Number'] = ($this->getTableColumnBody(++$count))->setClass("tableFixFirstColumn");
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

                        foreach ($headerList as $key => $value) {
                            // Leistungsüberprüfung
                            if (strpos($key, 'Test') !== false) {
                                $testId = str_replace('Test', '', $key);

                                if (isset($testGradeList[$tblPerson->getId()][$testId])) {
                                    $contentGrade = $testGradeList[$tblPerson->getId()][$testId];
                                } else {
                                    if ($isCheckTeacherLectureship) {
                                        $isNewGrade = $isEdit && !isset($tblTestListNoTeacherLectureship[$testId]);
                                    } else {
                                        $isNewGrade = $isEdit;
                                    }
                                    if ($isNewGrade) {
                                        $contentGrade = (new Link($this->getGradeContainer(), ApiGradeBook::getEndpoint()))
                                            ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewTestGradeEditContent($DivisionCourseId, $tblSubject->getId(), $Filter, $testId));
                                    } else {
                                        $contentGrade = '&nbsp;';
                                    }
                                }

                                $bodyList[$tblPerson->getId()][$key] = $this->getTableColumnBody($contentGrade);
                            // Notenauftrag
                            } elseif (strpos($key, 'Task') !== false) {
                                $taskId = str_replace('Task', '', $key);
                                $contentGrade = $taskGradeList[$tblPerson->getId()][$taskId] ?? '';
                                if ($taskListIsEdit[$taskId]) {
                                    $contentGrade = (new Link($contentGrade ?: $this->getGradeContainer(), ApiGradeBook::getEndpoint()))
                                        ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewTaskGradeEditContent($DivisionCourseId, $tblSubject->getId(), $Filter, $taskId));
                                }

                                $bodyList[$tblPerson->getId()][$key] = $this->getTableColumnBody($contentGrade, self::BACKGROUND_COLOR_TASK_BODY);
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
                    . "&nbsp;&nbsp;&nbsp;&nbsp;Notenbuch"
                    . new Muted(new Small(" für Kurs: ")) . $textKurs
                    . new Muted(new Small(" im Fach: ")) . $textSubject
                    . ($isEdit
                        ? new PullRight((new Primary('Leistungsüberprüfung hinzufügen', ApiGradeBook::getEndpoint(), new Plus()))
                            ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewTestEditContent($DivisionCourseId, $SubjectId, $Filter)))
                        : ''
                    )
            )
            . ApiSupportReadOnly::receiverOverViewModal()
            . ApiPersonPicture::receiverModal()
            . $content;
    }

    /**
     * @param string $content
     *
     * @return Container
     */
    private function getGradeContainer(string $content = '&nbsp;'): Container
    {
        return (new Container($content))->setStyle(array("height: 22px;"));
    }

    /**
     * @param $tblPersonList
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     * @param $DivisionCourseId
     * @param $Filter
     * @param array $tblTestList
     * @param $isEdit
     * @param $isCheckTeacherLectureship
     *
     * @return array[]
     */
    private function getTestGradeListAndTestListByPersonListAndSubject($tblPersonList, TblYear $tblYear, TblSubject $tblSubject, $DivisionCourseId,
        $Filter, array &$tblTestList, $isEdit, $isCheckTeacherLectureship): array
    {
        $tblPersonLogin = Account::useService()->getPersonByLogin();

        $testGradeList = array();
        $taskGradeList = array();
        $integrationList = array();
        $pictureList = array();
        $courseList = array();
        $tblTestListNoTeacherLectureship = array();
        if ($tblPersonList) {
            foreach ($tblPersonList as $tblPerson) {
                if (($tblVirtualSubject = DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))
                    && $tblVirtualSubject->getHasGrading()
                ) {
                    // Zensuren - Leistungsüberprüfungen
                    if (($tblTestGradeList = Grade::useService()->getTestGradeListByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))) {
                        foreach ($tblTestGradeList as $tblTestGrade) {
                            $tblTest = $tblTestGrade->getTblTest();

                            if ($tblTestGrade->getGrade() !== null && !isset($tblTestList[$tblTest->getId()])) {
                                $tblTestList[$tblTest->getId()] = $tblTest;

                                $hasTestEdit = false;
                                // bei Lehrern prüfen, ob ein Lehrauftrag vorliegt
                                if ($isCheckTeacherLectureship) {
                                    if (($tblDivisionCourseList = $tblTest->getDivisionCourses())) {
                                        foreach ($tblDivisionCourseList as $temp) {
                                            if (($hasTestEdit = Grade::useService()->getHasTeacherLectureshipForDivisionCourseAndSubject(
                                                $tblPersonLogin, $temp, $tblSubject
                                            ))) {
                                                break;
                                            }
                                        }
                                    }

                                    if (!$hasTestEdit) {
                                        $tblTestListNoTeacherLectureship[$tblTest->getId()] = $tblTest;
                                    }
                                } else {
                                    $hasTestEdit = true;
                                }
                            } elseif ($isCheckTeacherLectureship) {
                                $hasTestEdit = !isset($tblTestListNoTeacherLectureship[$tblTest->getId()]);
                            } else {
                                $hasTestEdit = true;
                            }

                            $gradeValue = $tblTestGrade->getGrade() !== null ? $tblTestGrade->getGrade() : '&ndash;';
                            $contentGrade = ($tblTest->getTblGradeType()->getIsHighlighted() ? new Bold($gradeValue) : $gradeValue)
                                // öffentlicher Kommentar
                                . (($tblTestGrade->getPublicComment() != '') ? new ToolTip(' ' . new Info(), $tblTestGrade->getPublicComment()) : '');
                            $testGradeList[$tblPerson->getId()][$tblTest->getId()] = $isEdit && $hasTestEdit
                                ? (new Link($this->getGradeContainer($contentGrade), ApiGradeBook::getEndpoint()))
                                    ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewTestGradeEditContent(
                                        $DivisionCourseId, $tblSubject->getId(), $Filter, $tblTest->getId()))
                                : $contentGrade;
                        }
                    }

                    // Zensuren - Notenaufträge
                    if (($tblTaskGradeList = Grade::useService()->getTaskGradeListByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))) {
                        foreach ($tblTaskGradeList as $tblTaskGrade) {
                            $tblTask = $tblTaskGrade->getTblTask();
                            if (isset($taskGradeList[$tblPerson->getId()][$tblTask->getId()])) {
                                $taskGradeList[$tblPerson->getId()][$tblTask->getId()] .= ', ' . $tblTaskGrade->getDisplayGrade();
                            } else {
                                $taskGradeList[$tblPerson->getId()][$tblTask->getId()] = $tblTaskGrade->getDisplayGrade();
                            }
                        }
                    }

                    Grade::useService()->setStudentInfo($tblPerson, $tblYear, $integrationList, $pictureList, $courseList);
                }
            }
        }

        return array($testGradeList, $taskGradeList, $tblTestListNoTeacherLectureship, $integrationList, $pictureList, $courseList);
    }

    private function setGradeBookHeaderList(array &$headerList, array &$taskListIsEdit,
        TblDivisionCourse $tblDivisionCourse, $tblTestList, bool $isEdit, bool $isCheckTeacherLectureship, $SubjectId, $Filter)
    {
        $isRoleHeadmaster = Grade::useService()->getRole() == 'Headmaster';
        if ($tblTestList) {
            foreach ($tblTestList as $tblTest) {
                $virtualTestTaskList[] = new VirtualTestTask($tblTest->getDate() ?: $tblTest->getFinishDate(), $tblTest, null);
            }
        }
        if (($tblTaskList = Grade::useService()->getTaskListByStudentsInDivisionCourse($tblDivisionCourse))) {
            foreach ($tblTaskList as $tblTask) {
                $virtualTestTaskList[] = new VirtualTestTask($tblTask->getDate(), null, $tblTask);
            }
        }
        if (!empty($virtualTestTaskList)) {
            $virtualTestTaskList = $this->getSorter($virtualTestTaskList)->sortObjectBy('Date', new DateTimeSorter());
            /** @var VirtualTestTask $virtualTestTask */
            foreach ($virtualTestTaskList as $virtualTestTask) {
                if ($virtualTestTask->getIsTask()) {
                    $taskId = $virtualTestTask->getTblTask()->getId();
                    $isEditTask = false;
                    if ($isEdit) {
                        $now = new DateTime('now');
                        if ($isRoleHeadmaster) {
                            $isEditTask = $virtualTestTask->getTblTask()->getFromDate() <= $now;
                        } else {
                            $isEditTask = $virtualTestTask->getTblTask()->getFromDate() <= $now && $now <= $virtualTestTask->getTblTask()->getToDate();
                        }
                    }
                    $taskListIsEdit[$taskId] = $isEditTask;
                    $headerList['Task' . $taskId]
                        = $this->getTableColumnHeadByTask($virtualTestTask->getTblTask(), $tblDivisionCourse->getId(), $SubjectId, $Filter, $isEditTask);
                } else {
                    $testId = $virtualTestTask->getTblTest()->getId();
                    if ($isCheckTeacherLectureship) {
                        $isEditTest = $isEdit && !isset($tblTestListNoTeacherLectureship[$testId]);
                    } else {
                        $isEditTest = $isEdit;
                    }

                    $headerList['Test' . $testId]
                        = $this->getTableColumnHeadByTest($virtualTestTask->getTblTest(), $tblDivisionCourse->getId(), $SubjectId, $Filter, $isEditTest);
                }
            }
        }
    }

    /**
     * @param TblTest $tblTest
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     * @param bool $isEdit
     *
     * @return TableColumn
     */
    private function getTableColumnHeadByTest(TblTest $tblTest, $DivisionCourseId, $SubjectId, $Filter, bool $isEdit): TableColumn
    {
        $date = $tblTest->getFinishDateString() ?: $tblTest->getDateString();
        if (strlen($date) > 6) {
            $date = substr($date, 0, 6);
        }

        $tblGradeType = $tblTest->getTblGradeType();
        $text = new Small($date) . '<br>' . $tblGradeType->getCode();

        if ($isEdit) {
            $content = new Container(
                (new Link(
                    $text,
                    ApiGradeBook::getEndpoint(),
                    null,
                    array(),
                    $tblTest->getDescription() ? htmlspecialchars($tblTest->getDescription()) : ''
                ))->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewTestEditContent($DivisionCourseId, $SubjectId, $Filter, $tblTest->getId()))
            );
        } else {
            $content = new Container($tblTest->getDescription() ? new ToolTip($text, htmlspecialchars($tblTest->getDescription())) :($text));
        }

        if (!$tblGradeType->getIsHighlighted()) {
            // Browser macht Tabellen Header automatisch bold
            $content->setStyle(array("font-weight: lighter;"));
        }

        return $this->getTableColumnHead(
            $content, $tblGradeType->getIsHighlighted()
        );
    }

    /**
     * @param TblTask $tblTask
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     * @param bool $isEdit
     *
     * @return TableColumn
     */
    private function getTableColumnHeadByTask(TblTask $tblTask, $DivisionCourseId, $SubjectId, $Filter, bool $isEdit): TableColumn
    {
        $date = $tblTask->getDateString();
        if (strlen($date) > 6) {
            $date = substr($date, 0, 6);
        }

        $text = new Small($date) . '<br>' . $tblTask->getShortTypeName();
        $toolTip = htmlspecialchars($tblTask->getName() . ' (' . $tblTask->getFromDateString() . ' - ' . $tblTask->getToDateString() . ')');

        if ($isEdit) {
            $content = new Container(
                (new Link(
                    $text,
                    ApiGradeBook::getEndpoint(),
                    null,
                    array(),
                    $toolTip
                ))->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewTaskGradeEditContent($DivisionCourseId, $SubjectId, $Filter, $tblTask->getId()))
            );
        } else {
            $content = new Container(new ToolTip($text, $toolTip));
        }

        return $this->getTableColumnHead($content, true, 1, self::BACKGROUND_COLOR_TASK_HEADER);
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     * @param $TaskId
     * @return string
     */
    public function loadViewTaskGradeEditContent($DivisionCourseId, $SubjectId, $Filter, $TaskId): string
    {
        return 'Notenauftrag';
    }
}