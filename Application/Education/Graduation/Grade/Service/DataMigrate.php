<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask as TblTaskOld;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest as TblTestOld;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCount;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCountLevelLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCountSubjectLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreConditionGradeTypeList;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreConditionGroupList;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreConditionGroupRequirement;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreGroup;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreGroupGradeTypeList;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRule;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRuleConditionList;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTaskCourseLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTaskGrade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTaskGradeTypeLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTestCourseLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTestGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType as TblGradeTypeOld;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\System\Database\Binding\AbstractData;

abstract class DataMigrate extends AbstractData
{
    /**
     * @return int
     */
    public function migrateTblGradeType(): int
    {
        $count = 0;
        if (($tblGradeTypeList = Gradebook::useService()->getGradeTypeAll())) {
            $tblGradeTypeList = $this->getSorter($tblGradeTypeList)->sortObjectBy('Id');
            $Manager = $this->getEntityManager();
            /** @var TblGradeTypeOld $item */
            foreach ($tblGradeTypeList as $item) {
                $isTypeBehavior = ($tblTestType = $item->getServiceTblTestType()) && $tblTestType->getIdentifier() == 'BEHAVIOR';
                $tblGradeType = new TblGradeType(
                    $item->getCode(), $item->getName(), $item->getDescription(),
                    $isTypeBehavior, $item->isHighlighted(), $item->isPartGrade(), $item->isActive(), $item->getId()
                );
                // beim Speichern mit vorgegebener Id ist kein bulkSave möglich
                $Manager->saveEntityWithSetId($tblGradeType);
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param TblYear $tblYear
     * @param array $tblDivisionList
     *
     * @return float
     */
    public function migrateTests(TblYear $tblYear, array $tblDivisionList): float
    {
        ini_set('memory_limit', '2G');
        $start = hrtime(true);

        if ($tblDivisionList) {
            $tblGradeTypeList = array();
            if (($tblTempList = Grade::useService()->getGradeTypeAll(true))) {
                foreach ($tblTempList as $temp) {
                    $tblGradeTypeList[$temp->getCode()] = $temp;
                }
            }

            $Manager = $this->getEntityManager();
            /** @var TblDivision $tblDivisionTemp */
            foreach ($tblDivisionList as $tblDivision) {
                $isCourseSystem = Division::useService()->getIsDivisionCourseSystem($tblDivision);
                if (($tblTestOldList = Evaluation::useService()->getTestAllByDivision($tblDivision))) {
                    /** @var TblTestOld $item */
                    foreach ($tblTestOldList as $item) {
                        // gelöschte ignorieren
                        if ($item->getEntityRemove()) {
                            continue;
                        }

                        if (($tblTestType = $item->getTblTestType())
                            && ($tblSubject = $item->getServiceTblSubject())
                        ) {
                            /*
                             * Leistungsüberprüfung
                             */
                            if ($tblTestType->getIdentifier() == 'TEST') {
                                if (($tblGradeTypeOld = $item->getServiceTblGradeType())
                                    && isset($tblGradeTypeList[$tblGradeTypeOld->getCode()])
                                ) {
                                    $tblTest = new TblTest(
                                        $tblYear, $tblSubject, $tblGradeTypeList[$tblGradeTypeOld->getCode()],
                                        $item->getDateTime(), $item->getFinishDateTime(), $item->getCorrectionDateTime(), $item->getReturnDateTime(),
                                        $item->isContinues(), $item->getDescription(), null, $item->getId()
                                    );
                                    // beim Speichern mit vorgegebener Id ist kein bulkSave möglich
                                    $Manager->saveEntityWithSetId($tblTest);

                                    // Kurse verknüpfen (alte Klasse)
                                    if (!$isCourseSystem) {
                                        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($tblDivision->getId()))) {
                                            $tblTestCourseLink = new TblTestCourseLink($tblTest, $tblDivisionCourse);
                                            $Manager->bulkSaveEntity($tblTestCourseLink);
                                        }
                                        // mit SekII-Kurs verknüpfen
                                    } else {
                                        if (($tblSubjectGroup = $item->getServiceTblSubjectGroup())
                                            && ($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseByMigrateSekCourse(
                                                Division::useService()->getMigrateSekCourseString($tblDivision, $tblSubject, $tblSubjectGroup)
                                            ))
                                        ) {
                                            $tblTestCourseLink = new TblTestCourseLink($tblTest, $tblDivisionCourse);
                                            $Manager->bulkSaveEntity($tblTestCourseLink);
                                        }
                                    }

                                    // Zensuren
                                    if (($tblGradeOldList = Gradebook::useService()->getGradeAllByTest($item))) {
                                        foreach ($tblGradeOldList as $tblGradeOld) {
                                            if (($tblStudent = $tblGradeOld->getServiceTblPerson())) {
                                                $grade = $tblGradeOld->getGrade() === null ? null : $tblGradeOld->getDisplayGrade();
                                                $tblTestGrade = new TblTestGrade(
                                                    $tblStudent, $tblTest, $tblGradeOld->getDateTime(), $grade,
                                                    $tblGradeOld->getComment(), $tblGradeOld->getPublicComment(),
                                                    $tblGradeOld->getServiceTblPersonTeacher() ?: null
                                                );
                                                $Manager->bulkSaveEntity($tblTestGrade);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $Manager->flushCache();
        }

        $end = hrtime(true);

        return round(($end - $start) / 1000000000, 2);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return float
     */
    public function migrateTasks(TblYear $tblYear): float
    {
        ini_set('memory_limit', '2G');
        $start = hrtime(true);

        $Manager = $this->getEntityManager();

        $tblGradeTextList = array();
        if (($tblTempList = Grade::useService()->getGradeTextAll())) {
            foreach ($tblTempList as $tblTemp) {
                $tblGradeTextList[$tblTemp->getIdentifier()] = $tblTemp;
            }
        }

        $tblScoreTypeList = array();
        if (($tblTemp2List = Grade::useService()->getScoreTypeAll())) {
            foreach ($tblTemp2List as $tblTemp2) {
                $tblScoreTypeList[$tblTemp2->getIdentifier()] = $tblTemp2;
            }
        }

        $tblGradeTypeList = array();
        if (($tblTemp3List = Grade::useService()->getGradeTypeAll(true))) {
            foreach ($tblTemp3List as $tblTemp3) {
                $tblGradeTypeList[$tblTemp3->getCode()] = $tblTemp3;
            }
        }

        $tblTaskOldList = array();
        if (($tblAppointedTaskList = Evaluation::useService()->getTaskAllByTestType(
            Evaluation::useService()->getTestTypeByIdentifier(TblTestType::APPOINTED_DATE_TASK),
            $tblYear
        ))) {
            $tblTaskOldList = array_merge($tblTaskOldList, $tblAppointedTaskList);
        }
        if (($tblBehaviorTaskList = Evaluation::useService()->getTaskAllByTestType(
            Evaluation::useService()->getTestTypeByIdentifier(TblTestType::BEHAVIOR_TASK),
            $tblYear
        ))) {
            $tblTaskOldList = array_merge($tblTaskOldList, $tblBehaviorTaskList);
        }

        /** @var TblTaskOld $tblTaskOld */
        foreach ($tblTaskOldList as $tblTaskOld) {
            // gelöschte ignorieren
            if ($tblTaskOld->getEntityRemove()) {
                continue;
            }

            $isTypeBehavior = $tblTaskOld->getTblTestType()->getIdentifier() == TblTestType::BEHAVIOR_TASK;
            $tblScoreType = null;
            if (($tblScoreTypeOld = $tblTaskOld->getServiceTblScoreType())) {
                $tblScoreType = $tblScoreTypeList[$tblScoreTypeOld->getIdentifier()];
            }

            $tblTask = new TblTask(
                $tblTaskOld->getServiceTblYear(), $isTypeBehavior, $tblTaskOld->getName(),
                $tblTaskOld->getDateTime(), $tblTaskOld->getFromDateTime(), $tblTaskOld->getToDateTime(),
                $tblTaskOld->isAllYears(),  $tblScoreType?: null, $tblTaskOld->getId()
            );
            // beim Speichern mit vorgegebener Id ist kein bulkSave möglich
            $Manager->saveEntityWithSetId($tblTask);

            $divisionCourseList = array();
            $gradeTypeListByTask = array();
            if (($tblTestOldList = Evaluation::useService()->getTestAllByTask($tblTaskOld))) {
                foreach ($tblTestOldList as $item) {
                    // Kurse linken aber nur einmal
                    if (($tblDivision = $item->getServiceTblDivision())) {
                        if (!isset($divisionCourseList[$tblDivision->getId()])
                            && ($temp = DivisionCourse::useService()->getDivisionCourseById($tblDivision->getId()))
                        ) {
                            $divisionCourseList[$temp->getId()] = $temp;
                        }
                    }

                    // Zensuren
                    if (($tblGradeOldList = Gradebook::useService()->getGradeAllByTest($item))) {
                        foreach ($tblGradeOldList as $tblGradeOld) {
                            if (($tblStudent = $tblGradeOld->getServiceTblPerson())
                                && ($tblSubject = $tblGradeOld->getServiceTblSubject())
                            ) {
                                if (($tblGradeTextOld = $tblGradeOld->getTblGradeText())) {
                                    $tblGradeText = $tblGradeTextList[$tblGradeTextOld->getIdentifier()];
                                    $grade = null;
                                } else {
                                    $tblGradeText = null;
                                    $grade = $tblGradeOld->getGrade() === null ? null : $tblGradeOld->getDisplayGrade();
                                }

                                if (($tblGradeTypeOld = $tblGradeOld->getTblGradeType())) {
                                    $tblGradeTypeNew = $tblGradeTypeList[$tblGradeTypeOld->getCode()];
                                } else {
                                    $tblGradeTypeNew = null;
                                }

                                $tblTaskGrade = new TblTaskGrade(
                                    $tblStudent, $tblSubject, $tblTask, $tblGradeTypeNew, $grade, $tblGradeText,
                                    $tblGradeOld->getComment(), $tblGradeOld->getServiceTblPersonTeacher() ?: null
                                );
                                $Manager->bulkSaveEntity($tblTaskGrade);
                            }
                        }
                    }

                    // Zensuren-Typen bei Kopfnotenauftrag
                    if (($tblGradeTypeOld = $item->getServiceTblGradeType())
                        && (!isset($gradeTypeListByTask[$tblGradeTypeOld->getCode()]))
                        && isset($tblGradeTypeList[$tblGradeTypeOld->getCode()])
                    ) {
                        $tblGradeTypeNew = $tblGradeTypeList[$tblGradeTypeOld->getCode()];
                        $gradeTypeListByTask[$tblGradeTypeNew->getCode()] = $tblGradeTypeNew;
                        $tblTestGradeTypeLink = new TblTaskGradeTypeLink($tblTask, $tblGradeTypeNew);
                        $Manager->bulkSaveEntity($tblTestGradeTypeLink);
                    }
                }
            }
            // Kurse verknüpfen
            foreach ($divisionCourseList as $tblDivisionCourse) {
                $tblTestCourseLink = new TblTaskCourseLink($tblTask, $tblDivisionCourse);
                $Manager->bulkSaveEntity($tblTestCourseLink);
            }
        }

        $Manager->flushCache();

        $end = hrtime(true);

        return round(($end - $start) / 1000000000, 2);
    }

    /**
     * @return array
     */
    public function migrateScoreRules(): array
    {
        $count = 0;
        $start = hrtime(true);

        $Manager = $this->getEntityManager();

        $tblGradeTypeList = array();
        if (($tblTempList = Grade::useService()->getGradeTypeAll(true))) {
            foreach ($tblTempList as $tblTemp) {
                $tblGradeTypeList[$tblTemp->getCode()] = $tblTemp;
            }
        }

        // TblScoreGroup
        $tblScoreGroupList = array();
        if (($tblTempList = Gradebook::useService()->getScoreGroupAll())) {
            foreach ($tblTempList as $tblScoreGroupOld) {
                $tblScoreGroup = new TblScoreGroup($tblScoreGroupOld->getName(), $tblScoreGroupOld->getMultiplier(),
                    $tblScoreGroupOld->isEveryGradeASingleGroup(), $tblScoreGroupOld->isActive(), $tblScoreGroupOld->getId());
                $Manager->saveEntityWithSetId($tblScoreGroup);
                $tblScoreGroupList[$tblScoreGroup->getId()] = $tblScoreGroup;

                // TblScoreGroupGradeTypeList
                if (($tblScoreGroupGradeTypeList = Gradebook::useService()->getScoreGroupGradeTypeListByGroup($tblScoreGroupOld))) {
                    foreach ($tblScoreGroupGradeTypeList as $item) {
                        if (($tblGradeTypeOld = $item->getTblGradeType())) {
                            $tblGradeType = $tblGradeTypeList[$tblGradeTypeOld->getCode()];
                            $Manager->bulkSaveEntity(new TblScoreGroupGradeTypeList($item->getMultiplier(), $tblGradeType, $tblScoreGroup));
                        }
                    }
                }
            }
        }

        // TblScoreCondition
        $tblScoreConditionList = array();
        if (($tblTempList = Gradebook::useService()->getScoreConditionAll())) {
            foreach ($tblTempList as $tblScoreConditionOld) {
                $tblScoreCondition = new TblScoreCondition($tblScoreConditionOld->getName(), $tblScoreConditionOld->getPriority(),
                    $tblScoreConditionOld->getPeriod(), $tblScoreConditionOld->isActive(), $tblScoreConditionOld->getId());
                $Manager->saveEntityWithSetId($tblScoreCondition);
                $tblScoreConditionList[$tblScoreCondition->getId()] = $tblScoreCondition;

                // TblScoreConditionGradeTypeList
                if (($tblScoreConditionGradeTypeList = Gradebook::useService()->getScoreConditionGradeTypeListByCondition($tblScoreConditionOld))) {
                    foreach ($tblScoreConditionGradeTypeList as $item) {
                        if (($tblGradeTypeOld = $item->getTblGradeType())) {
                            $tblGradeType = $tblGradeTypeList[$tblGradeTypeOld->getCode()];
                            $Manager->bulkSaveEntity(new TblScoreConditionGradeTypeList($item->getCount(), $tblGradeType, $tblScoreCondition));
                        }
                    }
                }

                // TblScoreConditionGroupList
                if (($tblScoreConditionGroupList = Gradebook::useService()->getScoreConditionGroupListByCondition($tblScoreConditionOld))) {
                    foreach ($tblScoreConditionGroupList as $item) {
                        if (($tblScoreGroupOld = $item->getTblScoreGroup())) {
                            $tblScoreGroup = $tblScoreGroupList[$tblScoreGroupOld->getId()];
                            $Manager->bulkSaveEntity(new TblScoreConditionGroupList($tblScoreGroup, $tblScoreCondition));
                        }
                    }
                }

                // TblScoreConditionGroupRequirement
                if (($tblScoreConditionGroupRequirementList = Gradebook::useService()->getScoreConditionGroupRequirementAllByCondition($tblScoreConditionOld))) {
                    foreach ($tblScoreConditionGroupRequirementList as $item) {
                        if (($tblScoreGroupOld = $item->getTblScoreGroup())) {
                            $tblScoreGroup = $tblScoreGroupList[$tblScoreGroupOld->getId()];
                            $Manager->bulkSaveEntity(new TblScoreConditionGroupRequirement($item->getCount(), $tblScoreGroup, $tblScoreCondition));
                        }
                    }
                }
            }
        }

        // TblScoreRule
        if (($tblScoreRuleList = Gradebook::useService()->getScoreRuleAll())) {
            foreach ($tblScoreRuleList as $tblScoreRuleOld) {
                $count++;
                $tblScoreRule = new TblScoreRule($tblScoreRuleOld->getName(), $tblScoreRuleOld->getDescription(),
                    $tblScoreRuleOld->getDescriptionForExtern(), $tblScoreRuleOld->isActive(), $tblScoreRuleOld->getId());
                $Manager->saveEntityWithSetId($tblScoreRule);

                // TblScoreRuleGradeTypeList
                if (($tblScoreRuleConditionList = Gradebook::useService()->getScoreRuleConditionListByRule($tblScoreRuleOld))) {
                    foreach ($tblScoreRuleConditionList as $item) {
                        if (($tblScoreConditionOld = $item->getTblScoreCondition())) {
                            $tblScoreCondition = $tblScoreConditionList[$tblScoreConditionOld->getId()];
                            $Manager->bulkSaveEntity(new TblScoreRuleConditionList($tblScoreCondition, $tblScoreRule));
                        }
                    }
                }
            }
        }

        $Manager->flushCache();

        $end = hrtime(true);

        return array($count, round(($end - $start) / 1000000000, 2));
    }

    /**
     * @return array
     */
    public function migrateMinimumGradeCounts(): array
    {
        $count = 0;
        $start = hrtime(true);

        $Manager = $this->getEntityManager();

        $tblGradeTypeList = array();
        if (($tblTempList = Grade::useService()->getGradeTypeAll(true))) {
            foreach ($tblTempList as $tblTemp) {
                $tblGradeTypeList[$tblTemp->getCode()] = $tblTemp;
            }
        }

        if (!Grade::useService()->getMinimumGradeCountAll()
            && ($tblMinimumGradeCountListOld = Gradebook::useService()->getMinimumGradeCountAll())
        ) {
            $list = array();
            foreach ($tblMinimumGradeCountListOld as $tblMinimumGradeCountOld) {
                $tblGradeTypeOld = $tblMinimumGradeCountOld->getTblGradeType();
                if (!isset($list['H' . $tblMinimumGradeCountOld->getHighlighted()
                    . 'G' . ($tblGradeTypeOld ? $tblGradeTypeOld->getId() : 0)
                    . 'P' . $tblMinimumGradeCountOld->getPeriod()
                    . 'C' . $tblMinimumGradeCountOld->getCourse()
                    . 'N' . $tblMinimumGradeCountOld->getCount()])
                ) {
                    $list['H' . $tblMinimumGradeCountOld->getHighlighted()
                    . 'G' . ($tblGradeTypeOld ? $tblGradeTypeOld->getId() : 0)
                    . 'P' . $tblMinimumGradeCountOld->getPeriod()
                    . 'C' . $tblMinimumGradeCountOld->getCourse()
                    . 'N' . $tblMinimumGradeCountOld->getCount()] = array(
                        'Id' => $tblMinimumGradeCountOld->getId(),
                        'GradeType' => $tblGradeTypeOld ? $tblGradeTypeOld->getCode() : null,
                        'Period' => $tblMinimumGradeCountOld->getPeriod(),
                        'Course' => $tblMinimumGradeCountOld->getCourse(),
                        'Count' => $tblMinimumGradeCountOld->getCount(),
                        'Highlighted' => $tblMinimumGradeCountOld->getHighlighted()
                    );
                }

                if (($tblLevel = $tblMinimumGradeCountOld->getServiceTblLevel())) {
                    $list['H' . $tblMinimumGradeCountOld->getHighlighted()
                    . 'G' . ($tblGradeTypeOld ? $tblGradeTypeOld->getId() : 0)
                    . 'P' . $tblMinimumGradeCountOld->getPeriod()
                    . 'C' . $tblMinimumGradeCountOld->getCourse()
                    . 'N' . $tblMinimumGradeCountOld->getCount()]
                    ['Levels'][$tblLevel->getId()] = $tblLevel->getName();
                }
                if ($tblSubject = $tblMinimumGradeCountOld->getServiceTblSubject()) {
                    $list['H' . $tblMinimumGradeCountOld->getHighlighted()
                    . 'G' . ($tblGradeTypeOld ? $tblGradeTypeOld->getId() : 0)
                    . 'P' . $tblMinimumGradeCountOld->getPeriod()
                    . 'C' . $tblMinimumGradeCountOld->getCourse()
                    . 'N' . $tblMinimumGradeCountOld->getCount()]
                    ['Subjects'][$tblSubject->getId()] = $tblSubject;

                    if ($tblLevel) {
                        $list['H' . $tblMinimumGradeCountOld->getHighlighted()
                        . 'G' . ($tblGradeTypeOld ? $tblGradeTypeOld->getId() : 0)
                        . 'P' . $tblMinimumGradeCountOld->getPeriod()
                        . 'C' . $tblMinimumGradeCountOld->getCourse()
                        . 'N' . $tblMinimumGradeCountOld->getCount()]
                        ['SubjectsLevelVerify'][$tblSubject->getId()][$tblLevel->getId()] = $tblLevel->getName();
                    }
                }
            }

            foreach ($list as $item) {
                $count++;
                $tblMinimumGradeCount = new TblMinimumGradeCount(
                    $item['Count'], $tblGradeTypeList[$item['GradeType']] ?? null, $item['Period'], $item['Highlighted'], $item['Course']
                );
                $Manager->saveEntity($tblMinimumGradeCount);

                if (isset($item['Levels'])) {
                    foreach ($item['Levels'] as $levelId => $value) {
                        if (($tblLevel = Division::useService()->getLevelById($levelId))
                            && ($tblSchoolType = $tblLevel->getServiceTblType())
                        ) {
                            $Manager->bulkSaveEntity(
                                new TblMinimumGradeCountLevelLink($tblMinimumGradeCount, $tblSchoolType, intval($tblLevel->getName()))
                            );
                        }
                    }
                }
                if (isset($item['Subjects'])) {
                    // es kann Fächer geben, wo die Mindestnoten nicht für alle Fächer gelten
                    $extraSubjectList = array();
                    if (isset($item['SubjectsLevelVerify'])) {
                        $countMaxLevels = isset($item['Levels']) ? count($item['Levels']) : 0;
                        foreach ($item['SubjectsLevelVerify'] as $subjectId => $levels) {
                            if (count($levels) < $countMaxLevels) {
                                foreach($levels as $tempId => $name) {
                                    $extraSubjectList[$subjectId][$tempId] = $name;
                                }
                            }
                        }
                    }

                    foreach ($item['Subjects'] as $tblSubject) {
                        if (!isset($extraSubjectList[$tblSubject->getId()])) {
                            $Manager->bulkSaveEntity(new TblMinimumGradeCountSubjectLink($tblMinimumGradeCount, $tblSubject));
                        }
                    }

                    if (!empty($extraSubjectList)) {
                        foreach ($extraSubjectList as $subjectIdExtra => $levelList)
                        {
                            if (($tblSubjectExtra = Subject::useService()->getSubjectById($subjectIdExtra))) {
                                $count++;
                                $tblMinimumGradeCountExtra = new TblMinimumGradeCount(
                                    $item['Count'], $tblGradeTypeList[$item['GradeType']] ?? null, $item['Period'], $item['Highlighted'], $item['Course']
                                );
                                $Manager->saveEntity($tblMinimumGradeCountExtra);
                                $Manager->bulkSaveEntity(new TblMinimumGradeCountSubjectLink($tblMinimumGradeCountExtra, $tblSubjectExtra));
                                foreach ($levelList as $levelIdExtra => $levelName) {
                                    if (($tblLevelExtra = Division::useService()->getLevelById($levelIdExtra))
                                        && ($tblSchoolTypeExtra = $tblLevelExtra->getServiceTblType())
                                    ) {
                                        $Manager->bulkSaveEntity(
                                            new TblMinimumGradeCountLevelLink($tblMinimumGradeCountExtra, $tblSchoolTypeExtra, intval($tblLevelExtra->getName()))
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $Manager->flushCache();

        $end = hrtime(true);

        return array($count, round(($end - $start) / 1000000000, 2));
    }
}