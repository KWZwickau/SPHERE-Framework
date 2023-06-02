<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use DateTime;
use SPHERE\Application\Api\Document\Storage\ApiPersonPicture;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Api\People\Meta\Support\ApiSupportReadOnly;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCount;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTaskGrade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Grade\Service\VirtualTestTask;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Comment;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success as TextSuccess;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

class Frontend extends FrontendTestPlanning
{
    /**
     * @param null $DivisionCourseId
     * @param null $SubjectId
     * @param null $TaskId
     * @param null $IsDirectJump
     *
     * @return Stage
     */
    public function frontendGradeBook($DivisionCourseId = null, $SubjectId = null, $TaskId = null, $IsDirectJump = null): Stage
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

        if ($TaskId) {
            // von der Willkommensseite direkt zur Noteneingabe für Notenaufträge springen
            $content = $this->loadViewTaskGradeEditContent($DivisionCourseId, $SubjectId, array(), $TaskId);
        } elseif ($IsDirectJump) {
            // Direkt ins Notenbuch springen, von einer anderen Stelle in der Schulsoftware (Kursheft im digitalen Klassenbuch)
            $content = $this->loadViewGradeBookContent($DivisionCourseId, $SubjectId, array());
        } else {
            $content = $this->loadViewGradeBookSelect();
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
                        ApiGradeBook::receiverBlock($content, 'Content')
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

        $textCourse = "";
        $textSubject = "";
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblSubject = Subject::useService()->getSubjectById($SubjectId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $textCourse = new Bold($tblDivisionCourse->getDisplayName());
            $textSubject = new Bold($tblSubject->getDisplayName());
            $tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses();

            $bodyList = array();

            $tblTestList = array();
            if (($tblTempList = Grade::useService()->getTestListByDivisionCourseAndSubject($tblDivisionCourse, $tblSubject))) {
                foreach ($tblTempList as $temp) {
                    $tblTestList[$temp->getId()] = $temp;
                }
            }

            list($testGradeList, $taskGradeList, $tblTestListNoTeacherLectureship, $integrationList, $pictureList, $courseList, $averagePeriodList,
                $averagePersonList, $scoreRulePersonList, $averageTestSumList, $averageTestCountList)
                = $this->getTestGradeListAndTestListByPersonListAndSubject(
                    $tblPersonList, $tblYear, $tblSubject, $tblDivisionCourse, $Filter, $tblTestList, $isEdit, $isCheckTeacherLectureship
                );

            $hasPicture = !empty($pictureList);
            $hasIntegration = !empty($integrationList);
            $hasCourse = !empty($courseList);
            $headerList = $this->getGradeBookPreHeaderList($hasPicture, $hasIntegration, $hasCourse);
            $taskListIsEdit = array();
            $this->setGradeBookHeaderList($headerList, $taskListIsEdit, $tblTestListNoTeacherLectureship,
                $tblDivisionCourse, $tblTestList, $isEdit, $isCheckTeacherLectureship, $SubjectId, $Filter, $averagePeriodList);

            $count = 0;
            if ($tblPersonList) {
                foreach ($tblPersonList as $tblPerson) {
                    if (($tblVirtualSubject = DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))
                        && $tblVirtualSubject->getHasGrading()
                    ) {
//                        $bodyList[$tblPerson->getId()]['Number'] = ($this->getTableColumnBody(++$count))->setClass("tableFixFirstColumn");
                        $bodyList[$tblPerson->getId()] = $this->getGradeBookPreBodyList($tblPerson, ++$count, $hasPicture, $hasIntegration, $hasCourse,
                            $pictureList, $integrationList, $courseList);

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

                                $bodyList[$tblPerson->getId()][$key] = $this->getTableColumnBody($contentGrade, self::BACKGROUND_COLOR_TASK_BODY == '#FFFFFF' ? null : self::BACKGROUND_COLOR_TASK_BODY);
                            // Halbjahr - Durchschnitt
                            } elseif (strpos($key, 'Period') !== false) {
                                $periodId = str_replace('Period', '', $key);
                                if (isset($averagePersonList[$tblPerson->getId()]['Periods'][$periodId])
                                    && ($tblPeriod = Term::useService()->getPeriodById($periodId))
                                    && ($tblTempTestGradeList = Grade::useService()->getTestGradeListBetweenDateTimesByPersonAndYearAndSubject(
                                        $tblPerson, $tblYear, $tblSubject, $tblPeriod->getFromDateTime(), $tblPeriod->getToDateTime()
                                    ))
                                ) {
                                    $contentPeriod = Grade::useService()->getCalcStudentAverageToolTip($tblPerson, $tblYear, $tblTempTestGradeList,
                                        $scoreRulePersonList[$tblPerson->getId()] ?? null, $tblPeriod);
                                } else {
                                    $contentPeriod = '';
                                }
                                $bodyList[$tblPerson->getId()][$key] = $this->getTableColumnBody(new Bold($contentPeriod), self::BACKGROUND_COLOR_PERIOD);
                            // Gesamtes Schuljahr - Durchschnitt
                            } elseif (strpos($key, 'TotalAverage') !== false) {
                                if (isset($averagePersonList[$tblPerson->getId()]['TotalAverage'])
                                    && ($tblTempTestGradeList = Grade::useService()->getTestGradeListByPersonAndYearAndSubject(
                                        $tblPerson, $tblYear, $tblSubject
                                    ))
                                ) {
                                    $contentPeriod = Grade::useService()->getCalcStudentAverageToolTip($tblPerson, $tblYear, $tblTempTestGradeList,
                                        $scoreRulePersonList[$tblPerson->getId()] ?? null);
                                } else {
                                    $contentPeriod = '';
                                }
                                $bodyList[$tblPerson->getId()][$key] = $this->getTableColumnBody(new Bold($contentPeriod), self::BACKGROUND_COLOR_PERIOD);
                            }
                        }
                    }
                }
            }

            // Fach-Klassen-Durchschnitt
            $bodyList[-1] = $this->getDivisionCourseSubjectAverageData($headerList, $averageTestSumList, $averageTestCountList);

            // table float
            $table = $this->getTableCustom($headerList, $bodyList);

