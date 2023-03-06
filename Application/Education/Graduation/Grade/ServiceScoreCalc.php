<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRule;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTestGrade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;

abstract class ServiceScoreCalc extends ServiceScore
{
    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param array $tblGradeList
     * @param TblScoreRule|null $tblScoreRule
     * @param TblPeriod|null $tblPeriod
     *
     * @return array
     */
    public function getCalcStudentAverage(TblPerson $tblPerson, TblYear $tblYear, array $tblGradeList, ?TblScoreRule $tblScoreRule = null, ?TblPeriod $tblPeriod = null): array
    {
        $resultAverage = '';
        $scoreRuleText = '';
        $error = array();

        if (!empty($tblGradeList)) {
            $result = array();
            $averageGroup = array();
            $count = 0;
            $sum = 0;

            if ($tblScoreRule) {
                $tblScoreCondition = $this->getScoreConditionByStudent($tblPerson, $tblYear, $tblScoreRule, $tblGradeList, $tblPeriod);
                $scoreRuleText = 'Berechnungsvorschrift: ' . $tblScoreRule->getName();
                if ($tblScoreCondition) {
                    $scoreRuleText .= '<br>Berechnungsvariante ' . $tblScoreCondition->getPriority() . ': ' . $tblScoreCondition->getName();
                }
            } else {
                $tblScoreCondition = false;
            }

            // Teilnoten
            $subResult = array();
            /** @var TblTestGrade $tblGrade */
            foreach ($tblGradeList as $tblGrade) {
                if ($tblScoreCondition) {
                    if (($tblScoreConditionGroupListByCondition = Grade::useService()->getScoreConditionGroupListByCondition($tblScoreCondition))) {
                        $hasFoundGradeType = false;
                        foreach ($tblScoreConditionGroupListByCondition as $tblScoreGroup) {
                            if (($tblScoreGroupGradeTypeListByGroup = Grade::useService()->getScoreGroupGradeTypeListByGroup($tblScoreGroup->getTblScoreGroup()))) {
                                foreach ($tblScoreGroupGradeTypeListByGroup as $tblScoreGroupGradeTypeList) {
                                    if ($tblGrade->getTblGradeType() && $tblScoreGroupGradeTypeList->getTblGradeType()
                                        && $tblGrade->getTblGradeType()->getId() === $tblScoreGroupGradeTypeList->getTblGradeType()->getId()
                                    ) {
                                        $hasFoundGradeType = true;
                                        if ($tblGrade->getIsGradeNumeric()) {
                                            // für Teilnoten Extra-Liste
                                            if (($tblGradeType = $tblGrade->getTblGradeType())
                                                && $tblGradeType->getIsPartGrade()
                                            ) {
                                                if (isset($subResult[$tblGradeType->getId()])) {
                                                    $subResult[$tblGradeType->getId()]['SubCount']++;
                                                    $subResult[$tblGradeType->getId()]['SubValue'] += $tblGrade->getGradeNumberValue();
                                                } else {
                                                    $subResult[$tblGradeType->getId()] = array(
                                                        'tblScoreConditionId' => $tblScoreCondition->getId(),
                                                        'tblScoreGroupId' => $tblScoreGroup->getTblScoreGroup()->getId(),
                                                        'Multiplier' => floatval($tblScoreGroupGradeTypeList->getMultiplier()),
                                                        'SubValue' =>  $tblGrade->getGradeNumberValue(),
                                                        'SubCount' => 1
                                                    );
                                                }

                                            } else {
                                                $count++;
                                                $result[$tblScoreCondition->getId()][$tblScoreGroup->getTblScoreGroup()->getId()][$count]['Value']
                                                    = $tblGrade->getGradeNumberValue() * floatval($tblScoreGroupGradeTypeList->getMultiplier());
                                                $result[$tblScoreCondition->getId()][$tblScoreGroup->getTblScoreGroup()->getId()][$count]['Multiplier']
                                                    = floatval($tblScoreGroupGradeTypeList->getMultiplier());
                                            }
                                        }

                                        break;
                                    }
                                }
                            }
                        }

                        if (!$hasFoundGradeType && $tblGrade->getIsGradeNumeric() && $tblGrade->getTblGradeType()) {
                            $error[$tblGrade->getTblGradeType()->getId()] = 'Der Zensuren-Typ: ' . $tblGrade->getTblGradeType()->getDisplayName()
                                    . ' ist nicht in der Berechnungsvariante: ' . $tblScoreCondition->getName() . ' hinterlegt!';
                        }
                    }
                } else {
                    // alle Noten gleichwertig
                    if ($tblGrade->getIsGradeNumeric()) {
                        $count++;
                        $sum = $sum + $tblGrade->getGradeNumberValue();
                    }
                }
            }
            if (!empty($error)) {
                $average = new Bold(new Danger('Fehler'));
                return array($average,  $scoreRuleText, $error);
            }

            if (!$tblScoreCondition) {
                // alle Gleichwertig
                if ($count > 0) {
                    $resultAverage = Grade::useService()->getGradeAverage($sum, $count);
                }

                return array($resultAverage, $scoreRuleText, $error);
            }

            // Teilnoten zusammenführen -> Gesamt-Teilnote
            if (!empty($subResult)) {
                foreach ($subResult as $item) {
                    $count++;
                    $result[$item['tblScoreConditionId']][$item['tblScoreGroupId']][$count]['Value'] = ($item['SubValue'] / $item['SubCount']) * $item['Multiplier'];
                    $result[$item['tblScoreConditionId']][$item['tblScoreGroupId']][$count]['Multiplier'] = $item['Multiplier'];
                }
            }

            if (!empty($result)) {
                foreach ($result as $conditionId => $groups) {
                    if (!empty($groups)) {
                        foreach ($groups as $groupId => $group) {
                            if (!empty($group) && ($tblScoreGroupItem = Grade::useService()->getScoreGroupById($groupId))) {
                                $countGrades = 0;
                                foreach ($group as $value) {
                                    if ($tblScoreGroupItem->getIsEveryGradeASingleGroup()) {
                                        $countGrades++;
                                        $averageGroup[$conditionId][$groupId][$countGrades]['Value'] = $value['Value'];
                                        $averageGroup[$conditionId][$groupId][$countGrades]['Multiplier'] = $value['Multiplier'];
                                    } else {
                                        if (isset($averageGroup[$conditionId][$groupId])) {
                                            $averageGroup[$conditionId][$groupId]['Value'] += $value['Value'];
                                            $averageGroup[$conditionId][$groupId]['Multiplier'] += $value['Multiplier'];
                                        } else {
                                            $averageGroup[$conditionId][$groupId]['Value'] = $value['Value'];
                                            $averageGroup[$conditionId][$groupId]['Multiplier'] = $value['Multiplier'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (!empty($averageGroup[$tblScoreCondition->getId()])) {
                    $average = 0;
                    $totalMultiplier = 0;
                    foreach ($averageGroup[$tblScoreCondition->getId()] as $groupId => $group) {
                        if (($tblScoreGroup = Grade::useService()->getScoreGroupById($groupId))) {
                            $multiplier = floatval($tblScoreGroup->getMultiplier());
                            if ($tblScoreGroup->getIsEveryGradeASingleGroup() && is_array($group)) {
                                foreach ($group as $itemValue) {
                                    if (isset($itemValue['Value']) && isset($itemValue['Multiplier'])) {
                                        $totalMultiplier += $multiplier;
                                        $average += $multiplier * ($itemValue['Value'] / $itemValue['Multiplier']);
                                    }
                                }
                            } else {
                                if (isset($group['Value']) && isset($group['Multiplier'])) {
                                    $totalMultiplier += $multiplier;
                                    $average += $multiplier * ($group['Value'] / $group['Multiplier']);
                                }
                            }
                        }
                    }

                    if ($totalMultiplier > 0) {
                        $resultAverage = Grade::useService()->getGradeAverage($average, $totalMultiplier);
                    }
                }
            }
        }

        return array($resultAverage, $scoreRuleText, $error);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param array $tblGradeList
     * @param TblScoreRule|null $tblScoreRule
     * @param TblPeriod|null $tblPeriod
     *
     * @return string
     */
    public function getCalcStudentAverageToolTip(TblPerson $tblPerson, TblYear $tblYear, array $tblGradeList, ?TblScoreRule $tblScoreRule = null,
        ?TblPeriod $tblPeriod = null): string
    {
        list($average, $scoreRuleText, $error) = $this->getCalcStudentAverage($tblPerson, $tblYear, $tblGradeList, $tblScoreRule, $tblPeriod);

        return $this->getCalcStudentAverageToolTipByAverage($average, $scoreRuleText, $error);
    }

    /**
     * @param string $average
     * @param string $scoreRuleText
     * @param array $error
     *
     * @return string
     */
    public function getCalcStudentAverageToolTipByAverage(string $average, string $scoreRuleText, array $error): string
    {
        if ($scoreRuleText || !empty($error)) {
            return (new ToolTip($average, $scoreRuleText . (!empty($error) ? '<br>' . implode('<br>', $error) : '')))->enableHtml();
        }

        return $average;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblScoreRule $tblScoreRule
     * @param array $tblGradeList
     * @param TblPeriod|null $tblPeriod
     *
     * @return false|TblScoreCondition
     */
    public function getScoreConditionByStudent(TblPerson $tblPerson, TblYear $tblYear, TblScoreRule $tblScoreRule, array $tblGradeList, TblPeriod $tblPeriod = null) {
        $tblScoreCondition = false;
        if ($tblScoreConditionsByRule = Grade::useService()->getScoreConditionsByScoreRule($tblScoreRule)) {
            if (count($tblScoreConditionsByRule) > 1) {
                $tblScoreConditionsByRule = $this->getSorter($tblScoreConditionsByRule)->sortObjectBy('Priority');
                if ($tblScoreConditionsByRule) {
                    /** @var TblScoreCondition $item */
                    foreach ($tblScoreConditionsByRule as $item) {
                        $hasConditions = true;

                        // check period
                        if (($period = $item->getPeriod())) {
                            if (($tblPeriodList = $tblYear->getPeriodListByPerson($tblPerson))) {
                                $firstPeriod = reset($tblPeriodList);
                                if ($period == TblScoreCondition::PERIOD_FIRST_PERIOD) {
                                    if ($tblPeriod && $firstPeriod->getId() == $tblPeriod->getId()) {

                                    } else {
                                        $hasConditions = false;
                                    }
                                } elseif ($period == TblScoreCondition::PERIOD_SECOND_PERIOD) {
                                    if ($tblPeriod && $firstPeriod->getId() != $tblPeriod->getId()) {

                                    } else {
                                        $hasConditions = false;
                                    }
                                }
                            }
                        }

                        // check gradeTypes
                        if (($tblScoreConditionGradeTypeListByCondition = Grade::useService()->getScoreConditionGradeTypeListByCondition($item))) {
                            foreach ($tblScoreConditionGradeTypeListByCondition as $tblScoreConditionGradeTypeList) {
                                $countMinimum = $tblScoreConditionGradeTypeList->getCount();
                                $countGradeType = 0;
                                if (($tblGradeType = $tblScoreConditionGradeTypeList->getTblGradeType())) {
                                    /** @var TblTestGrade $tblGrade */
                                    foreach ($tblGradeList as $tblGrade) {
                                        if ($tblGrade->getIsGradeNumeric()
                                            && $tblGrade->getTblGradeType()
                                            && ($tblGrade->getTblGradeType()->getId() == $tblGradeType->getId())
                                        ) {
                                            $countGradeType++;
                                        }
                                    }

                                    if ($countGradeType < $countMinimum) {
                                        $hasConditions = false;
                                    }
                                }
                            }
                        }

                        // check group requirements
                        if (($tblScoreConditionGroupRequirementList = Grade::useService()->getScoreConditionGroupRequirementAllByCondition($item))) {
                            foreach ($tblScoreConditionGroupRequirementList as $tblScoreConditionGroupRequirement) {
                                $countMinimum = $tblScoreConditionGroupRequirement->getCount();
                                $countGradeTypes = 0;
                                if (($tblScoreGroup = $tblScoreConditionGroupRequirement->getTblScoreGroup())
                                    && ($tblGradeTypeList = Grade::useService()->getScoreGroupGradeTypeListByGroup($tblScoreGroup))
                                ) {
                                    $gradeTypeList = array();
                                    foreach ($tblGradeTypeList as $tblGradeTypeItem) {
                                        if (($tblGradeType = $tblGradeTypeItem->getTblGradeType())){
                                            $gradeTypeList[$tblGradeType->getId()] = $tblGradeType;
                                        }
                                    }

                                    /** @var TblTestGrade $tblGrade */
                                    foreach ($tblGradeList as $tblGrade) {
                                        if ($tblGrade->getIsGradeNumeric()
                                            && $tblGrade->getTblGradeType()
                                            && isset($gradeTypeList[$tblGrade->getTblGradeType()->getId()])
                                        ) {
                                            $countGradeTypes++;
                                        }
                                    }

                                    if ($countGradeTypes < $countMinimum) {
                                        $hasConditions = false;
                                    }
                                }
                            }
                        }

                        if ($hasConditions) {
                            $tblScoreCondition = $item;
                            break;
                        }
                    }
                }
            } else {
                $tblScoreCondition = $tblScoreConditionsByRule[0];
            }
        }

        return $tblScoreCondition;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     * @param TblDivisionCourse|null $tblDivisionCourseItem
     *
     * @return false|TblScoreRule
     */
    public function getScoreRuleByPersonAndYearAndSubject(TblPerson $tblPerson, TblYear $tblYear, TblSubject $tblSubject,
        ?TblDivisionCourse $tblDivisionCourseItem = null)
    {
        // SekII-Kurse
        if ($tblDivisionCourseItem
            && ($temp = $this->getScoreRuleSubjectDivisionCourseByDivisionCourseAndSubject($tblDivisionCourseItem, $tblSubject))
            && ($tblScoreRule = $temp->getTblScoreRule())
        ) {
            return $tblScoreRule;
        }

        if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByStudentAndYear($tblPerson, $tblYear))) {
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                if (($temp = $this->getScoreRuleSubjectDivisionCourseByDivisionCourseAndSubject($tblDivisionCourse, $tblSubject))
                    && ($tblScoreRule = $temp->getTblScoreRule())
                ) {
                    return $tblScoreRule;
                }
            }
        }

        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
            && ($temp = $this->getScoreRuleSubjectByYearAndSchoolTypeAndLevelAndSubject($tblYear, $tblSchoolType, $tblStudentEducation->getLevel(), $tblSubject))
            && ($tblScoreRule = $temp->getTblScoreRule())
        ) {
            return $tblScoreRule;
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     *
     * @return false|TblScoreRule[]
     */
    public function getScoreRuleListByDivisionCourseAndSubject(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject)
    {
        $tblScoreRuleList = array();
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            foreach ($tblPersonList as $tblPerson) {
                if (($tblScoreRule = $this->getScoreRuleByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject, $tblDivisionCourse))
                    && (!isset($tblScoreRuleList[$tblScoreRule->getId()]))
                ) {
                    $tblScoreRuleList[$tblScoreRule->getId()] = $tblScoreRule;
                }
            }
        }

        return empty($tblScoreRuleList) ? false : $tblScoreRuleList;
    }
}