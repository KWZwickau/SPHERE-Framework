<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Education\Graduation\Grade\Service\Data;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCount;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCountLevelLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCountSubjectLink;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Extern;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Extension\Repository\Sorter\StringNaturalOrderSorter;

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

    /**
     * @param $Data
     *
     * @return string
     */
    public function loadMinimumGradeCountReporting($Data = null): string
    {
        ini_set('memory_limit', '2G');

        $IsDivisionTeacher = Grade::useService()->getRole() === 'Teacher';
        if ($Data === null) {
            return '';
        }

        if (!($tblYear = Grade::useService()->getYear())) {
            return new Warning('Bitte wählen Sie ein Schuljahr aus!', new Exclamation());
        }

        $tblType = Type::useService()->getTypeById($Data['Type']);

        if (!$IsDivisionTeacher && !$tblType) {
            return new Warning('Bitte wählen Sie eine Schulart aus!', new Exclamation());
        }

        $warning = '';
        $tblDivisionCourseList = $this->getDivisionCourseListForMinimumGradeCountReporting(
            $tblYear,
            $tblType ?: null,
            $IsDivisionTeacher,
            trim($Data['DivisionName']),
            $warning
        );
        if ($warning) {
            return $warning;
        }

        if ($tblDivisionCourseList) {
            $schoolTypeLevelList = array();
            $panelList = array();
            $tblDivisionCourseList = $this->getSorter($tblDivisionCourseList)->sortObjectBy('DisplayName', new StringNaturalOrderSorter());
            /** @var TblDivisionCourse $tblDivisionCourse */
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                $isSekII = DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse);
                $contentPanel = array();
                $isDivisionFulfilled = true;
                $countArray = array();
                if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByDivisionCourse($tblDivisionCourse))
                    && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
                ) {
                    foreach ($tblPersonList as $tblPerson) {
                        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
                            && $Level = $tblStudentEducation->getLevel()
                        ) {
                            foreach ($tblSubjectList as $tblSubject) {
                                if (($tblVirtualSubject = DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject(
                                        $tblPerson, $tblYear, $tblSubject
                                    ))
                                    && $tblVirtualSubject->getHasGrading()
                                ) {
                                    if (!isset($schoolTypeLevelList[$tblSchoolType->getId()][$Level][$tblSubject->getId()])) {
                                        $tblMinimumGradeTypeList = Grade::useService()->getMinimumGradeCountListBySchoolTypeAndLevelAndSubject(
                                            $tblSchoolType, $Level, $tblSubject
                                        );
                                        if ($tblMinimumGradeTypeList) {
                                            foreach ($tblMinimumGradeTypeList as $tblMinimumGradeCount) {
                                                $schoolTypeLevelList[$tblSchoolType->getId()][$Level][$tblSubject->getId()][$tblMinimumGradeCount->getId()]
                                                    = $tblMinimumGradeCount;
                                            }
                                        }
                                    }

                                    if (isset($schoolTypeLevelList[$tblSchoolType->getId()][$Level][$tblSubject->getId()])) {
                                        /** @var TblMinimumGradeCount $tblMinimumGradeCount */
                                        foreach ($schoolTypeLevelList[$tblSchoolType->getId()][$Level][$tblSubject->getId()] as $tblMinimumGradeCount) {
                                            // nach Zeiträumen filtern, beim "Gesamtes Schuljahr" werden alle angezeigt
                                            if ($Data['Period'] != SelectBoxItem::PERIOD_FULL_YEAR
                                                && $Data['Period'] != $tblMinimumGradeCount->getPeriod()
                                            ) {
                                                continue;
                                            }

                                            $countMinimumGradeByPerson = Grade::useService()->getMinimumGradeCountNumberByPersonAndYearAndSubject(
                                                $tblMinimumGradeCount, $tblPerson, $tblYear, $tblSubject
                                            );

                                            // Mindestnotenanzahl erfüllt
                                            if ($countMinimumGradeByPerson >= $tblMinimumGradeCount->getCount()) {
                                                if (!isset($countArray[$tblSubject->getId()][$tblMinimumGradeCount->getId()]['FullFilled'])) {
                                                    $countArray[$tblSubject->getId()][$tblMinimumGradeCount->getId()]['FullFilled'] = 0;
                                                }
                                                $countArray[$tblSubject->getId()][$tblMinimumGradeCount->getId()]['FullFilled']++;
                                            }

                                            // Mindestnotenanzahl Schüler Anzahl Gesamt
                                            if (!isset($countArray[$tblSubject->getId()][$tblMinimumGradeCount->getId()]['StudentCount'])) {
                                                $countArray[$tblSubject->getId()][$tblMinimumGradeCount->getId()]['StudentCount'] = 0;
                                            }
                                            $countArray[$tblSubject->getId()][$tblMinimumGradeCount->getId()]['StudentCount']++;

                                            // Mindestnotenanzahl Schüler Minimum
                                            if (!isset($countArray[$tblSubject->getId()][$tblMinimumGradeCount->getId()]['Minimum'])) {
                                                $countArray[$tblSubject->getId()][$tblMinimumGradeCount->getId()]['Minimum'] = $countMinimumGradeByPerson;
                                            } elseif($countMinimumGradeByPerson < $countArray[$tblSubject->getId()][$tblMinimumGradeCount->getId()]['Minimum']) {
                                                $countArray[$tblSubject->getId()][$tblMinimumGradeCount->getId()]['Minimum'] = $countMinimumGradeByPerson;
                                            }

                                            // Mindestnotenanzahl Schüler Maximum
                                            if (!isset($countArray[$tblSubject->getId()][$tblMinimumGradeCount->getId()]['Maximum'])) {
                                                $countArray[$tblSubject->getId()][$tblMinimumGradeCount->getId()]['Maximum'] = $countMinimumGradeByPerson;
                                            } elseif($countMinimumGradeByPerson > $countArray[$tblSubject->getId()][$tblMinimumGradeCount->getId()]['Maximum']) {
                                                $countArray[$tblSubject->getId()][$tblMinimumGradeCount->getId()]['Maximum'] = $countMinimumGradeByPerson;
                                            }

                                            // Summe der Mindestnotenanzahl für Durchschnitt
                                            if (!isset($countArray[$tblSubject->getId()][$tblMinimumGradeCount->getId()]['Sum'])) {
                                                $countArray[$tblSubject->getId()][$tblMinimumGradeCount->getId()]['Sum'] = 0;
                                            }
                                            $countArray[$tblSubject->getId()][$tblMinimumGradeCount->getId()]['Sum'] += $countMinimumGradeByPerson;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                foreach ($countArray as $subjectId => $minimumGradeCountList) {
                    if (($tblSubject = Subject::useService()->getSubjectById($subjectId))) {
                        foreach ($minimumGradeCountList as $minimumGradeCountId => $valueList) {
                            if (($tblMinimumGradeCount = Grade::useService()->getMinimumGradeCountById($minimumGradeCountId))) {
                                $countFulfilled = $valueList['FullFilled'] ?? 0;
                                $countPersons = $valueList['StudentCount'] ?? 0;
                                $status = $countFulfilled . ' von ' . $countPersons . ' Schüler';
                                if ($countFulfilled >= $countPersons) {
                                    $status = new \SPHERE\Common\Frontend\Text\Repository\Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' ' . $status);
                                } else {
                                    $isDivisionFulfilled = false;
                                    $status = new \SPHERE\Common\Frontend\Text\Repository\Warning(new Disable() . ' ' . $status);
                                }

                                $external = new External(
                                    '',
                                    '/Education/Graduation/Grade/GradeBook',
                                    new Extern(),
                                    array(
                                        'DivisionCourseId' => $tblDivisionCourse->getId(),
                                        'SubjectId' => $tblSubject->getId(),
                                        'IsDirectJump' => true
                                    ),
                                    'Zum Notenbuch wechseln'
                                );

                                $contentPanel[] = new Layout(new LayoutGroup(new LayoutRow(array(
                                    new LayoutColumn($tblSubject->getDisplayName(), 2),
                                    new LayoutColumn($tblMinimumGradeCount->getGradeTypeDisplayName(), 3),
                                    new LayoutColumn($tblMinimumGradeCount->getPeriodDisplayName(), 2),
                                    new LayoutColumn($tblMinimumGradeCount->getCourseDisplayName(), 1),
                                    new LayoutColumn($tblMinimumGradeCount->getCount()
                                        . ' (Min: ' . ($valueList['Minimum'] ?? 0)
                                        . ($countPersons > 0 ? ', &#216;: ' . round(floatval($valueList['Sum'] ?? 0) / $countPersons, 1) : '')
                                        . ', Max: ' . ($valueList['Maximum'] ?? 0) . ')', 2),
                                    new LayoutColumn($status . new PullRight($external), 2)
                                ))));
                            }
                        }
                    }
                }

                $schoolTypeNames = $tblDivisionCourse->getSchoolTypeListFromStudents(true);
                $panelList[] = new Panel(
                    new Layout(new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn($tblDivisionCourse->getDisplayName() . ($schoolTypeNames ? new Small(' (' . $schoolTypeNames . ')') : ''), 2),
                        new LayoutColumn(new Small('Zensuren-Typ:'), 3),
                        new LayoutColumn(new Small('Zeitraum:'), 2),
                        new LayoutColumn(new Small($isSekII ? 'Kurs:' : '&nbsp;'), 1),
                        new LayoutColumn(new Small('Anzahl:'), 2),
                        new LayoutColumn(new Small('Status:'), 2)
                    )))),
                    empty($contentPanel) ? new Ban() .' Keine Mindestnoten vorhanden' : $contentPanel,
                    $isDivisionFulfilled ? Panel::PANEL_TYPE_SUCCESS : Panel::PANEL_TYPE_WARNING
                );
            }

            if (!empty($panelList)) {
                return new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn($panelList))));
            }
        }

        return new Warning('Keine Mindestnoten gefunden', new Exclamation());
    }

    /**
     * @param TblYear $tblYear
     * @param TblType|null $tblType
     * @param bool $IsDivisionTeacher
     * @param string $divisionName
     * @param string $warning
     *
     * @return array
     */
    public function getDivisionCourseListForMinimumGradeCountReporting(
        TblYear $tblYear,
        ?TblType $tblType,
        bool $IsDivisionTeacher,
        string $divisionName,
        string &$warning
    ): array
    {
        $tblDivisionCourseList = array();
        if ($divisionName != '') {
            $tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByLikeName($divisionName, array($tblYear), true);
            if (empty($tblDivisionCourseList)) {
                $warning = new Warning('Klasse/Stammgruppe nicht gefunden', new Exclamation());

                return array();
            }
        } else {
            if (($tblDivisionCourseListDivision = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_DIVISION))) {
                $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListDivision);
            }
            if (($tblDivisionCourseListCoreGroup = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_CORE_GROUP))) {
                $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListCoreGroup);
            }

            if ($tblType && $tblDivisionCourseList) {
                $tblDivisionCourseListForType = array();
                foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                    if (($tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents())
                        && isset($tblSchoolTypeList[$tblType->getId()])
                    ) {
                        $tblDivisionCourseListForType[$tblDivisionCourse->getId()] = $tblDivisionCourse;
                    }
                }
                $tblDivisionCourseList = $tblDivisionCourseListForType;
            }
        }

        // Klassenlehrer können nur ihre eigenen Klassen sehen
        if ($IsDivisionTeacher && $tblDivisionCourseList) {
            $tblMemberType = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER);
            $tempDivisionList = array();
            if (($tblPersonAccount = Account::useService()->getPersonByLogin())) {
                foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                    if (DivisionCourse::useService()->getDivisionCourseMemberByPerson($tblDivisionCourse, $tblMemberType, $tblPersonAccount)) {
                        $tempDivisionList[] = $tblDivisionCourse;
                    }
                }
            }

            $tblDivisionCourseList = empty($tempDivisionList) ? false : $tempDivisionList;
        }

        return  $tblDivisionCourseList;
    }
}