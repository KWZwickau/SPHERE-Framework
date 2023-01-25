<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Education\Graduation\Grade\Service\Data;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCount;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCountLevelLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCountSubjectLink;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;

abstract class ServiceMinimumGradeCount extends ServiceGradeType
{
    /**
     * @param $Id
     *
     * @return false|TblMinimumGradeCount
     */
    public function getMinimumGradeCountById($Id)
    {
        return (new Data($this->getBinding()))->getMinimumGradeCountById($Id);
    }

    /**
     * @return false|TblMinimumGradeCount[]
     */
    public function getMinimumGradeCountAll()
    {
        return (new Data($this->getBinding()))->getMinimumGradeCountAll();
    }

    /**
     * @return array
     */
    public function migrateMinimumGradeCounts(): array
    {
        return (new Data($this->getBinding()))->migrateMinimumGradeCounts();
    }

    /**
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     *
     * @return false|TblMinimumGradeCountLevelLink[]
     */
    public function getMinimumGradeCountLevelLinkByMinimumGradeCount(TblMinimumGradeCount $tblMinimumGradeCount)
    {
        return (new Data($this->getBinding()))->getMinimumGradeCountLevelLinkByMinimumGradeCount($tblMinimumGradeCount);
    }

    /**
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     *
     * @return false|TblMinimumGradeCountSubjectLink[]
     */
    public function getMinimumGradeCountSubjectLinkByMinimumGradeCount(TblMinimumGradeCount $tblMinimumGradeCount)
    {
        return (new Data($this->getBinding()))->getMinimumGradeCountSubjectLinkByMinimumGradeCount($tblMinimumGradeCount);
    }

    /**
     * @param IFormInterface|null $form
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function createMinimumGradeCount(?IFormInterface $form, $Data)
    {
        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $form;
        }

        $Error = false;
        if (isset($Data['Count']) && empty($Data['Count'])) {
            $form->setError('Data[Count]', 'Bitte geben Sie eine Anzahl an');
            $Error = true;
        }
        // message for level required
        if (!isset($Data['Levels'])) {
            $form->prependGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie mindestens eine Klassenstufe aus.', new Exclamation())))));
            $Error = true;
        }

        if (!$Error) {
            if ($Data['GradeType'] < 0) {
                $highlighted = -$Data['GradeType'];
                $tblGradeType = false;
            } else {
                $highlighted = SelectBoxItem::HIGHLIGHTED_ALL;
                $tblGradeType = Grade::useService()->getGradeTypeById($Data['GradeType']);
            }

            if (($tblMinimumGradeCount = (new Data($this->getBinding()))->createMinimumGradeCount(
                $Data['Count'], $tblGradeType ?: null, $Data['Period'], $highlighted, $Data['Course']
            ))) {
                $createLevelList = array();
                $createSubjectList = array();
                if (isset($Data['Levels'])) {
                    foreach ($Data['Levels'] as $schoolTypeId => $levelList) {
                        if (($tblSchoolType = Type::useService()->getTypeById($schoolTypeId))) {
                            foreach ($levelList as $level => $value) {
                                $createLevelList[] = new TblMinimumGradeCountLevelLink($tblMinimumGradeCount, $tblSchoolType, $level);
                            }
                        }
                    }
                }
                if (isset($Data['Subjects'])) {
                    foreach ($Data['Subjects'] as $subjectId => $value) {
                        if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                            $createSubjectList[] = new TblMinimumGradeCountSubjectLink($tblMinimumGradeCount, $tblSubject);
                        }
                    }
                }

                if (!empty($createLevelList)) {
                    Grade::useService()->createEntityListBulk($createLevelList);
                }
                if (!empty($createSubjectList)) {
                    Grade::useService()->createEntityListBulk($createSubjectList);
                }
            }

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Mindestnotenanzahl ist gespeichert worden.')
                . new Redirect('/Education/Graduation/Grade/MinimumGradeCount', Redirect::TIMEOUT_SUCCESS);
        }

        return $form;
    }

    /**
     * @param IFormInterface|null $form
     * @param $Data
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     *
     * @return IFormInterface|string
     */
    public function updateMinimumGradeCount(?IFormInterface $form, $Data, TblMinimumGradeCount $tblMinimumGradeCount)
    {
        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $form;
        }

