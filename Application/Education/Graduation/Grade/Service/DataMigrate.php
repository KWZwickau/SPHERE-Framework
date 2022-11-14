<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service;

use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask as TblTaskOld;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest as TblTestOld;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTaskCourseLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTaskGrade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTestCourseLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTestGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType as TblGradeTypeOld;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
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
                                        $item->isContinues(), $item->getDescription(), $item->getId()
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

                                $tblTaskGrade = new TblTaskGrade(
                                    $tblStudent, $tblSubject, $tblTask, $grade, $tblGradeText,
                                    $tblGradeOld->getComment(), $tblGradeOld->getServiceTblPersonTeacher() ?: null
                                );
                                $Manager->bulkSaveEntity($tblTaskGrade);
                            }
                        }
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
}