<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Api\Document\Standard\Repository\GradebookOverview\GradebookOverview;
use SPHERE\Application\Api\ParentStudentAccess\ApiOnlineGradebook;
use SPHERE\Application\Document\Generator\Repository\Section;
use SPHERE\Application\Document\Generator\Repository\Slice;
use SPHERE\Application\Education\Graduation\Grade\Service\VirtualTestTask;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
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
            $isShortYear = DivisionCourse::useService()->getIsShortYearBySchoolTypeAndLevel($tblSchoolType, $tblStudentEducation->getLevel());
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

                if ($tblDivisionCourseList) {
                    foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                        if (($tblTestList = Grade::useService()->getTestListByDivisionCourseAndSubject($tblDivisionCourse, $tblSubject))) {
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
                                    $countColumns[$periodNumber]++;
                                    $virtualTestTaskList[$tblSubject->getId()][$periodNumber][] = new VirtualTestTask($date, $tblTest);
                                }
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


        if (!empty($virtualTestTaskList)) {
            foreach($virtualTestTaskList as $subjectId => $list) {
                if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
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
                        if (isset($list[$i])) {
                            $tempList = $this->getSorter($list[$i])->sortObjectBy('Date', new DateTimeSorter());
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
                                    $tblScoreRule ?? null);
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
                                $tblScoreRule ?? null);
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
}