        $Error = false;
        if (isset($Data['Count']) && empty($Data['Count'])) {
            $form->setError('Data[Count]', 'Bitte geben Sie eine Anzahl an');
            $Error = true;
        }
        // message for level required
        if (!isset($Data['Levels'])) {
            $form->prependGridGroup(new FormGroup(new FormRow(new FormColumn(new Danger('Bitte wählen Sie mindestens eine Klassenstufe aus.', new Exclamation())))));
            $Error = true;
        }

        if (!$Error) {
            if ($Data['GradeType'] < 0) {
                $highlighted = -$Data['GradeType'];
                $tblGradeType = false;
            } else {
                $highlighted = SelectBoxItem::HIGHLIGHTED_ALL;
                $tblGradeType = Grade::useService()->getGradeTypeById($Data['GradeType']);
            }

            (new Data($this->getBinding()))->updateMinimumGradeCount(
                $tblMinimumGradeCount, $Data['Count'], $tblGradeType ?: null, $Data['Period'], $highlighted, $Data['Course']
            );

            /**
             * Klassenstufen (Schulart)
             */
            $createLevelList = array();
            $removeLevelList = array();
            $keepLevelList = array();
            if (($levelList = Grade::useService()->getMinimumGradeCountLevelLinkByMinimumGradeCount($tblMinimumGradeCount))) {
                foreach ($levelList as $levelItem) {
                    if (($tblSchoolType = $levelItem->getServiceTblSchoolType())) {
                        // löschen
                        if (!isset($Data['Levels'][$tblSchoolType->getId()][$levelItem->getLevel()])) {
                            $removeLevelList[] = $levelItem;
                        } else {
                            $keepLevelList[$tblSchoolType->getId()][$levelItem->getLevel()] = 1;
                        }
                    }
                }
            }
            // neu
            if (isset($Data['Levels'])) {
                foreach ($Data['Levels'] as $schoolTypeId => $levelList) {
                    if (($tblSchoolType = Type::useService()->getTypeById($schoolTypeId))) {
                        foreach ($levelList as $level => $value) {
                            if (!isset($keepLevelList[$tblSchoolType->getId()][$level])) {
                                $createLevelList[] = new TblMinimumGradeCountLevelLink($tblMinimumGradeCount, $tblSchoolType, $level);
                            }
                        }
                    }
                }
            }
            if (!empty($createLevelList)) {
                Grade::useService()->createEntityListBulk($createLevelList);
            }
            if (!empty($removeLevelList)) {
                Grade::useService()->deleteEntityListBulk($removeLevelList);
            }

            /**
             * Fächer
             */
            $createSubjectList = array();
            $removeSubjectList = array();
            $keepSubjectList = array();
            if (($subjectList = Grade::useService()->getMinimumGradeCountSubjectLinkByMinimumGradeCount($tblMinimumGradeCount))) {
                foreach ($subjectList as $subjectItem) {
                    if (($tblSubject = $subjectItem->getServiceTblSubject())) {
                        // löschen
                        if (!isset($Data['Subjects'][$tblSubject->getId()])) {
                            $removeSubjectList[] = $subjectItem;
                        } else {
                            $keepSubjectList[$tblSubject->getId()] = 1;
                        }
                    }
                }
            }
            // neu
            if (isset($Data['Subjects'])) {
                foreach ($Data['Subjects'] as $subjectId => $value) {
                    if (($tblSubject = Subject::useService()->getSubjectById($subjectId))
                        && !isset($keepSubjectList[$tblSubject->getId()])
                    ) {
                        $createSubjectList[] = new TblMinimumGradeCountSubjectLink($tblMinimumGradeCount, $tblSubject);
                    }
                }
            }
            if (!empty($createSubjectList)) {
                Grade::useService()->createEntityListBulk($createSubjectList);
            }
            if (!empty($removeSubjectList)) {
                Grade::useService()->deleteEntityListBulk($removeSubjectList);
            }

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Mindestnotenanzahl ist gespeichert worden.')
                . new Redirect('/Education/Graduation/Grade/MinimumGradeCount', Redirect::TIMEOUT_SUCCESS);
        }

