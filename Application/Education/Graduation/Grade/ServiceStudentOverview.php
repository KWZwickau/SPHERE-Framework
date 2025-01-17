<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Document\Standard\Repository\GradebookOverview\GradebookOverview;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiStudentOverview;
use SPHERE\Application\Api\ParentStudentAccess\ApiOnlineGradebook;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\Graduation\Grade\Service\VirtualTestTask;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

abstract class ServiceStudentOverview extends ServiceScoreCalc
{
    /**
     * Achtung wird auch für den Eltern/Schüler-Zugang verwendet
     *
     * @return array
     */
    public function getConsumerSettingsForGradeOverview(): array
    {
        // Mandant-Einstellungen für Notenübersicht (Schüler/Eltern) und Schülerübersicht (Ansicht: Eltern/Schüler)
        // !!!! wichtig: immer beide anpassen bei einer neuen Einstellung !!!!!!

        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Graduation', 'Gradebook', 'IsShownAverageInStudentOverview'
            ))
            && $tblSetting->getValue()
        ) {
            $isShownAverage = true;
        } else {
            $isShownAverage = false;
        }

        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Graduation', 'Gradebook', 'IsShownDivisionSubjectScoreInStudentOverview'
            ))
            && $tblSetting->getValue()
        ) {
            $isShownDivisionSubjectScore = true;
        } else {
            $isShownDivisionSubjectScore = false;
        }

        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Graduation', 'Gradebook', 'IsShownGradeMirrorInStudentOverview'
            ))
            && $tblSetting->getValue()
        ) {
            $isShownGradeMirror = true;
        } else {
            $isShownGradeMirror = false;
        }

        // erlaubte Schularten:
        $tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'IgnoreSchoolType');
        $tblSchoolTypeList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue());
        if($tblSchoolTypeList){
            // erzeuge eine Id Liste, wenn Schularten blockiert werden
            foreach ($tblSchoolTypeList as &$tblSchoolTypeControl){
                $tblSchoolTypeControl = $tblSchoolTypeControl->getId();
            }
        }

        // Schuljahre Anzeigen ab:
        $startYear = '';
        $tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'YearOfUserView');
        if($tblSetting){
            $YearTempId = $tblSetting->getValue();
            if ($YearTempId && ($tblYearTemp = Term::useService()->getYearById($YearTempId))){
                $startYear = ($tblYearTemp->getYear() ? $tblYearTemp->getYear() : $tblYearTemp->getName());
            }
        }

        if (($tblSetting = Consumer::useService()->getSetting('ParentStudentAccess', 'OnlineGradebook', 'OnlineGradebook' , 'IsScoreRuleShown'))
            && $tblSetting->getValue()
        ) {
            $isScoreRuleShown = true;
        } else {
            $isScoreRuleShown = false;
        }

        // fettmarkierte Tests wie Klassenarbeiten anzeigen
        if (($tblSetting = Consumer::useService()->getSetting(
                'Education', 'Graduation', 'Gradebook', 'ShowHighlightedTestsInGradeOverview'))
            && $tblSetting->getValue()
        ) {
            $showHighlightedTestsInGradeOverview = true;
        } else {
            $showHighlightedTestsInGradeOverview = false;
        }

        // automatische Bekanntgabe nach X Tagen
        if (($tblSetting = Consumer::useService()->getSetting(
            'Education', 'Graduation', 'Evaluation', 'AutoPublicationOfTestsAfterXDays'))
        ) {
            $AutoPublicationOfTestsAfterXDays = intval($tblSetting->getValue());
        } else {
            $AutoPublicationOfTestsAfterXDays = 28;
        }

        return array($isShownAverage, $isShownDivisionSubjectScore, $isShownGradeMirror, $tblSchoolTypeList, $startYear, $isScoreRuleShown,
            $showHighlightedTestsInGradeOverview, $AutoPublicationOfTestsAfterXDays);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblStudentEducation $tblStudentEducation
     * @param bool $IsParentView
     * @param bool $IsPdf
     *
     * @return string|Slice
     */
    public function getStudentOverviewDataByPerson(TblPerson $tblPerson, TblYear $tblYear, TblStudentEducation $tblStudentEducation,
        bool $IsParentView, bool $IsPdf)
    {
        $countMaxColumn = 5;
        $withSubjectNumber = $IsPdf ? 5 : 10;
        $widthSubject = $withSubjectNumber . '%';

        $headerList = array();
        $headerPdfSection = new Section();

        $bodyList = array();
        $bodyPdfSectionList = array();

        if ($IsParentView) {
            list($isShownAverage, $isShownDivisionSubjectScore, $isShownGradeMirror, $tblSchoolTypeList, $startYear, $isScoreRuleShown,
                $showHighlightedTestsInGradeOverview, $AutoPublicationOfTestsAfterXDays)
                = $this->getConsumerSettingsForGradeOverview();
            $isShownAppointedDateGrade = false;
        } else {
            $isShownAverage = true;
            $isShownDivisionSubjectScore = true;
            $isShownGradeMirror = true;
            $isShownAppointedDateGrade = true;
            $tblSchoolTypeList = false;
            $startYear = '';
            $isScoreRuleShown = false;
            $showHighlightedTestsInGradeOverview = true;
            $AutoPublicationOfTestsAfterXDays = 28;
        }

        if (($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())) {
            $isShortYear = DivisionCourse::useService()->getIsShortYearBySchoolTypeAndLevel($tblSchoolType, $tblStudentEducation->getLevel() ?: 0);
        } else {
            $isShortYear = false;
        }

        if ($IsParentView) {
            // Schulart Prüfung nur, wenn auch Schularten in den Einstellungen erlaubt werden.
            if($tblSchoolTypeList && (!$tblSchoolType || !in_array($tblSchoolType->getId(), $tblSchoolTypeList))) {
                return new Warning('Die Schulart des Schüler ist nicht für die Notenübersicht freigegeben');
            }

            // Anzeige nur für Schuljahre die nach dem "Startschuljahr"(Veröffentlichung) liegen
            if ($tblYear->getYear() < $startYear) {
                return new Warning('Das Schuljahr ist nicht für die Notenübersicht freigegeben');
            }
        }

        $tblTaskList = Grade::useService()->getTaskListByStudentAndYear($tblPerson, $tblYear);
        $taskDate = null;
        // automatische Bekanntgabe durch den Stichtagsnotenauftrag
        if ($IsParentView && $tblTaskList) {
            foreach ($tblTaskList as $tblTask) {
                if ($tblTask->getIsTypeBehavior()) {
                    continue;
                }

                if (Grade::useService()->getTaskGradeListByTaskAndPerson($tblTask, $tblPerson)) {
                    $taskDate = $tblTask->getDate();
                }
            }
        }
        $tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByStudentAndYear($tblPerson, $tblYear);

        $frontend = Grade::useFrontend();
        $headerList['Subject'] = $frontend->getTableColumnHead('Fach');
        $headerPdfSection->addElementColumn(GradebookOverview::getHeaderElement('Fach', true), $widthSubject);
        $halfYearDate = false;
        if (($tblPeriodList = Term::useService()->getPeriodListByYear($tblYear, $isShortYear))) {
            foreach($tblPeriodList as $tblPeriod) {
                if (!$halfYearDate) {
                    $halfYearDate = $tblPeriod->getToDateTime();
                }
            }
        }

        $virtualTestTaskList = array();
        $tblTestGradeList = array();
        $hideTestInfoList = array();
        if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByStudentAndYear($tblPerson, $tblYear))) {
            $tblSubjectList = $this->getSorter($tblSubjectList)->sortObjectBy('DisplayName', new StringNaturalOrderSorter());
            /** @var TblSubject $tblSubject */
            foreach ($tblSubjectList as $tblSubject) {
                $countColumns[1] = 0;
                $countColumns[2] = 0;

                // SEKII
                if (DivisionCourse::useService()->getIsCourseSystemByStudentEducation($tblStudentEducation)
                    && ($tblStudentSubject = DivisionCourse::useService()->getStudentSubjectByPersonAndYearAndSubjectForCourseSystem($tblPerson, $tblYear, $tblSubject))
                ) {
                    $tblDivisionCourseTempList = array();
                    if ($tblStudentSubject->getTblDivisionCourse()) {
                        $tblDivisionCourseTempList[] = $tblStudentSubject->getTblDivisionCourse();
                    }
                } else {
                    $tblDivisionCourseTempList = $tblDivisionCourseList;
                }

                $tblTestList = array();
                if ($tblDivisionCourseTempList) {
                    foreach ($tblDivisionCourseTempList as $tblDivisionCourse) {
                        $tblTestList = array_merge($tblTestList, Grade::useService()->getTestListByDivisionCourseAndSubject($tblDivisionCourse, $tblSubject, true));
                    }
                }
                // Tests auch von den Zensuren, wo der Schüler nicht mehr im Kurs sitzt
                if (($tblTestGradeExtraList = Grade::useService()->getTestGradeListByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))) {
                    foreach ($tblTestGradeExtraList as $tblTestGradeExtra) {
                        if ($tblTestGradeExtra->getGrade() !== null
                            && ($tblTestExtra = $tblTestGradeExtra->getTblTest())
                            && $tblTestExtra->getEntityRemove() == null
                            && !isset($tblTestList[$tblTestExtra->getId()])
                        ) {
                            $tblTestList[$tblTestExtra->getId()] = $tblTestExtra;
                        }
                    }
                }

                if (!empty($tblTestList)) {
                    foreach ($tblTestList as $tblTest) {
                        $tblTestGrade = Grade::useService()->getTestGradeByTestAndPerson($tblTest, $tblPerson);
                        $isAddTest = false;
                        if (!$IsParentView || $tblTest->getIsShownInParentView($tblTestGrade ?: null, $taskDate ?: null, $AutoPublicationOfTestsAfterXDays)) {
                            $isAddTest = true;
                            if ($tblTestGrade) {
                                // nicht teilgenommen
                                if ($tblTestGrade->getGrade() === null) {
                                    continue;
                                }
                                $tblTestGradeList[$tblTest->getId()] = $tblTestGrade;
                            }
                            // zukünftige große Noten bei entsprechender Einstellung
                        } elseif ($showHighlightedTestsInGradeOverview && $tblTest->getTblGradeType()->getIsHighlighted()) {
                            $isAddTest = true;
                            // notenspiegel und co darf nicht angezeigt werden
                            $hideTestInfoList[$tblTest->getId()] = $tblTest;
                        }

                        if($isAddTest) {
                            $date = $tblTest->getDate() ?: $tblTest->getFinishDate();
                            $periodNumber = $date > $halfYearDate ? 2 : 1;
                            if (!isset($virtualTestTaskList[$tblSubject->getId()][$periodNumber][$tblTest->getId()])) {
                                $countColumns[$periodNumber]++;
                                $virtualTestTaskList[$tblSubject->getId()][$periodNumber][$tblTest->getId()] = new VirtualTestTask($date, $tblTest);
                            }
                        }
                    }
                }

                if ($isShownAppointedDateGrade && $tblTaskList) {
                    foreach ($tblTaskList as $tblTask) {
                        // Kopfnoten werden nicht mit angezeigt
                        if ($tblTask->getIsTypeBehavior()) {
                            continue;
                        }

                        $periodNumber = $tblTask->getDate() > $halfYearDate ? 2 : 1;
                        $countColumns[$periodNumber]++;
                        $virtualTestTaskList[$tblSubject->getId()][$periodNumber][] = new VirtualTestTask($tblTask->getDate(), null, $tblTask);
                    }
                }

                if ($countColumns[1] > $countMaxColumn) {
                    $countMaxColumn = $countColumns[1];
                }
                if ($countColumns[2] > $countMaxColumn) {
                    $countMaxColumn = $countColumns[2];
                }
            }
        }

        // für Durchschnitt des Halbjahres
        if ($isShownAverage) {
            $countMaxColumn++;
        }

        // Berechnung der breite für eine Note
        $widthGradeNumber = (100 - $withSubjectNumber) / (2 * $countMaxColumn + ($isShownAverage ? 1 : 0));
        $widthGrade = $widthGradeNumber . '%';

        if ($tblPeriodList) {
            foreach($tblPeriodList as $tblPeriod) {
                $headerList[$tblPeriod->getId()] = $frontend->getTableColumnHead($tblPeriod->getDisplayName(), true, null, $countMaxColumn);
                $headerPdfSection->addElementColumn(GradebookOverview::getHeaderElement($tblPeriod->getDisplayName()), ($countMaxColumn * $widthGradeNumber) . '%');
            }
            if ($isShownAverage) {
                $headerList['Average'] = $frontend->getTableColumnHead('&#216;');
                $headerPdfSection->addElementColumn(GradebookOverview::getHeaderElement('&#216;'), $widthGrade);
            }
        }

        if ($tblSubjectList) {
            foreach ($tblSubjectList as $tblSubject) {
                $tblScoreRule = Grade::useService()->getScoreRuleByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject);
                $tblScoreType = Grade::useService()->getScoreTypeByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject);

                $data = array();
                $dataPdfSection = new Section();

                $data['Subject'] = $frontend->getTableColumnBody(
                    new Bold($tblSubject->getName())
                    . ($isScoreRuleShown && $tblScoreRule
                        ? (new Link('', ApiOnlineGradebook::getEndpoint(), new Info(), array(),
                            'Berechnungsvorschrift für dieses Fach anzeigen'))
                            ->ajaxPipelineOnClick(ApiOnlineGradebook::pipelineOpenScoreRuleModal($tblScoreRule->getId()))
                        : ''),
                    $frontend::BACKGROUND_COLOR, $widthSubject
                );
                $dataPdfSection->addElementColumn(GradebookOverview::getHeaderElement($tblSubject->getAcronym(), true), $widthSubject);

                $testGrades = array();
                $testGrades['All'] = array();

                for ($i = 1; $i < 3; $i++) {
                    $count = 0;
                    if (isset($virtualTestTaskList[$tblSubject->getId()][$i])) {
                        $tempList = $this->getVirtualTestTaskListSorted($virtualTestTaskList[$tblSubject->getId()][$i]);
                        /** @var VirtualTestTask $virtualTestTask */
                        foreach ($tempList as $virtualTestTask) {
                            $count++;
                            switch ($virtualTestTask->getType()) {
                                case VirtualTestTask::TYPE_TEST:
                                    $tblTest = $virtualTestTask->getTblTest();
                                    $testId = $tblTest->getId();
                                    $tblTestGrade = $tblTestGradeList[$testId] ?? null;
                                    $dateItem = $tblTestGrade && $tblTestGrade->getDate() ? $tblTestGrade->getDate() : $virtualTestTask->getDate();
                                    $isBold = $tblTest->getTblGradeType()->getIsHighlighted();
                                    $toolTip = '';
                                    if (!$IsPdf) {
                                        if ($tblTest->getDescription()) {
                                            $toolTip .= 'Thema: ' . $tblTest->getDescription();
                                        }
                                        if (!isset($hideTestInfoList[$testId])) {
                                            if ($isShownGradeMirror && $tblScoreType
                                                && ($gradeMirror = Grade::useService()->getGradeMirrorForToolTipByTest($tblTest, $tblScoreType))
                                            ) {
                                                $toolTip .= ($toolTip ? '<br />' : '') . $gradeMirror;
                                            }
                                            if ($isShownDivisionSubjectScore && ($averageTest = Grade::useService()->getGradeAverageByTest($tblTest))) {
                                                $toolTip .= ($toolTip ? '<br />' : '') . '&#216; ' . $averageTest;
                                            }
                                        }
                                    }
                                    $contentTemp = $virtualTestTask->getTblTest()->getTblGradeType()->getCode() . '<br>'
                                        . ($tblTestGrade ? $tblTestGrade->getGrade() : '&nbsp;');
                                    $contentTest = ($dateItem ? $dateItem->format('d.m.') : '&nbsp;') . '<br>'
                                        . ($isBold ? new Bold($contentTemp) : $contentTemp);

                                    $publicComment = $tblTestGrade && $tblTestGrade->getPublicComment() ? $tblTestGrade->getPublicComment() : '';

                                    $data[] = $frontend->getTableColumnBody(
                                        ($toolTip ? (new ToolTip($contentTest, htmlspecialchars($toolTip)))->enableHtml() : $contentTest)
                                        . ($publicComment ?  ' ' . new ToolTip(new Info(), $publicComment) : ''),
                                        null,
                                        $widthGrade
                                    );
                                    $dataPdfSection->addElementColumn(GradebookOverview::getBodyElement($contentTest), $widthGrade);

                                    if ($tblTestGrade) {
                                        $testGrades[$i][] = $tblTestGrade;
                                    }
                                    break;
                                case VirtualTestTask::TYPE_TASK:
                                    $contentTask = $virtualTestTask->getDate()->format('d.m.') . '<br>'
                                        . 'SN' . '<br>'
                                        . (($tblTaskGrade = Grade::useService()->getTaskGradeByPersonAndTaskAndSubject($tblPerson, $virtualTestTask->getTblTask(), $tblSubject))
                                        && $tblTaskGrade->getGrade() ? $tblTaskGrade->getGrade() : '&nbsp;');

                                    $data[] = $frontend->getTableColumnBody(new Bold($contentTask), $frontend::BACKGROUND_COLOR, $widthGrade);
                                    $dataPdfSection->addElementColumn(GradebookOverview::getBodyElement($contentTask, true, true), $widthGrade);
                            }
                        }
                    }

                    // leere Spalten
                    while ($count < $countMaxColumn - ($isShownAverage ? 1 : 0)) {
                        $count++;

                        $data[] = $frontend->getTableColumnBody('&nbsp;');
                        $dataPdfSection->addElementColumn(GradebookOverview::getBodyElement('&nbsp;<br>&nbsp;<br>&nbsp;'), $widthGrade);
                    }

                    // Notendurchschnitt pro Halbjahr
                    if ($isShownAverage) {
                        if (isset($testGrades[$i])) {
                            list ($average, $scoreRuleText, $error) = Grade::useService()->getCalcStudentAverage($tblPerson, $tblYear, $testGrades[$i],
                                $tblScoreRule ?: null);
                            $toolTip = Grade::useService()->getCalcStudentAverageToolTipByAverage($average, $scoreRuleText, $error);

                            $testGrades['All'] = array_merge($testGrades['All'], $testGrades[$i]);
                        } else {
                            $average = '&nbsp;';
                            $toolTip = '';
                        }

                        $data[] = $frontend->getTableColumnBody(
                            new Bold('&nbsp;' . '<br>' . '&#216;' . '<br>' . $toolTip),
                            $frontend::BACKGROUND_COLOR,
                            $widthGrade
                        );
                        $dataPdfSection->addElementColumn(GradebookOverview::getBodyElement('&nbsp;' . '<br>' . '&#216;' . '<br>' . $average, true, true), $widthGrade);
                    }
                }

                // Gesamt-Notendurchschnitt
                if ($isShownAverage) {
                    if (!empty($testGrades['All'])) {
                        list ($average, $scoreRuleText, $error) = Grade::useService()->getCalcStudentAverage($tblPerson, $tblYear, $testGrades['All'],
                            $tblScoreRule ?: null);
                        $toolTip = Grade::useService()->getCalcStudentAverageToolTipByAverage($average, $scoreRuleText, $error);
                    } else {
                        $average = '&nbsp;';
                        $toolTip = '';
                    }

                    $data[] = $frontend->getTableColumnBody(
                        new Bold('&nbsp;' . '<br>' . '&nbsp;' . '<br>' . $toolTip),
                        $frontend::BACKGROUND_COLOR,
                        $widthGrade
                    );
                    $dataPdfSection->addElementColumn(GradebookOverview::getBodyElement('&nbsp;' . '<br>' . '&nbsp;' . '<br>' . $average, true, true), $widthGrade);
                }

                $bodyList[] = $data;
                $bodyPdfSectionList[] = $dataPdfSection;
            }
        }

        if ($IsPdf) {
            $slice = (new Slice())->addSection($headerPdfSection);
            if (!empty($bodyPdfSectionList)) {
                $slice->addSectionList($bodyPdfSectionList);
            }

            return $slice->styleBorderBottom();
        } else {
            return ($frontend->getTableCustom($headerList, $bodyList))->__toString();
        }
    }

    /**
     * @param array $virtualTestTaskList
     *
     * @return array
     */
    private function getVirtualTestTaskListSorted(array $virtualTestTaskList): array
    {
        $isSortedByHighlighted = ($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'SortHighlighted'))
            && $tblSetting->getValue();
        $isSortedToRight = ($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Gradebook', 'IsHighlightedSortedRight'))
            && $tblSetting->getValue();
        if ($isSortedByHighlighted) {
            $tempList = array();
            $resultList = array();
            /** @var VirtualTestTask $virtualTestTask */
            foreach ($virtualTestTaskList as $virtualTestTask) {
                switch ($virtualTestTask->getType()) {
                    case VirtualTestTask::TYPE_TEST:
                        $tblTest = $virtualTestTask->getTblTest();
                        if (($tblGradeType = $tblTest->getTblGradeType()) && $tblGradeType->getIsHighlighted()) {
                            $tempList['Highlighted'][] = $virtualTestTask;
                        } else {
                            $tempList['NotHighlighted'][] = $virtualTestTask;
                        }
                        break;
                    case VirtualTestTask::TYPE_TASK:
                        $tempList['Default'][] = $virtualTestTask;
                        break;
                    case VirtualTestTask::TYPE_PERIOD:
                        $tempList['Default'][] = $virtualTestTask;
                }
            }

            $highlightedList = array();
            if (isset($tempList['Highlighted'])) {
                $highlightedList = $this->getSorter($tempList['Highlighted'])->sortObjectBy('Date', new DateTimeSorter());
            }
            $notHighlightedList = array();
            if (isset($tempList['NotHighlighted'])) {
                $notHighlightedList = $this->getSorter($tempList['NotHighlighted'])->sortObjectBy('Date', new DateTimeSorter());
            }
            $defaultList = array();
            if (isset($tempList['Default'])) {
                $defaultList = $this->getSorter($tempList['Default'])->sortObjectBy('Date', new DateTimeSorter());
            }

            if ($isSortedToRight) {
                if (!empty($notHighlightedList)) {
                    $resultList = array_merge($resultList, $notHighlightedList);
                }
                if (!empty($highlightedList)) {
                    $resultList = array_merge($resultList, $highlightedList);
                }
            } else {
                if (!empty($highlightedList)) {
                    $resultList = array_merge($resultList, $highlightedList);
                }
                if (!empty($notHighlightedList)) {
                    $resultList = array_merge($resultList, $notHighlightedList);
                }
            }

            if (!empty($defaultList)) {
                $resultList = array_merge($resultList, $defaultList);
            }

            return $resultList;
        } else {
            return $this->getSorter($virtualTestTaskList)->sortObjectBy('Date', new DateTimeSorter());
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param $Filter
     * @param bool $isPdf
     * @param TblPeriod|null $tblPeriod
     *
     * @return array
     */
    public function getStudentOverviewCourseData(TblDivisionCourse $tblDivisionCourse, $Filter, bool $isPdf, ?TblPeriod $tblPeriod = null): array
    {
        $bodyList = array();
        $headerList = array();

        if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            $integrationList = array();
            $pictureList = array();
            $courseList = array();

            $tblSubjectList = array();
            $tblPersonList = array();
            $inactiveStudentList = array();
            if (($tblDivisionCourseMemberList = $tblDivisionCourse->getStudentsWithSubCourses(true, false))) {
                /** @var TblDivisionCourseMember $tblDivisionCourseMember */
                foreach ($tblDivisionCourseMemberList as $tblDivisionCourseMember) {
                    if (($tblPersonTemp = $tblDivisionCourseMember->getServiceTblPerson())) {
                        // Schüler-Informationen
                        Grade::useService()->setStudentInfo($tblPersonTemp, $tblYear, $integrationList, $pictureList, $courseList);
                        $tblPersonList[$tblPersonTemp->getId()] = $tblPersonTemp;

                        if ($tblDivisionCourseMember->isInActive()) {
                            $inactiveStudentList[$tblPersonTemp->getId()] = $tblPersonTemp;
                        }
                    }
                }

                $tblSubjectList = DivisionCourse::useService()->getSubjectListByPersonListAndYear($tblPersonList, $tblYear);
            }

            $hasPicture = !empty($pictureList);
            $hasIntegration = !empty($integrationList);
            $hasCourse = !empty($courseList);
            $headerList = Grade::useFrontend()->getGradeBookPreHeaderList($hasPicture, $hasIntegration, $hasCourse);
            if ($tblSubjectList) {
                $tblSubjectList = $this->getSorter($tblSubjectList)->sortObjectBy('DisplayName', new StringNaturalOrderSorter());
                /** @var TblSubject $tblSubject */
                foreach ($tblSubjectList as $tblSubject) {
                    $headerList[$tblSubject->getId()] = Grade::useFrontend()->getTableColumnHead($tblSubject->getAcronym());
                }
            } else {
                $tblSubjectList = array();
            }
            $headerList['Option'] = Grade::useFrontend()->getTableColumnHead('');

            $averageSumList = array();
            $averageCountList = array();
            if ($tblPersonList) {
                $count = 0;
                foreach ($tblPersonList as $tblPerson) {
                    $bodyList[$tblPerson->getId()] = Grade::useFrontend()->getGradeBookPreBodyList($tblPerson, ++$count, $hasPicture, $hasIntegration, $hasCourse,
                        $pictureList, $integrationList, $courseList, isset($inactiveStudentList[$tblPerson->getId()]));

                    foreach ($tblSubjectList as $tblSubject) {
                        // Schüler Berechnungsvorschrift ermitteln
                        $tblScoreRule = Grade::useService()->getScoreRuleByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject, $tblDivisionCourse);

                        // Zensuren abhängig vom Halbjahr
                        if ($tblPeriod) {
                            $tblTestGradeList = Grade::useService()->getTestGradeListBetweenDateTimesByPersonAndYearAndSubject(
                                $tblPerson, $tblYear, $tblSubject, $tblPeriod->getFromDateTime(), $tblPeriod->getToDateTime()
                            );
                        } else {
                            $tblTestGradeList = Grade::useService()->getTestGradeListByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject);
                        }

                        if ($tblTestGradeList) {
                            list ($average, $scoreRuleText, $error) = Grade::useService()->getCalcStudentAverage($tblPerson, $tblYear, $tblTestGradeList, $tblScoreRule ?: null, $tblPeriod ?: null);
                            $contentSubject = '&#216; '
                                . ($isPdf
                                    ? $average
                                    : Grade::useService()->getCalcStudentAverageToolTipByAverage($average, $scoreRuleText, $error));
                            $average = Grade::useService()->getGradeNumberValue($average);
                            if (isset($averageSumList[$tblSubject->getId()])) {
                                $averageSumList[$tblSubject->getId()] += $average;
                            } else {
                                $averageSumList[$tblSubject->getId()] = $average;
                            }
                            if (isset($averageCountList[$tblSubject->getId()])) {
                                $averageCountList[$tblSubject->getId()]++;
                            } else {
                                $averageCountList[$tblSubject->getId()] = 1;
                            }
                        } else {
                            $contentSubject = '';
                        }

                        $bodyList[$tblPerson->getId()][$tblSubject->getId()] = Grade::useFrontend()->getTableColumnBody($contentSubject);
                    }

                    $bodyList[$tblPerson->getId()]['Option'] = Grade::useFrontend()->getTableColumnBody((new Standard("", ApiStudentOverview::getEndpoint(), new EyeOpen(), array(), "Schülerübersicht anzeigen"))
                        ->ajaxPipelineOnClick(ApiStudentOverview::pipelineLoadViewStudentOverviewStudentContent($tblDivisionCourse->getId(), $tblPerson->getId(), $Filter, 'All')));
                }
            }

            // Fach-Klassen-Durchschnitt
            $rowDataList = array();
            foreach ($headerList as $key => $value) {
                $contentTemp = '';
                if ($key == 'Person') {
                    $contentTemp = Grade::useFrontend()->getTableColumnBody(new Muted('&#216; Fach-Kurs'));
                } elseif (isset($averageSumList[$key])) {
                    $contentTemp = Grade::useFrontend()->getTableColumnBody('&#216; ' . Grade::useService()->getGradeAverage($averageSumList[$key], $averageCountList[$key]));
                }
                $rowDataList[$key] = $contentTemp;
            }
            $bodyList[-1] = $rowDataList;
        }

        return array($bodyList, $headerList);
    }
}