//            $content = (new Container($table))->setStyle(array(
////                'max-width: 2000px;',
////                'max-height: 2000px;',
//                'overflow: scroll;'
//            ));
            $content = $table;
        } else {
            $content = new Danger("Kurse oder Fach nicht gefunden.", new Exclamation());
        }

        $externalDownloadSingleGradeBook = new External(
            'Notenbuch herunterladen',
            '/Api/Document/Standard/Gradebook/Create',
            new Download(),
            array(
                'DivisionCourseId' => $DivisionCourseId,
                'SubjectId' => $SubjectId
            ),
            false
        );

        // Download alle Klassenbücher nicht für Lehrer
        $externalDownloadAllGradeBooks = '';
        if ($tblDivisionCourse->getIsDivisionOrCoreGroup()
            && (Grade::useService()->getRole() != 'Teacher'
                || (($tblPerson = Account::useService()->getPersonByLogin())
                    && ($tblDivisionCourseMemberType = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))
                    && (DivisionCourse::useService()->getDivisionCourseMemberByPerson($tblDivisionCourse, $tblDivisionCourseMemberType, $tblPerson))
                )
            )
        ) {
            $externalDownloadAllGradeBooks = new External(
                'Alle Notenbücher dieses Kurses herunterladen',
                '/Api/Document/Standard/MultiGradebook/Create',
                new Download(),
                array(
                    'DivisionCourseId' => $DivisionCourseId,
                ),
                false
            );
        }

        return
            new Title(
                (new Standard("Zurück", ApiGradeBook::getEndpoint(), new ChevronLeft()))
                    ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewGradeBookSelect($Filter))
                . "&nbsp;&nbsp;&nbsp;Notenbuch"
                . new Muted(new Small(" für Kurs: ")) . $textCourse
                . new Muted(new Small(" im Fach: ")) . $textSubject
                . new PullRight($externalDownloadSingleGradeBook . $externalDownloadAllGradeBooks)
            )
            . new PullClear(new Container(
                 ($isEdit
                    ? (new Primary('Leistungsüberprüfung hinzufügen', ApiGradeBook::getEndpoint(), new Plus()))
                        ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewTestEditContent($DivisionCourseId, $SubjectId, $Filter))
                    : ''
                )
                . new PullRight((new Standard('Mindestnotenanzahl anzeigen', ApiGradeBook::getEndpoint()))
                    ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewMinimumGradeCountContent($DivisionCourseId, $SubjectId, $Filter)))
            ))
            . new Container('&nbsp;')
            . ApiSupportReadOnly::receiverOverViewModal()
            . ApiPersonPicture::receiverModal()
            . $content;
    }

    private function getDivisionCourseSubjectAverageData($headerList, $averageTestSumList, $averageTestCountList): array
    {
        $data = array();
        foreach ($headerList as $key => $value) {
            $contentTemp = '';
            if ($key == 'Person') {
                $contentTemp = new Muted('&#216; Fach-Klasse');
            }
            // Leistungsüberprüfung
            elseif (strpos($key, 'Test') !== false) {
                $testId = str_replace('Test', '', $key);
                if (isset($averageTestSumList[$testId]) && isset($averageTestCountList[$testId])) {
                    $contentTemp = new Muted(Grade::useService()->getGradeAverage($averageTestSumList[$testId], $averageTestCountList[$testId]));
                }
            }
            $data[$key] = $this->getTableColumnBody($contentTemp);
        }

        return $data;
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
     * @param TblDivisionCourse $tblDivisionCourse
     * @param $Filter
     * @param array $tblTestList
     * @param $isEdit
     * @param $isCheckTeacherLectureship
     *
     * @return array[]
     */
    private function getTestGradeListAndTestListByPersonListAndSubject($tblPersonList, TblYear $tblYear, TblSubject $tblSubject, TblDivisionCourse $tblDivisionCourse,
        $Filter, array &$tblTestList, $isEdit, $isCheckTeacherLectureship): array
    {
        $tblPersonLogin = Account::useService()->getPersonByLogin();
        $DivisionCourseId = $tblDivisionCourse->getId();

        $testGradeList = array();
        $taskGradeList = array();
        $integrationList = array();
        $pictureList = array();
        $courseList = array();
        $tblTestListNoTeacherLectureship = array();
        $scoreRulePersonList = array();

        $periodList = array();
        $averagePeriodList = array();
        $averagePersonList = array();

        // für Fach-Klassen-Durchschnitt
        $averageTestSumList = array();
        $averageTestCountList = array();

        if (($tblPeriodList = $tblYear->getPeriodList(false, true))) {
            foreach($tblPeriodList as $tblPeriod) {
                if ($tblPeriod->isLevel12()) {
                    $periodList['Short'][$tblPeriod->getId()] = $tblPeriod;
                } else {
                    $periodList['Normal'][$tblPeriod->getId()] = $tblPeriod;
                }
            }
        }

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

                            // verbale Benotung abkürzen + ToolTip
                            if (strlen($gradeValue) > 10) {
                                $gradeValue = new ToolTip(substr($gradeValue, 0, 9) . '.', $gradeValue);
                            }

                            $contentGrade = ($tblTest->getTblGradeType()->getIsHighlighted() ? new Bold($gradeValue) : $gradeValue)
                                // öffentlicher Kommentar
                                . (($tblTestGrade->getPublicComment() != '') ? new ToolTip(' ' . new Info(), $tblTestGrade->getPublicComment()) : '');
                            $testGradeList[$tblPerson->getId()][$tblTest->getId()] = $isEdit && $hasTestEdit
                                ? (new Link($this->getGradeContainer($contentGrade), ApiGradeBook::getEndpoint()))
                                    ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewTestGradeEditContent(
                                        $DivisionCourseId, $tblSubject->getId(), $Filter, $tblTest->getId()))
                                : $contentGrade;

                            // Fach-Klassen-Durchschnitt
                            if ($tblTestGrade->getIsGradeNumeric()) {
                                if (isset($averageTestSumList[$tblTest->getId()])) {
                                    $averageTestSumList[$tblTest->getId()] += $tblTestGrade->getGradeNumberValue();
                                } else {
                                    $averageTestSumList[$tblTest->getId()] = $tblTestGrade->getGradeNumberValue();
                                }
                                if (isset($averageTestCountList[$tblTest->getId()])) {
                                    $averageTestCountList[$tblTest->getId()]++;
                                } else {
                                    $averageTestCountList[$tblTest->getId()] = 1;
                                }
                            }
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

                    // Schüler-Informationen
                    Grade::useService()->setStudentInfo($tblPerson, $tblYear, $integrationList, $pictureList, $courseList);

                    // Schüler Berechnungsvorschrift ermitteln
                    if (($tblScoreRule = Grade::useService()->getScoreRuleByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject, $tblDivisionCourse))) {
                        $scoreRulePersonList[$tblPerson->getId()] = $tblScoreRule;
                    }

                    // Schüler-Durchschnitte anzeigen
                    if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                        && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                    ) {
                        // SEKII
                        if (DivisionCourse::useService()->getIsCourseSystemBySchoolTypeAndLevel($tblSchoolType, $tblStudentEducation->getLevel())) {
                            $tempList = $periodList['Normal'] ?? array();
                            if (DivisionCourse::useService()->getIsShortYearBySchoolTypeAndLevel($tblSchoolType, $tblStudentEducation->getLevel())
                                && isset($periodList['Short'])
                            ) {
                                $tempList = $periodList['Short'];
                            }
                            if (!empty($tempList)) {
                                $count = 0;
                                foreach ($tempList as $period) {
                                    $count++;
                                    $averagePersonList[$tblPerson->getId()]['Periods'][$period->getId()] = $period->getToDateTime();
                                    if (!isset($averagePeriodList['Periods'][$period->getId()])) {
                                        $averagePeriodList['Periods'][$period->getId()] = $count;
                                    }
                                }
                            }
                        // SEKI
                        } else {
                            if (isset($periodList['Normal'])) {
                                $countPeriod = count($periodList['Normal']);
                                $count = 0;
                                foreach ($periodList['Normal'] as $item) {
                                    $count++;
                                    if ($count < $countPeriod) {
                                        $averagePersonList[$tblPerson->getId()]['Periods'][$item->getId()] = $item->getToDateTime();
                                        if (!isset($averagePeriodList['Periods'][$item->getId()])) {
                                            $averagePeriodList['Periods'][$item->getId()] = $count;
                                        }
                                    } else {
                                        $averagePersonList[$tblPerson->getId()]['TotalAverage'] = $item->getToDateTime();
                                        if (!isset($averagePeriodList['TotalAverage'])) {
                                            $averagePeriodList['TotalAverage'] = $item;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return array($testGradeList, $taskGradeList, $tblTestListNoTeacherLectureship, $integrationList, $pictureList, $courseList,
            $averagePeriodList, $averagePersonList, $scoreRulePersonList, $averageTestSumList, $averageTestCountList);
    }

    private function setGradeBookHeaderList(array &$headerList, array &$taskListIsEdit, array $tblTestListNoTeacherLectureship,
        TblDivisionCourse $tblDivisionCourse, $tblTestList, bool $isEdit, bool $isCheckTeacherLectureship, $SubjectId, $Filter, $averagePeriodList)
    {
        $isRoleHeadmaster = Grade::useService()->getRole() == 'Headmaster';
        if ($tblTestList) {
            foreach ($tblTestList as $tblTest) {
                $virtualTestTaskList[] = new VirtualTestTask($tblTest->getDate() ?: $tblTest->getFinishDate(), $tblTest);
            }
        }
        if (($tblTaskList = Grade::useService()->getTaskListByStudentsInDivisionCourse($tblDivisionCourse))) {
            foreach ($tblTaskList as $tblTask) {
                $virtualTestTaskList[] = new VirtualTestTask($tblTask->getDate(), null, $tblTask);
            }
        }
        if (isset($averagePeriodList['Periods'])) {
            foreach ($averagePeriodList['Periods'] as $periodId => $countPeriod) {
                if (($tblPeriod = Term::useService()->getPeriodById($periodId))) {
                    $virtualTestTaskList[] = new VirtualTestTask($tblPeriod->getToDateTime(), null, null, $tblPeriod, $countPeriod);
                }
            }
        }

        if (!empty($virtualTestTaskList)) {
            $virtualTestTaskList = Grade::useService()->getVirtualTestTaskListSorted($virtualTestTaskList, $averagePeriodList);
            // soll immer als Letztes stehen
            if (isset($averagePeriodList['TotalAverage'])) {
                /** @var TblPeriod $tblPeriodTemp */
                $tblPeriodTemp = $averagePeriodList['TotalAverage'];
                $virtualTestTaskList[] = new VirtualTestTask($tblPeriodTemp->getToDateTime(), null, null, $tblPeriodTemp);
            }
            /** @var VirtualTestTask $virtualTestTask */
            foreach ($virtualTestTaskList as $virtualTestTask) {
                switch ($virtualTestTask->getType()) {
                    case VirtualTestTask::TYPE_TEST:
                        $testId = $virtualTestTask->getTblTest()->getId();
                        if ($isCheckTeacherLectureship) {
                            $isEditTest = $isEdit && !isset($tblTestListNoTeacherLectureship[$testId]);
                        } else {
                            $isEditTest = $isEdit;
                        }

                        $headerList['Test' . $testId]
                            = $this->getTableColumnHeadByTest($virtualTestTask->getTblTest(), $tblDivisionCourse->getId(), $SubjectId, $Filter, $isEditTest);
                        break;
                    case VirtualTestTask::TYPE_TASK:
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
                        break;
                    case VirtualTestTask::TYPE_PERIOD:
                        $isTotalAverage = $virtualTestTask->getCountPeriod() == 0;
                        $preKey = $isTotalAverage ? 'TotalAverage' : 'Period';
                        $headerList[$preKey . $virtualTestTask->getTblPeriod()->getId()] = $this->getTableColumnHead(
                            '&#216;'
                                . (!$isTotalAverage ? new Container($virtualTestTask->getCountPeriod() . '. HJ') : ''),
                            true,
                            self::BACKGROUND_COLOR_PERIOD
                        );
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

        return $this->getTableColumnHead($content, true, self::BACKGROUND_COLOR_TASK_HEADER);
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     * @param $TaskId
     *
     * @return string
     */
    public function loadViewTaskGradeEditContent($DivisionCourseId, $SubjectId, $Filter, $TaskId): string
    {
        if (!($tblTask = Grade::useService()->getTaskById($TaskId))) {
            return (new Danger("Notenauftrag wurde nicht gefunden!", new Exclamation()));
        }
        if (!($tblYear = $tblTask->getServiceTblYear())) {
            return (new Danger("Schuljahr wurde nicht gefunden!", new Exclamation()));
        }
        if (!($tblSubject = Subject::useService()->getSubjectById($SubjectId))) {
            return (new Danger("Fach wurde nicht gefunden!", new Exclamation()));
        }

        $form = $this->formTaskGrades($tblTask, $tblYear, $tblSubject, $DivisionCourseId, $Filter, true);

        return $this->getTaskGradesEdit($form, $DivisionCourseId, $SubjectId, $Filter, $TaskId);
    }

    /**
     * @param $form
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     * @param $TaskId
     *
     * @return string
     */
    public function getTaskGradesEdit($form, $DivisionCourseId, $SubjectId, $Filter, $TaskId): string
    {
        if (!($tblTask = Grade::useService()->getTaskById($TaskId))) {
            return new Danger('Der Notenauftrag wurde nicht gefunden', new Exclamation());
        }
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return new Danger('Der Kurs wurde nicht gefunden', new Exclamation());
        }
        $textSubject = '';
        if (($tblSubject = Subject::useService()->getSubjectById($SubjectId))) {
            $textSubject = new Bold($tblSubject->getDisplayName());
        }

        // prüfen, ob es ein freigegebenes Zeugnis zu den Stichtagsnoten gibt
        $hasApprovedCertificates = false;
        if (!$tblTask->getIsTypeBehavior()
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            foreach ($tblPersonList as $tblPerson) {
                if (Prepare::useService()->getIsAppointedDateTaskGradeApproved($tblPerson, $tblTask)) {
                    $hasApprovedCertificates = true;
                    break;
                }
            }
        }

        $content =
            new Panel(
                $tblTask->getTypeName(),
                $tblTask->getName() . ' ' . $tblTask->getDateString()
                . new Container(new Muted('Bearbeitungszeitraum '.$tblTask->getFromDateString() . ' - ' . $tblTask->getToDateString())),
                Panel::PANEL_TYPE_INFO
            )
            . ($hasApprovedCertificates ? new Warning('Es können keine Stichtagsnoten von freigegebenen Zeugnissen bearbeitet werden.', new Exclamation()) : '')
            . $form;

        return new Title(
                $this->getBackButton($DivisionCourseId, $SubjectId, $Filter)
                . "&nbsp;&nbsp;&nbsp;&nbsp; " . ($tblTask->getIsTypeBehavior() ? ' Kopfnoten - Eingabe' : ' Stichtagsnote - Eingabe')
                . new Muted(new Small(" für Kurs: ")) . new Bold($tblDivisionCourse->getName())
                . new Muted(new Small(" Zensuren eintragen im Fach: ")) . $textSubject
            )
            . ApiSupportReadOnly::receiverOverViewModal()
            . ApiPersonPicture::receiverModal()
            . ApiGradeBook::receiverModal()
            . $content;
    }

    /**
     * @param TblTask $tblTask
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     * @param $DivisionCourseId
     * @param $Filter
     * @param bool $setPost
     * @param null $Errors
     * @param null $Data
     *
     * @return Form
     */
    public function formTaskGrades(TblTask $tblTask, TblYear $tblYear, TblSubject $tblSubject, $DivisionCourseId, $Filter, bool $setPost = false,
        $Errors = null, $Data = null): Form
    {
        $bodyList = array();

        $tblPersonList = array();
        $integrationList = array();
        $pictureList = array();
        $courseList = array();
        $personScoreRuleList = array();
        $personPeriodList = array();
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tempPersons = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            foreach ($tempPersons as $tblPersonTemp) {
                if (($tblVirtualSubject = DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject(
                        $tblPersonTemp, $tblYear, $tblSubject
                    ))
                    && $tblVirtualSubject->getHasGrading()
                    && !isset($tblPersonList[$tblPersonTemp->getId()])
                ) {
                    Grade::useService()->setStudentInfo($tblPersonTemp, $tblYear, $integrationList, $pictureList, $courseList);
                    $tblPersonList[$tblPersonTemp->getId()] = $tblPersonTemp;

                    if (!$tblTask->getIsTypeBehavior()) {
                        if (($tblScoreRule = Grade::useService()->getScoreRuleByPersonAndYearAndSubject($tblPersonTemp, $tblYear, $tblSubject, $tblDivisionCourse))) {
                            $personScoreRuleList[$tblPersonTemp->getId()] = $tblScoreRule;
                        }
                    }
                }
            }
        }

        $tblTaskGradeList = array();
        if ($setPost) {
            if ($tblPersonList) {
                foreach ($tblPersonList as $tblPerson) {
                    $global = $this->getGlobal();
                    if (($tempList = Grade::useService()->getTaskGradeListByPersonAndYearAndSubjectAndTask($tblPerson, $tblTask, $tblSubject))) {
                        foreach ($tempList as $tblTaskGrade) {
                            if ($tblTask->getIsTypeBehavior()) {
                                if (($tblGradeType = $tblTaskGrade->getTblGradeType())) {
                                    $global->POST['Data'][$tblPerson->getId()]['GradeTypes'][$tblGradeType->getId()] = $tblTaskGrade->getGrade();
                                    $global->POST['Data'][$tblPerson->getId()]['Comment'] = $tblTaskGrade->getComment();
                                }
                            } else {
                                $gradeValue = str_replace('.', ',', $tblTaskGrade->getGrade());
                                $global->POST['Data'][$tblPerson->getId()]['Grade'] = $gradeValue;
                                $global->POST['Data'][$tblPerson->getId()]['Comment'] = $tblTaskGrade->getComment();

                                // für Lehrer, welcher die Note gespeichert hat + Zeugnistexte
                                $tblTaskGradeList[$tblPerson->getId()] = $tblTaskGrade;
                            }
                        }
                    }
                    $global->savePost();
                }
            }
        }

        $hasPicture = !empty($pictureList);
        $hasIntegration = !empty($integrationList);
        $hasCourse = !empty($courseList);
        $headerList = $this->getGradeBookPreHeaderList($hasPicture, $hasIntegration, $hasCourse);

        // Stichtagsnotenauftrag
        if (!$tblTask->getIsTypeBehavior()) {
            list($tblTestList, $tblTestGradeValueList, $tblTestGradeList, $personPeriodList)
                = $this->getTestsAndTestGradesForAppointedDateTask($tblPersonList, $tblTask, $tblYear, $tblSubject);
            if ($tblTestList) {
                $tblTestList = $this->getSorter($tblTestList)->sortObjectBy('SortDate', new DateTimeSorter());
                foreach ($tblTestList as $tblTest) {
                    $headerList['Test' . $tblTest->getId()] = $this->getTableColumnHeadByTest($tblTest, $DivisionCourseId, $tblSubject->getId(), $Filter, false);
                }
            }
            $headerList['Average'] = $this->getTableColumnHead('&#216;');
            $headerList['Grade'] = $this->getTableColumnHead('Zensur');
            $headerList['GradeText'] = $this->getTableColumnHead(
                (new Link('oder Zeugnistext ' . new Edit(), ApiGradeBook::getEndpoint(), null, array(), 'Alle Zeugnistexte des Kurses auf einmal bearbeiten'))
                    ->ajaxPipelineOnClick(ApiGradeBook::pipelineOpenGradeTextModal($DivisionCourseId))
            );
            $headerList['Comment'] = $this->getTableColumnHead('Vermerk Noten&shy;änderung');
        }

        if ($tblPersonList) {
            $count = 0;
            $tabIndex = 1;

            $selectListBehaviorTask = array();
            $selectListGrades = array();
            $selectListPoints = array();
            if ($tblTask->getIsTypeBehavior()) {
                $selectListBehaviorTask = Grade::useService()->getGradeSelectListByScoreType(
                    Grade::useService()->getScoreTypeByIdentifier('GRADES_BEHAVIOR_TASK')
                );
            } else {
                $selectListGrades = Grade::useService()->getGradeSelectListByScoreType(
                    Grade::useService()->getScoreTypeByIdentifier('GRADES')
                );
                $selectListPoints = Grade::useService()->getGradeSelectListByScoreType(
                    Grade::useService()->getScoreTypeByIdentifier('POINTS')
                );
            }

            foreach ($tblPersonList as $tblPerson) {
                /** @var TblTaskGrade $tblGrade */
                $tblGrade = $tblTaskGradeList[$tblPerson->getId()] ?? false;

                $bodyList[$tblPerson->getId()] = $this->getGradeBookPreBodyList($tblPerson, ++$count, $hasPicture, $hasIntegration, $hasCourse,
                    $pictureList, $integrationList, $courseList);

                // Kopfnoten
                if ($tblTask->getIsTypeBehavior()) {
                    // todo Kopfnotenvorschlag Klassenlehrer
                    $selectList = $selectListBehaviorTask;
                    if (($tblGradeTypes = $tblTask->getGradeTypes())) {
                        foreach ($tblGradeTypes as $tblGradeType) {
                            $key = 'GradeType' . $tblGradeType->getId();
                            if (!isset($headerList[$key])) {
                                $tooltip = $this->getGradeTypeTooltip($tblGradeType);
                                $headerList[$key] = $this->getTableColumnHead($tooltip ? new ToolTip($tblGradeType->getName(), $tooltip) : $tblGradeType->getName());
                            }

                            $selectComplete = (new SelectCompleter('Data[' . $tblPerson->getId() . '][GradeTypes][' . $tblGradeType->getId() . ']', '', '', $selectList))
                                ->setTabIndex($tabIndex++);
                            // vorherige Kopfnote
                            if (($tblPreviousBehaviorGrade = Grade::useService()->getPreviousBehaviorTaskGrade(
                                $tblPerson, $tblYear, $tblSubject, $tblTask->getDate(), $tblGradeType
                            ))) {
                                $selectComplete->setPrefixValue($tblPreviousBehaviorGrade->getGrade());
                            }
                            // Eingabe-Fehler anzeigen
                            if (isset($Errors[$tblPerson->getId()]['GradeTypes'][$tblGradeType->getId()])) {
                                $selectComplete->setError('Bitte geben Sie eine gültige Kopfnote ein');
                            }
                            $bodyList[$tblPerson->getId()][$key] = $this->getTableColumnBody($selectComplete);
                        }
                    }

                    // Kommentar Notenänderung
                    if (!isset($headerList['Comment'])) {
                        $headerList['Comment'] = $this->getTableColumnHead('Vermerk Noten&shy;änderung');
                    }

                    $textFieldComment = (new TextField('Data[' . $tblPerson->getId() . '][Comment]', '', '', new Comment()))
                        ->setTabIndex(1000 + $tabIndex);
                    if (isset($Errors[$tblPerson->getId()]['Comment'])) {
                        $textFieldComment->setError('Bitte geben Sie einen Änderungsgrund an');
                    }
                    $bodyList[$tblPerson->getId()]['Comment'] = $this->getTableColumnBody($textFieldComment);
                // Stichtagsnoten
                } else {
                    // Zensuren bis zum Stichtag
                    foreach ($headerList as $key => $value) {
                        if (strpos($key, 'Test') !== false) {
                            $testId = str_replace('Test', '', $key);
                            $bodyList[$tblPerson->getId()]['Test' . $testId] = $this->getTableColumnBody($tblTestGradeValueList[$tblPerson->getId()][$testId] ?? '');
                        }
                    }

                    list ($contentAverage, $scoreRuleText, $error) = Grade::useService()->getCalcStudentAverage($tblPerson, $tblYear, $tblTestGradeList[$tblPerson->getId()] ?? array(),
                        $personScoreRuleList[$tblPerson->getId()] ?? null, $personPeriodList[$tblPerson->getId()] ?? null
                    );
                    $bodyList[$tblPerson->getId()]['Average'] = $this->getTableColumnBody(Grade::useService()->getCalcStudentAverageToolTipByAverage(
                        $contentAverage, $scoreRuleText, $error));
                    // Notenvorschlag ins Noten-Feld voreintragen
                    if ($contentAverage !== '' && !$tblGrade && $setPost && empty($error)) {
                        $global = $this->getGlobal();
                        $global->POST['Data'][$tblPerson->getId()]['Grade'] = Grade::useService()->getGradeAverageByString($contentAverage);
                        $global->savePost();
                        $gradeAverageProposal = 'Notenvorschlag';
                    } else {
                        $gradeAverageProposal = '';
                    }

                    if (DivisionCourse::useService()->getIsCourseSystemByPersonAndYear($tblPerson, $tblYear)) {
                        $selectList = $selectListPoints;
                    } else {
                        $selectList = $selectListGrades;
                    }

                    $selectComplete = (new SelectCompleter('Data[' . $tblPerson->getId() . '][Grade]', '', '', $selectList))
                        ->setTabIndex($tabIndex++)
                        ->setPrefixValue($tblGrade ? $tblGrade->getDisplayTeacher() : $gradeAverageProposal);
                    // Eingabe-Fehler anzeigen
                    if (isset($Errors[$tblPerson->getId()]['Grade'])) {
                        $selectComplete->setError('Bitte geben Sie eine gültige Stichtagsnote ein');
                    }

                    if (isset($Data[$tblPerson->getId()]['GradeText'])) {
                        $gradeTextId = $Data[$tblPerson->getId()]['GradeText'];
                    } else {
                        $gradeTextId = $tblGrade && ($tblGradeText = $tblGrade->getTblGradeText()) ? $tblGradeText->getId() : 0;
                    }

                    $textFieldComment = (new TextField('Data[' . $tblPerson->getId() . '][Comment]', '', '', new Comment()))
                        ->setTabIndex(1000 + $tabIndex);
                    if (isset($Errors[$tblPerson->getId()]['Comment'])) {
                        $textFieldComment->setError('Bitte geben Sie einen Änderungsgrund an');
                    }

                    // sperren, wenn es ein freigegebenes Zeugnis zur Stichtagsnote gibt
                    if (Prepare::useService()->getIsAppointedDateTaskGradeApproved($tblPerson, $tblTask)) {
                        $selectComplete->setDisabled();
                        // kein alle bearbeiten
                        $global = $this->getGlobal();
                        $global->POST['Data'][$tblPerson->getId()]['GradeText'] = $gradeTextId;
                        $global->savePost();
                        $selectBoxGradeText = (new SelectBox(
                            'Data[' . $tblPerson->getId() . '][GradeText]', '', array(TblGradeText::ATTR_NAME => Grade::useService()->getGradeTextAll())
                        ))->setDisabled();
                        $textFieldComment->setDisabled();
                    } else {
                        // notwendig für alle bearbeiten
                        $selectBoxGradeText = ApiGradeBook::receiverBlock(
                            $this->getGradeTextSelectBox($tblPerson->getId(), $gradeTextId),
                            'ChangeGradeText_' . $tblPerson->getId()
                        );
                    }

                    $bodyList[$tblPerson->getId()]['Grade'] = $this->getTableColumnBody($selectComplete);
                    $bodyList[$tblPerson->getId()]['GradeText'] = $this->getTableColumnBody($selectBoxGradeText);
                    $bodyList[$tblPerson->getId()]['Comment'] = $this->getTableColumnBody($textFieldComment);
                }
            }
        }

        $table = $this->getTableCustom($headerList, $bodyList);

        $formRows[] = new FormRow(new FormColumn(
            $table
//            new TableData($bodyList, null, $headerList,
//                array(
//                    "paging"         => false, // Deaktivieren Blättern
//                    "iDisplayLength" => -1,    // Alle Einträge zeigen
//                    "searching"      => false, // Deaktivieren Suchen
//                    "info"           => false,  // Deaktivieren Such-Info
//                    "responsive"   => false,
//                    'order'      => array(
//                        array('0', 'asc'),
//                    ),
//                    'columnDefs' => array(
//                        array('orderable' => false, 'targets' => '_all'),
//                    ),
//                )
//            )
        ));
        if ($Errors) {
            $formRows[] = new FormRow(new FormColumn(
                new Danger("Die Zensuren wurden nicht gespeichert. Bitte überprüfen Sie die Fehlermeldungen oben.", new Exclamation())
            ));
        }
        $formRows[] = new FormRow(new FormColumn(array(
            (new Primary('Speichern', ApiGradeBook::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiGradeBook::pipelineSaveTaskGradeEdit($DivisionCourseId, $tblSubject->getId(), $Filter, $tblTask->getId())),
            (new Standard('Abbrechen', ApiGradeBook::getEndpoint(), new Disable()))
                ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewGradeBookContent($DivisionCourseId, $tblSubject->getId(), $Filter))
        )));

        return (new Form(new FormGroup($formRows)))->disableSubmitAction();
    }

    private function getTestsAndTestGradesForAppointedDateTask($tblPersonList, TblTask $tblTask, TblYear $tblYear, TblSubject $tblSubject): array
    {
        $tblTestGradeValueList = array();
        $tblTestGradeList = array();
        $tblTestList = array();
        $personPeriodList = array();
        if ($tblPersonList) {
            foreach ($tblPersonList as $tblPerson) {
                $startDate = false;
                $tempList = false;
                // SEKII: nur Noten des Halbjahres bei Kurssystem
                if (DivisionCourse::useService()->getIsCourseSystemByPersonAndYear($tblPerson, $tblYear)) {
                    if (($tblPeriodList = $tblYear->getPeriodListByPerson($tblPerson))) {
                        foreach ($tblPeriodList as $tblPeriod) {
                            if ($tblPeriod->getFromDateTime() <= $tblTask->getDate()
                                && $tblTask->getDate() <= $tblPeriod->getToDateTime()
                            ) {
                                $startDate = $tblPeriod->getFromDateTime();
                                $personPeriodList[$tblPerson->getId()] = $tblPeriod;
                                break;
                            }
                        }
                    }
                // SEKI
                } else {
                    list($startDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
                    $count = 0;
                    // es kann sein, dass es eine Berechnungsvarianten-Bedingung für das 1. Halbjahr gibt
                    if (($tblPeriodList = $tblYear->getPeriodListByPerson($tblPerson))) {
                        foreach ($tblPeriodList as $tblPeriod) {
                            $count++;
                            if ($count == 1) {
                                if ($tblTask->getDate() <= $tblPeriod->getToDateTime()) {
                                    $personPeriodList[$tblPerson->getId()] = $tblPeriod;
                                }
                                break;
                            }
                        }
                    }
                }

                if ($tblTask->getIsAllYears()) {
                    // Zensuren von allen Schuljahren
                    $tempList = Grade::useService()->getTestGradeListToDateTimeByPersonAndSubject($tblPerson, $tblSubject, $tblTask->getToDate());
                } elseif ($startDate) {
                    $tempList = Grade::useService()->getTestGradeListBetweenDateTimesByPersonAndYearAndSubject(
                        $tblPerson, $tblYear, $tblSubject, $startDate, $tblTask->getDate()
                    );
                }

                // Zensuren - Leistungsüberprüfungen
                if ($tempList) {
                    foreach ($tempList as $tblTestGrade) {
                        $tblTest = $tblTestGrade->getTblTest();
                        if (($tblTestGrade->getGrade() !== null)) {
                            $tblTestGradeValueList[$tblPerson->getId()][$tblTest->getId()] = $tblTest->getTblGradeType()->getIsHighlighted()
                                ? new Bold($tblTestGrade->getGrade())
                                : $tblTestGrade->getGrade();

                            $tblTestGradeList[$tblPerson->getId()][$tblTestGrade->getId()] = $tblTestGrade;

                            if (!isset($tblTestList[$tblTest->getId()])) {
                                $tblTestList[$tblTest->getId()] = $tblTest;
                            }
                        }
                    }
                }
            }
        }

        return array($tblTestList, $tblTestGradeValueList, $tblTestGradeList, $personPeriodList);
    }

    /**
     * @param $personId
     * @param $gradeTextId
     *
     * @return SelectBox
     */
    public function getGradeTextSelectBox($personId, $gradeTextId): SelectBox
    {
        $global = $this->getGlobal();
        $global->POST['Data'][$personId]['GradeText'] = $gradeTextId;
        $global->savePost();

        return new SelectBox(
            'Data[' . $personId . '][GradeText]', '', array(TblGradeText::ATTR_NAME => Grade::useService()->getGradeTextAll())
        );
    }

    /**
     * @param $DivisionCourseId
     *
     * @return String
     */
    public function openGradeTextModal($DivisionCourseId): string
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return (new Danger('Kurs nicht gefunden', new Exclamation()));
        }

        $selectBox = new SelectBox(
            'GradeText',
            '',
            array(\SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeText::ATTR_NAME => Grade::useService()->getGradeTextAll())
        );

        return
            new Title('Stichtagsnote - Zeugnistext für den gesamten Kurs: ' . new Bold($tblDivisionCourse->getName()) . ' auswählen')
            . '<br>'
            . new Warning(
                'Es werden alle Zeugnistexte auf den gewählten Wert vorausgefüllt. Die Daten müssen anschließend noch gespeichert werden.',
                new Exclamation()
            )
            . new Well(new Form(new FormGroup(array(
                new FormRow(
                    new FormColumn(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        '<table><tr><td style="width:100px">&nbsp;Zeugnistext</td><td style="width:720px">' . $selectBox . '</td></tr></table>'
                    )))))
                ),
                new FormRow(
                    new FormColumn(
                        new Container('&nbsp;')
                    )
                ),
                new FormRow(
                    new FormColumn(
                        (new Primary('Übernehmen', ApiGradeBook::getEndpoint()))->ajaxPipelineOnClick(ApiGradeBook::pipelineSetGradeText($DivisionCourseId))
                    )
                )
            ))));
    }

    /**
     * @param $DivisionCourseId
     * @param $SubjectId
     * @param $Filter
     *
     * @return string
     */
    public function loadViewMinimumGradeCountContent($DivisionCourseId, $SubjectId, $Filter): string
    {
        $textKurs = "";
        $textSubject = "";
        $minimumGradeCountList = array();

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblSubject = Subject::useService()->getSubjectById($SubjectId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            $textKurs = new Bold($tblDivisionCourse->getDisplayName());
            $textSubject = new Bold($tblSubject->getDisplayName());
            $tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses();

            $bodyList = array();
            $integrationList = array();
            $pictureList = array();
            $courseList = array();
            $educationList = array();
            $schoolTypeLevelList = array();

            if ($tblPersonList) {
                foreach ($tblPersonList as $tblPerson) {
                    if (($tblVirtualSubject = DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject($tblPerson, $tblYear,
                            $tblSubject))
                        && $tblVirtualSubject->getHasGrading()
                    ) {
                        // Schüler-Informationen
                        Grade::useService()->setStudentInfo($tblPerson, $tblYear, $integrationList, $pictureList, $courseList);

                        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                            && $Level = $tblStudentEducation->getLevel()
                        ) {
                            $educationList[$tblPerson->getId()] = array('tblSchoolType' => $tblSchoolType, 'Level' => $Level);
                            if (!isset($schoolTypeLevelList[$tblSchoolType->getId()][$Level])) {
                                $tblMinimumGradeTypeList = Grade::useService()->getMinimumGradeCountListBySchoolTypeAndLevelAndSubject(
                                    $tblSchoolType, $Level, $tblSubject
                                );
                                if ($tblMinimumGradeTypeList) {
                                    foreach ($tblMinimumGradeTypeList as $tblMinimumGradeCount) {
                                        $minimumGradeCountList[$tblMinimumGradeCount->getId()] = $tblMinimumGradeCount;
                                        $schoolTypeLevelList[$tblSchoolType->getId()][$Level][$tblMinimumGradeCount->getId()] = $tblMinimumGradeCount;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $hasPicture = !empty($pictureList);
            $hasIntegration = !empty($integrationList);
            $hasCourse = !empty($courseList);
            $headerList = $this->getGradeBookPreHeaderList($hasPicture, $hasIntegration, $hasCourse);

            $count = 0;
            foreach ($minimumGradeCountList as $headerKey => $header) {
                $headerList[$headerKey] = $this->getTableColumnHead('#' . ++$count);
            }

            $count = 0;
            if ($tblPersonList) {
                foreach ($tblPersonList as $tblPerson) {
                    if (($tblVirtualSubject = DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))
                        && $tblVirtualSubject->getHasGrading()
                    ) {
                        $bodyList[$tblPerson->getId()] = $this->getGradeBookPreBodyList($tblPerson, ++$count, $hasPicture, $hasIntegration, $hasCourse,
                            $pictureList, $integrationList, $courseList);

                        if (isset($educationList[$tblPerson->getId()])) {
                            $tblSchoolType = $educationList[$tblPerson->getId()]['tblSchoolType'];
                            $schoolTypeId = $tblSchoolType->getId();
                            $level = $educationList[$tblPerson->getId()]['Level'];

                            foreach ($minimumGradeCountList as $key => $item) {
                                if (isset($schoolTypeLevelList[$schoolTypeId][$level][$key])
                                    && (!DivisionCourse::useService()->getIsCourseSystemBySchoolTypeAndLevel($tblSchoolType, $level)
                                        // SEKII-Kurse
                                        || ($tblVirtualSubject->getIsAdvancedCourse() && $item->getCourse() == SelectBoxItem::COURSE_ADVANCED)
                                        || (!$tblVirtualSubject->getIsAdvancedCourse() && $item->getCourse() == SelectBoxItem::COURSE_BASIC)
                                    )
                                ) {
                                    $number = Grade::useService()->getMinimumGradeCountNumberByPersonAndYearAndSubject(
                                        $item, $tblPerson, $tblYear, $tblSubject
                                    );
                                    if ($number < $item->getCount()){
                                        $contentPerson = new \SPHERE\Common\Frontend\Text\Repository\Warning(new Disable() . ' '. new Bold(
                                            $number . ' von ' . $item->getCount() . ' ' . $item->getGradeTypeDisplayShortName()
                                        ));
                                    } else {
                                        $contentPerson = new TextSuccess(new Ok() . ' ' . new Bold($number) . ' ' . $item->getGradeTypeDisplayShortName());
                                    }
                                } else {
                                    $contentPerson = '&nbsp;';
                                }
                                $bodyList[$tblPerson->getId()][$key] = $contentPerson;
                            }
                        }
                    }
                }
            }

            // table float
            $table = $this->getTableCustom($headerList, $bodyList);
            $content = $table;
        } else {
            $content = new Danger("Kurse oder Fach nicht gefunden.", new Exclamation());
        }

        return new Title(
                (new Standard("Zurück", ApiGradeBook::getEndpoint(), new ChevronLeft()))
                    ->ajaxPipelineOnClick(ApiGradeBook::pipelineLoadViewGradeBookContent($DivisionCourseId, $SubjectId, $Filter))
                . "&nbsp;&nbsp;&nbsp;&nbsp;Notenbuch - Mindestnotenanzahl"
                . new Muted(new Small(" für Kurs: ")) . $textKurs
                . new Muted(new Small(" im Fach: ")) . $textSubject
            )
            . ApiSupportReadOnly::receiverOverViewModal()
            . ApiPersonPicture::receiverModal()
            . $this->getMinimumGradeCountPanel($minimumGradeCountList)
            . $content;
    }

    /**
     * @param $tblMinimumGradeCountList
     *
     * @return false|Panel
     */
    private function getMinimumGradeCountPanel($tblMinimumGradeCountList)
    {
        if ($tblMinimumGradeCountList) {
            $minimumGradeCountContent = array();
            $count = 1;

            /** @var TblMinimumGradeCount $tblMinimumGradeCount */
            foreach ($tblMinimumGradeCountList as $tblMinimumGradeCount) {
                $minimumGradeCountContent[] = array(
                    'Number' => '#' . $count++,
                    'Level' => $tblMinimumGradeCount->getLevelListDisplayName(),
                    'Subject' => $tblMinimumGradeCount->getSubjectListDisplayName(),
                    'GradeType' => $tblMinimumGradeCount->getGradeTypeDisplayName(),
                    'Period' => $tblMinimumGradeCount->getPeriodDisplayName(),
                    'Course' => $tblMinimumGradeCount->getCourseDisplayName(),
                    'Count' => $tblMinimumGradeCount->getCount()
                );
            }

            if (!empty($minimumGradeCountContent)) {
                $columns = array(
                    'Number' => 'Nummer',
                    'Level' => 'Klassenstufe',
                    'Subject' => 'Fach',
                    'GradeType' => 'Zensuren-Typ',
                    'Period' => 'Zeitraum',
                    'Course' => 'SEKII - Kurs',
                    'Count' => 'Anzahl',
                );

                return new Panel(
                    'Mindestnotenanzahl',
                    '<div style="margin-top: -18px;">'.
                    new TableData(
                        $minimumGradeCountContent,
                        null,
                        $columns,
                        array(
                            'pageLength' => -1,
                            'paging' => false,
                            'info' => false,
                            'searching' => false,
                            'responsive' => false,
                            'ordering' => false
                        )
                    ).'</div>',
                    Panel::PANEL_TYPE_INFO
                );
            }
        }

        return false;
    }
}