        return $form;
    }

    /**
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     *
     * @return bool
     */
    public function removeMinimumGradeCount(TblMinimumGradeCount $tblMinimumGradeCount): bool
    {
        if (($levelList = Grade::useService()->getMinimumGradeCountLevelLinkByMinimumGradeCount($tblMinimumGradeCount))) {
            $removeLevelList = array();
            foreach ($levelList as $levelItem) {
                $removeLevelList[] = $levelItem;
            }
            Grade::useService()->deleteEntityListBulk($removeLevelList);
        }
        if (($subjectList = Grade::useService()->getMinimumGradeCountSubjectLinkByMinimumGradeCount($tblMinimumGradeCount))) {
            $removeSubjectList = array();
            foreach ($subjectList as $subjectItem) {
                $removeSubjectList[] = $subjectItem;
            }
            Grade::useService()->deleteEntityListBulk($removeSubjectList);
        }

        return Grade::useService()->deleteEntityListBulk(array($tblMinimumGradeCount));
    }

    /**
     * @param TblType $tblSchoolType
     * @param int $Level
     * @param TblSubject $tblSubject
     *
     * @return TblMinimumGradeCount[]|false
     */
    public function getMinimumGradeCountListBySchoolTypeAndLevelAndSubject(TblType $tblSchoolType, int $Level, TblSubject $tblSubject)
    {
        return (new Data($this->getBinding()))->getMinimumGradeCountListBySchoolTypeAndLevelAndSubject($tblSchoolType, $Level, $tblSubject);
    }

    /**
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     *
     * @return int
     */
    public function getMinimumGradeCountNumberByPersonAndYearAndSubject(TblMinimumGradeCount $tblMinimumGradeCount, TblPerson $tblPerson,
        TblYear $tblYear, TblSubject $tblSubject
    ): int {
        $count = 0;
        $tblGradeType = $tblMinimumGradeCount->getTblGradeType();
        $tblPeriod = false;
        if ($tblMinimumGradeCount->getPeriod() != SelectBoxItem::PERIOD_FULL_YEAR) {
            $index = $tblMinimumGradeCount->getPeriod() - 1;
            if (($tblPeriodList = Term::useService()->getPeriodListByPersonAndYear($tblPerson, $tblYear))
                && isset($tblPeriodList[$index])
            ) {
                $tblPeriod = $tblPeriodList[$index];
            }
        }

        if (($tblGradeList = Grade::useService()->getTestGradeListByPersonAndYearAndSubject(
            $tblPerson, $tblYear, $tblSubject
        ))) {
            foreach ($tblGradeList as $tblGrade) {
                if (($tblGrade->getGrade() !== null)
                    && ($tblGradeTypeItem = $tblGrade->getTblGradeType())
                ) {
                    if ($tblPeriod
                        && ($date = $tblGrade->getSortDate())
                        && ($date < $tblPeriod->getFromDateTime() || $date > $tblPeriod->getToDateTime())
                    ) {
                        continue;
                    }
                    if ($tblGradeType) {
                        if ($tblGradeType->getId() == $tblGradeTypeItem->getId()) {
                            $count++;
                        }
                    } elseif ($tblMinimumGradeCount->getHighlighted() == SelectBoxItem::HIGHLIGHTED_IS_HIGHLIGHTED) {
                        if ($tblGradeTypeItem->getIsHighlighted()) {
                            $count++;
                        }
                    } elseif ($tblMinimumGradeCount->getHighlighted() == SelectBoxItem::HIGHLIGHTED_IS_NOT_HIGHLIGHTED) {
                        if (!$tblGradeTypeItem->getIsHighlighted()) {
                            $count++;
                        }
                    } else {
                        // Alle Zensuren-Typen
                        $count++;
                    }
                }
            }
        }

        return $count;
    }
}