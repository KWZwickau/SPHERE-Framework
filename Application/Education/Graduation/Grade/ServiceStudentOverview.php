<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Education\Graduation\Grade\Service\VirtualTestTask;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
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

        return array($isShownAverage, $isShownDivisionSubjectScore, $isShownGradeMirror, $tblSchoolTypeList, $startYear, $isScoreRuleShown);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblStudentEducation $tblStudentEducation
     * @param bool $IsParentView
     *
     * @return void
     */
    public function getStudentOverviewDataByPerson(TblPerson $tblPerson, TblYear $tblYear, TblStudentEducation $tblStudentEducation,
        TblDivisionCourse $tblDivisionCourse, bool $IsParentView): string
    {
        $headerList = array();
        $bodyList = array();
        $countMaxColumn = 5;
        if ($IsParentView) {
            list($isShownAverage, $isShownDivisionSubjectScore, $isShownGradeMirror, $tblSchoolTypeList, $startYear, $isScoreRuleShown)
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

        if (($tblTaskList = Grade::useService()->getTaskListByStudentAndYear($tblPerson, $tblYear))) {

        }
        $tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByStudentAndYear($tblPerson, $tblYear);

        $frontend = Grade::useFrontend();
        $headerList['Subject'] = $frontend->getTableColumnHead('Fach');
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
                                // todo is add

                                // todo zukünftige Große Noten bei entsprechender Einstellung

                                if (($tblTestGrade = Grade::useService()->getTestGradeByTestAndPerson($tblTest, $tblPerson))) {
                                    // nicht teilgenommen
                                    if ($tblTestGrade->getGrade() === null) {
                                        continue;
                                    }
                                    $tblTestGradeList[$tblTest->getId()] = $tblTestGrade;
                                }

                                $date = $tblTest->getDate() ?: $tblTest->getFinishDate();
                                $periodNumber = $date > $halfYearDate ? 2 : 1;
                                $countColumns[$periodNumber]++;
                                $virtualTestTaskList[$tblSubject->getId()][$periodNumber][] = new VirtualTestTask($date, $tblTest);
                            }
                        }
                    }
                }

                if ($tblTaskList) {
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
        $countMaxColumn++;

        if ($tblPeriodList) {
            foreach($tblPeriodList as $tblPeriod) {
                $headerList[$tblPeriod->getId()] = $frontend->getTableColumnHead($tblPeriod->getDisplayName(), true, null, $countMaxColumn);
            }
            $headerList['Average'] = $frontend->getTableColumnHead('&#216;');
        }

        $widthAcronym = '10%';
        $widthGrade = (45 / $countMaxColumn) . '%';

        if (!empty($virtualTestTaskList)) {
            foreach($virtualTestTaskList as $subjectId => $list) {
                if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                    $data = array();
                    $data['Subject'] = $frontend->getTableColumnBody($tblSubject->getAcronym(), $frontend::BACKGROUND_COLOR, $widthAcronym);
                    $testGrades = array();
                    $testGrades['All'] = array();
                    $tblScoreRule = Grade::useService()->getScoreRuleByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject, $tblDivisionCourse);

                    for ($i = 1; $i < 3; $i++) {
                        $count = 0;
                        if (isset($list[$i])) {
                            // todo tooltip thema, Notenspiegel Fach-klassendurchschnitt
                            $tempList = $this->getSorter($list[$i])->sortObjectBy('Date', new DateTimeSorter());
                            /** @var VirtualTestTask $virtualTestTask */
                            foreach ($tempList as $virtualTestTask) {
                                $count++;
                                switch ($virtualTestTask->getType()) {
                                    case VirtualTestTask::TYPE_TEST:
                                        $testId = $virtualTestTask->getTblTest()->getId();
                                        $tblTestGrade = $tblTestGradeList[$testId] ?? null;
                                        $dateItem = $tblTestGrade && $tblTestGrade->getDate() ? $tblTestGrade->getGrade() : $virtualTestTask->getDate();
                                        $data[] = $frontend->getTableColumnBody(
                                            $dateItem->format('d.m.') . '<br>'
                                            . $virtualTestTask->getTblTest()->getTblGradeType()->getCode() . '<br>'
                                            . ($tblTestGrade ? $tblTestGrade->getGrade() : '&nbsp;'),
                                            null,
                                            $widthGrade
                                        );
                                        if ($tblTestGrade) {
                                            $testGrades[$i][] = $tblTestGrade;
                                        }
                                        break;
                                    case VirtualTestTask::TYPE_TASK:
                                        $data[] = $frontend->getTableColumnBody(
                                            new Bold(
                                                $virtualTestTask->getDate()->format('d.m.') . '<br>'
                                                . 'SN' . '<br>'
                                                . (($tblTaskGrade = Grade::useService()->getTaskGradeByPersonAndTaskAndSubject($tblPerson, $virtualTestTask->getTblTask(), $tblSubject))
                                                    ? $tblTaskGrade->getGrade() : '&nbsp;')
                                            ),
                                            $frontend::BACKGROUND_COLOR,
                                            $widthGrade
                                        );
                                }
                            }
                        }

                        // leere Spalten
                        while ($count < $countMaxColumn - 1) {
                            $count++;
                            $data[] = $frontend->getTableColumnBody('&nbsp;');
                        }

                        if (isset($testGrades[$i])) {
                            list ($average, $scoreRuleText, $error) = Grade::useService()->calcStudentAverage($tblPerson, $tblYear, $testGrades[$i], $tblScoreRule ?? null);
                            $toolTip = Grade::useService()->getCalcStudentAverageToolTipByAverage($average, $scoreRuleText, $error);

                            $testGrades['All'] = array_merge($testGrades['All'], $testGrades[$i]);
                        } else {
                            $toolTip = '';
                        }

                        // Notendurchschnitt pro Halbjahr
                        $data[] = $frontend->getTableColumnBody(
                            new Bold(
                                '&nbsp;' . '<br>'
                                . '&#216;' . '<br>'
                                . $toolTip
                            ),
                            $frontend::BACKGROUND_COLOR,
                            $widthGrade
                        );
                    }

                    // Gesamt-Notendurchschnitt
                    if (!empty($testGrades['All'])) {
                        list ($average, $scoreRuleText, $error) = Grade::useService()->calcStudentAverage($tblPerson, $tblYear, $testGrades['All'], $tblScoreRule ?? null);
                        $toolTip = Grade::useService()->getCalcStudentAverageToolTipByAverage($average, $scoreRuleText, $error);
                    } else {
                        $toolTip = '';
                    }
                    $data[] = $frontend->getTableColumnBody(
                        new Bold(
                            '&nbsp;' . '<br>'
                            . '&nbsp;' . '<br>'
                            . $toolTip
                        ),
                        $frontend::BACKGROUND_COLOR,
                        $widthGrade
                    );

                    $bodyList[] = $data;
                }
            }
        }

        $content = $frontend->getTableCustom($headerList, $bodyList);

        return $content;
    }
}