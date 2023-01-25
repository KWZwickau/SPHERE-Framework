<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Education\Graduation\Grade\Service\Data;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreConditionGradeTypeList;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreConditionGroupList;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreConditionGroupRequirement;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreGroup;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreGroupGradeTypeList;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRule;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRuleConditionList;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRuleSubject;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRuleSubjectDivisionCourse;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreTypeSubject;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Window\Redirect;

abstract class ServiceScore extends ServiceMinimumGradeCount
{
    const PREG_MATCH_DECIMAL_NUMBER = '!^[0-9]+((\.|,)[0-9]+)?$!is';

    /**
     * @param $id
     *
     * @return false|TblScoreType
     */
    public function getScoreTypeById($id)
    {
        return (new Data($this->getBinding()))->getScoreTypeById($id);
    }

    /**
     * @param $identifier
     *
     * @return false|TblScoreType
     */
    public function getScoreTypeByIdentifier($identifier)
    {
        return (new Data($this->getBinding()))->getScoreTypeByIdentifier($identifier);
    }

    /**
     * @return false|TblScoreType[]
     */
    public function getScoreTypeAll()
    {
        return (new Data($this->getBinding()))->getScoreTypeAll();
    }

    /**
     * @param TblScoreType $tblScoreType
     * @param TblType|null $tblSchoolType
     *
     * @return false|TblScoreTypeSubject[]
     */
    public function getScoreTypeSubjectListByScoreType(TblScoreType $tblScoreType, ?TblType $tblSchoolType = null)
    {
        return (new Data($this->getBinding()))->getScoreTypeSubjectListByScoreType($tblScoreType, $tblSchoolType);
    }

    /**
     * @param TblType $tblSchoolType
     *
     * @return false|TblScoreTypeSubject[]
     */
    public function getScoreTypeSubjectListBySchoolType(TblType $tblSchoolType)
    {
        return (new Data($this->getBinding()))->getScoreTypeSubjectListBySchoolType($tblSchoolType);
    }

    /**
     * @param TblType $tblSchoolType
     * @param int $level
     * @param TblSubject $tblSubject
     *
     * @return false|TblScoreTypeSubject
     */
    public function getScoreTypeSubjectBySchoolTypeAndLevelAndSubject(TblType $tblSchoolType, int $level, TblSubject $tblSubject)
    {
        return (new Data($this->getBinding()))->getScoreTypeSubjectBySchoolTypeAndLevelAndSubject($tblSchoolType, $level, $tblSubject);
    }

    /**
     * @param TblType $tblSchoolType
     * @param int $level
     * @param TblSubject $tblSubject
     *
     * @return false|TblScoreType
     */
    public function getScoreTypeBySchoolTypeAndLevelAndSubject(TblType $tblSchoolType, int $level, TblSubject $tblSubject)
    {
        if (($temp = (new Data($this->getBinding()))->getScoreTypeSubjectBySchoolTypeAndLevelAndSubject($tblSchoolType, $level, $tblSubject))) {
            return $temp->getTblScoreType();
        }

        return false;
    }

    /**
     * @param TblScoreType $tblScoreType
     *
     * @return array
     */
    public function getGradeSelectListByScoreType(TblScoreType $tblScoreType): array
    {
        $selectList[-1] = '';
        switch ($tblScoreType->getIdentifier()) {
            case 'POINTS':
                for ($i = 0; $i < 16; $i++) {
                    $selectList[$i] = (string)$i;
                }
                break;
            case 'GRADES_BEHAVIOR_TASK':
                for ($i = 1; $i < 5; $i++) {
                    $selectList[$i . '+'] = ($i . '+');
                    $selectList[$i] = (string)($i);
                    $selectList[$i . '-'] = ($i . '-');
                }
                $selectList[5] = 5;
                break;
            case 'GRADES':
            default:
                for ($i = 1; $i < 6; $i++) {
                    $selectList[$i . '+'] = ($i . '+');
                    $selectList[$i] = (string)($i);
                    $selectList[$i . '-'] = ($i . '-');
                }
                $selectList[6] = 6;
        }

        return $selectList;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     *
     * @return false|TblScoreType
     */
    public function getScoreTypeByPersonAndYearAndSubject(TblPerson $tblPerson, TblYear $tblYear, TblSubject $tblSubject)
    {
        $tblScoreType = false;
        if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())
        ) {
            if (!($tblScoreType = $this->getScoreTypeBySchoolTypeAndLevelAndSubject($tblSchoolType, $tblStudentEducation->getLevel(), $tblSubject))) {
                // Fallback, falls kein Bewertungssystem eingestellt ist
                if (DivisionCourse::useService()->getIsCourseSystemBySchoolTypeAndLevel($tblSchoolType, $tblStudentEducation->getLevel())) {
                    $tblScoreType = Grade::useService()->getScoreTypeByIdentifier('POINTS');
                } else {
                    $tblScoreType = Grade::useService()->getScoreTypeByIdentifier('GRADES');
                }
            }
        }

        return $tblScoreType ?: Grade::useService()->getScoreTypeByIdentifier('GRADES');
    }

    /**
     * @param $id
     *
     * @return false|TblScoreRule
     */
    public function getScoreRuleById($id)
    {
        return (new Data($this->getBinding()))->getScoreRuleById($id);
    }

    /**
     * @param bool $withInActive
     *
     * @return false|TblScoreRule[]
     */
    public function getScoreRuleAll(bool $withInActive = false)
    {
        return (new Data($this->getBinding()))->getScoreRuleAll($withInActive);
    }

    /**
     * @param TblScoreRule $tblScoreRule
     *
     * @return TblGradeType[]|false
     */
    public function getGradeTypeListByScoreRule(TblScoreRule $tblScoreRule)
    {
        $resultList = array();
        if (($tblScoreConditionAllByRule = Grade::useService()->getScoreConditionsByScoreRule($tblScoreRule))) {
            foreach ($tblScoreConditionAllByRule as $tblScoreCondition){
                if (($tblGroupListByCondition = Grade::useService()->getScoreConditionGroupListByCondition($tblScoreCondition))){
                    foreach ($tblGroupListByCondition as $group){
                        if (($tblScoreGroupGradeTypeListByGroup = Grade::useService()->getScoreGroupGradeTypeListByGroup($group->getTblScoreGroup()))){
                            foreach ($tblScoreGroupGradeTypeListByGroup as $tblScoreGroupGradeType){
                                if ($tblScoreGroupGradeType->getTblGradeType() && $tblScoreGroupGradeType->getTblGradeType()->getIsActive()) {
                                    $resultList[$tblScoreGroupGradeType->getTblGradeType()->getId()] = $tblScoreGroupGradeType->getTblGradeType();
                                }
                            }
                        }
                    }
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblYear $tblYear
     * @param TblType $tblSchoolType
     *
     * @return false|TblScoreRuleSubject[]
     */
    public function getScoreRuleSubjectListByYearAndSchoolType(TblYear $tblYear, TblType $tblSchoolType)
    {
        return (new Data($this->getBinding()))->getScoreRuleSubjectListByYearAndSchoolType($tblYear, $tblSchoolType);
    }

    /**
     * @param TblScoreRule $tblScoreRule
     * @param TblYear $tblYear
     * @param TblType $tblSchoolType
     *
     * @return false|TblScoreRuleSubject[]
     */
    public function getScoreRuleSubjectListByScoreRuleAndYearAndSchoolType(TblScoreRule $tblScoreRule, TblYear $tblYear, TblType $tblSchoolType)
    {
        return (new Data($this->getBinding()))->getScoreRuleSubjectListByScoreRuleAndYearAndSchoolType($tblScoreRule, $tblYear, $tblSchoolType);
    }

    /**
     * @param TblYear $tblYear
     * @param TblType $tblSchoolType
     * @param int $level
     * @param TblSubject $tblSubject
     *
     * @return false|TblScoreRuleSubject
     */
    public function getScoreRuleSubjectByYearAndSchoolTypeAndLevelAndSubject(TblYear $tblYear, TblType $tblSchoolType, int $level, TblSubject $tblSubject)
    {
        return (new Data($this->getBinding()))->getScoreRuleSubjectByYearAndSchoolTypeAndLevelAndSubject($tblYear, $tblSchoolType, $level, $tblSubject);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblScoreRuleSubjectDivisionCourse[]
     */
    public function getScoreRuleSubjectDivisionCourseListByYear(TblYear $tblYear)
    {
        return (new Data($this->getBinding()))->getScoreRuleSubjectDivisionCourseListByYear($tblYear);
    }

    /**
     * @param TblScoreRule $tblScoreRule
     * @param TblYear $tblYear
     * @param TblType $tblSchoolType
     *
     * @return false|TblScoreRuleSubjectDivisionCourse[]
     */
    public function getScoreRuleSubjectDivisionCourseListByScoreRuleAndYearAndSchoolType(TblScoreRule $tblScoreRule, TblYear $tblYear, TblType $tblSchoolType)
    {
        $resultList = array();
        if (($list = (new Data($this->getBinding()))->getScoreRuleSubjectDivisionCourseListByScoreRuleAndYear($tblScoreRule, $tblYear))) {
            foreach ($list as $item) {
                if (($tblDivisionCourse = $item->getServiceTblDivisionCourse())
                    && ($tblSchoolTypeList = $tblDivisionCourse->getSchoolTypeListFromStudents())
                    && isset($tblSchoolTypeList[$tblSchoolType->getId()])
                ) {
                    $resultList[] = $item;
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     *
     * @return false|TblScoreRuleSubjectDivisionCourse
     */
    public function getScoreRuleSubjectDivisionCourseByDivisionCourseAndSubject(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject)
    {
        return (new Data($this->getBinding()))->getScoreRuleSubjectDivisionCourseByDivisionCourseAndSubject($tblDivisionCourse, $tblSubject);
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreRuleConditionList
     */
    public function getScoreRuleConditionListById($Id)
    {
        return (new Data($this->getBinding()))->getScoreRuleConditionListById($Id);
    }

    /**
     * @param TblScoreRule $tblScoreRule
     *
     * @return bool|TblScoreRuleConditionList[]
     */
    public function getScoreRuleConditionListByScoreRule(TblScoreRule $tblScoreRule)
    {
        return (new Data($this->getBinding()))->getScoreRuleConditionListByScoreRule($tblScoreRule);
    }

    /**
     * @param TblScoreRule $tblScoreRule
     * @return TblScoreCondition[]|false
     */
    public function getScoreConditionsByScoreRule(TblScoreRule $tblScoreRule)
    {
        $tblScoreConditionList = array();
        if (($list = $this->getScoreRuleConditionListByScoreRule($tblScoreRule))) {
            foreach ($list as $item) {
                if (($tblScoreCondition = $item->getTblScoreCondition())) {
                    $tblScoreConditionList[] = $tblScoreCondition;
                }
            }
        }

        return empty($tblScoreConditionList) ? false : $tblScoreConditionList;
    }

    /**
     * @param TblScoreRule $tblScoreRule
     *
     * @return bool
     */
    public function getIsScoreRuleUsed(TblScoreRule $tblScoreRule): bool
    {
        return (new Data($this->getBinding()))->getIsScoreRuleUsed($tblScoreRule);
    }

    /**
     * @param IFormInterface|null $form
     * @param                     $ScoreRule
     *
     * @return IFormInterface|string
     */
    public function createScoreRule(?IFormInterface $form, $ScoreRule = null)
    {
        /**
         * Skip to Frontend
         */
        if (null === $ScoreRule) {
            return $form;
        }

        $Error = false;
        if (isset($ScoreRule['Name']) && empty($ScoreRule['Name'])) {
            $form->setError('ScoreRule[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createScoreRule(
                $ScoreRule['Name'],
                $ScoreRule['Description'],
                $ScoreRule['DescriptionForExtern']
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Berechnungsvorschrift ist erfasst worden')
                . new Redirect('/Education/Graduation/Grade/ScoreRule', Redirect::TIMEOUT_SUCCESS);
        }

        return $form;
    }

    /**
     * @param IFormInterface|null $form
     * @param $Id
     * @param $ScoreRule
     * @return IFormInterface|string
     */
    public function updateScoreRule(?IFormInterface $form, $Id, $ScoreRule)
    {
        /**
         * Skip to Frontend
         */
        if (null === $ScoreRule || null === $Id) {
            return $form;
        }

        $Error = false;
        if (isset($ScoreRule['Name']) && empty($ScoreRule['Name'])) {
            $form->setError('ScoreRule[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }

        $tblScoreRule = $this->getScoreRuleById($Id);
        if (!$tblScoreRule) {
            return new Danger(new Ban() . ' Berechnungsvorschrift nicht gefunden')
                . new Redirect('/Education/Graduation/Grade/ScoreRule', Redirect::TIMEOUT_ERROR);
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateScoreRule(
                $tblScoreRule,
                $ScoreRule['Name'],
                $ScoreRule['Description'],
                $ScoreRule['DescriptionForExtern'],
                $tblScoreRule->getIsActive()
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Berechnungsvorschrift ist erfolgreich gespeichert worden')
                . new Redirect('/Education/Graduation/Grade/ScoreRule', Redirect::TIMEOUT_SUCCESS);
        }

        return $form;
    }

    /**
     * @param TblScoreRule $tblScoreRule
     *
     * @return bool
     */
    public function destroyScoreRule(TblScoreRule $tblScoreRule): bool
    {
        return (new Data($this->getBinding()))->destroyScoreRule($tblScoreRule);
    }

    /**
     * @param TblScoreRule $tblScoreRule
     * @param bool $IsActive
     *
     * @return string
     */
    public function setScoreRuleActive(TblScoreRule $tblScoreRule, bool $IsActive = true): string
    {
        return (new Data($this->getBinding()))->updateScoreRule($tblScoreRule, $tblScoreRule->getName(),
            $tblScoreRule->getDescription(), $tblScoreRule->getDescriptionForExtern(), $IsActive);
    }

    /**
     * @param TblScoreRule $tblScoreRule
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return string
     */
    public function addScoreRuleConditionList(TblScoreRule $tblScoreRule, TblScoreCondition $tblScoreCondition): string
    {
        if ((new Data($this->getBinding()))->addScoreRuleConditionList($tblScoreRule, $tblScoreCondition)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich hinzugefügt.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Condition/Select', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblScoreRule->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht hinzugefügt werden.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Condition/Select', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblScoreRule->getId()));
        }
    }

    /**
     * @param TblScoreRuleConditionList $tblScoreRuleConditionList
     * @return string
     */
    public function removeScoreRuleConditionList(TblScoreRuleConditionList $tblScoreRuleConditionList): string
    {
        $tblScoreRule = $tblScoreRuleConditionList->getTblScoreRule();
        if ((new Data($this->getBinding()))->removeScoreRuleConditionList($tblScoreRuleConditionList)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich entfernt.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Condition/Select', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblScoreRule->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht entfernt werden.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Condition/Select', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblScoreRule->getId()));
        }
    }

    /**
     * @param TblScoreRule $tblScoreRule
     * @param array $structure
     *
     * @return array
     */
    public function getScoreRuleStructure(TblScoreRule $tblScoreRule, array $structure): array
    {
        $tblScoreConditions = $this->getScoreConditionsByScoreRule($tblScoreRule);
        if ($tblScoreConditions) {
            $tblScoreConditions = $this->getSorter($tblScoreConditions)->sortObjectBy('Priority');

            $count = 1;
            /** @var TblScoreCondition $tblScoreCondition */
            foreach ($tblScoreConditions as $tblScoreCondition) {
                $structure[] = $count++ . '. Berechnungsvariante: ' . $tblScoreCondition->getName()
                    . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . 'Priorität: '
                    . $tblScoreCondition->getPriority();

                if (($requirements = Grade::useService()->getRequirementsForScoreCondition($tblScoreCondition, true))) {
                    $structure[] = '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;' . 'Bedingungen: ' . $requirements;
                }

                $tblScoreConditionGroupListByCondition = Grade::useService()->getScoreConditionGroupListByCondition(
                    $tblScoreCondition
                );
                if ($tblScoreConditionGroupListByCondition) {
                    foreach ($tblScoreConditionGroupListByCondition as $tblScoreConditionGroupList) {
                        $structure[] = '&nbsp;&nbsp;&nbsp;&bull;&nbsp;&nbsp;' . 'Zensuren-Gruppe: '
                            . $tblScoreConditionGroupList->getTblScoreGroup()->getName()
                            . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . 'Faktor: '
                            . $tblScoreConditionGroupList->getTblScoreGroup()->getDisplayMultiplier()
                            . ($tblScoreConditionGroupList->getTblScoreGroup()->getIsEveryGradeASingleGroup()
                                ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . 'Noten einzeln ' : '');

                        $tblGradeTypeList = Grade::useService()->getScoreGroupGradeTypeListByGroup(
                            $tblScoreConditionGroupList->getTblScoreGroup()
                        );
                        if ($tblGradeTypeList) {
                            foreach ($tblGradeTypeList as $tblGradeType) {
                                $structure[] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#9702;&nbsp;&nbsp;'
                                    . 'Zensuren-Typ: '
                                    . ($tblGradeType->getTblGradeType() ? $tblGradeType->getTblGradeType()->getDisplayName() : '')
                                    . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . 'Faktor: '
                                    . $tblGradeType->getDisplayMultiplier();
                            }
                        } else {
                            $structure[] = new Warning('Kein Zenuren-Typ hinterlegt.', new Ban());
                        }
                    }
                } else {
                    $structure[] = new Warning('Keine Zenuren-Gruppe hinterlegt.', new Ban());
                }
                $structure[] = ' ';
            }
        } else {
            $structure[] = new Warning('Keine Berechnungsvariante hinterlegt.', new Ban());
        }
        return $structure;
    }

    /**
     * @param $id
     *
     * @return false|TblScoreCondition
     */
    public function getScoreConditionById($id)
    {
        return (new Data($this->getBinding()))->getScoreConditionById($id);
    }

    /**
     * @param bool $withInActive
     *
     * @return false|TblScoreCondition[]
     */
    public function getScoreConditionAll(bool $withInActive = false)
    {
        return (new Data($this->getBinding()))->getScoreConditionAll($withInActive);
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreConditionGradeTypeList
     */
    public function getScoreConditionGradeTypeListById($Id)
    {
        return (new Data($this->getBinding()))->getScoreConditionGradeTypeListById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreConditionGroupList
     */
    public function getScoreConditionGroupListById($Id)
    {
        return (new Data($this->getBinding()))->getScoreConditionGroupListById($Id);
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return bool|TblScoreConditionGroupList[]
     */
    public function getScoreConditionGroupListByCondition(TblScoreCondition $tblScoreCondition)
    {
        return (new Data($this->getBinding()))->getScoreConditionGroupListByCondition($tblScoreCondition);
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return bool|TblScoreConditionGradeTypeList[]
     */
    public function getScoreConditionGradeTypeListByCondition(TblScoreCondition $tblScoreCondition)
    {
        return (new Data($this->getBinding()))->getScoreConditionGradeTypeListByCondition($tblScoreCondition);
    }

    /**
     * @param $Id
     *
     * @return false|TblScoreConditionGroupRequirement
     */
    public function getScoreConditionGroupRequirementById($Id)
    {
        return (new Data($this->getBinding()))->getScoreConditionGroupRequirementById($Id);
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return bool|TblScoreConditionGroupRequirement[]
     */
    public function getScoreConditionGroupRequirementAllByCondition(TblScoreCondition $tblScoreCondition)
    {
        return (new Data($this->getBinding()))->getScoreConditionGroupRequirementAllByCondition($tblScoreCondition);
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     * @param bool $isDisplay
     *
     * @return array|bool
     */
    public function getRequirementsForScoreCondition(TblScoreCondition $tblScoreCondition, bool $isDisplay = false)
    {
        $requirements = array();
        $displayList = array();
        // period
        if ($tblScoreCondition->getPeriod()) {
            $requirements['Period'] = $tblScoreCondition->getPeriod();
            $displayList[] = $tblScoreCondition->getPeriodDisplayName();
        }

        // gradeTypes
        if (($tblScoreConditionGradeTypeList = $this->getScoreConditionGradeTypeListByCondition($tblScoreCondition))) {
            $temp = array();
            foreach ($tblScoreConditionGradeTypeList as $tblScoreConditionGradeType) {
                if (($tblGradeType = $tblScoreConditionGradeType->getTblGradeType())) {
                    $temp[] = $tblScoreConditionGradeType;
                    $displayList[] = $tblGradeType->getDisplayName() . ' ' . new Muted('(Anzahl: '
                            . $tblScoreConditionGradeType->getCount() . ')');
                }
            }
            $requirements['GradeTypes'] = $temp;
        }
        // groups
        if (($tblScoreConditionGroupRequirementList = $this->getScoreConditionGroupRequirementAllByCondition($tblScoreCondition))) {
            $temp = array();
            foreach ($tblScoreConditionGroupRequirementList as $tblScoreConditionGroupRequirement) {
                if (($tblScoreGroup = $tblScoreConditionGroupRequirement->getTblScoreGroup())) {
                    $temp[] = $tblScoreConditionGroupRequirement;
                    $displayList[] = $tblScoreGroup->getName() . ' '
                        . new Muted('(Anzahl: ' . $tblScoreConditionGroupRequirement->getCount() . ')');
                }
            }
            $requirements['GradeGroups'] = $temp;
        }

        if ($isDisplay) {
            return implode(', ', $displayList);
        } else {
            return empty($requirements) ? false : $requirements;
        }
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return bool
     */
    public function getIsScoreConditionUsed(TblScoreCondition $tblScoreCondition): bool
    {
        if ((new Data($this->getBinding()))->getScoreRuleConditionListByScoreCondition($tblScoreCondition)) {
            return true;
        }

        return false;
    }

    /**
     * @param IFormInterface|null $form
     * @param                     $ScoreCondition
     *
     * @return IFormInterface|string
     */
    public function createScoreCondition(IFormInterface $form = null, $ScoreCondition = null)
    {
        /**
         * Skip to Frontend
         */
        if (null === $ScoreCondition) {
            return $form;
        }

        $Error = false;
        if (isset($ScoreCondition['Name']) && empty($ScoreCondition['Name'])) {
            $form->setError('ScoreCondition[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }

        if ($ScoreCondition['Priority'] == '') {
            $priority = 1;
        } else {
            $priority = $ScoreCondition['Priority'];
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createScoreCondition($ScoreCondition['Name'], $priority);

            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Berechnungsvariante ist erfasst worden')
                . new Redirect('/Education/Graduation/Grade/ScoreRule/Condition', Redirect::TIMEOUT_SUCCESS);
        }

        return $form;
    }

    /**
     * @param IFormInterface|null $form
     * @param $Id
     * @param $ScoreCondition
     * @return IFormInterface|string
     */
    public function updateScoreCondition(IFormInterface $form = null, $Id, $ScoreCondition)
    {
        /**
         * Skip to Frontend
         */
        if (null === $ScoreCondition || null === $Id) {
            return $form;
        }

        $Error = false;
        if (isset($ScoreCondition['Name']) && empty($ScoreCondition['Name'])) {
            $form->setError('ScoreCondition[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }

        $tblScoreCondition = $this->getScoreConditionById($Id);
        if (!$tblScoreCondition) {
            return new Danger(new Ban() . ' Berechnungsvariante nicht gefunden')
                . new Redirect('/Education/Graduation/Grade/ScoreRule/Condition', Redirect::TIMEOUT_ERROR);
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateScoreCondition(
                $tblScoreCondition,
                $ScoreCondition['Name'],
                $ScoreCondition['Priority'],
                $tblScoreCondition->getIsActive(),
                $tblScoreCondition->getPeriod()
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Berechnungsvariante ist erfolgreich gespeichert worden')
                . new Redirect('/Education/Graduation/Grade/ScoreRule/Condition', Redirect::TIMEOUT_SUCCESS);
        }

        return $form;
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     * @param bool $IsActive
     *
     * @return bool
     */
    public function setScoreConditionActive(TblScoreCondition $tblScoreCondition, bool $IsActive = true): bool
    {
        return (new Data($this->getBinding()))->updateScoreCondition($tblScoreCondition, $tblScoreCondition->getName(), $tblScoreCondition->getPriority(),
            $IsActive, $tblScoreCondition->getPeriod());
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     * @param TblScoreGroup $tblScoreGroup
     *
     * @return string
     */
    public function addScoreConditionGroupList(TblScoreCondition $tblScoreCondition, TblScoreGroup $tblScoreGroup): string
    {
        if ((new Data($this->getBinding()))->addScoreConditionGroupList($tblScoreCondition, $tblScoreGroup)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich hinzugefügt.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Condition/Group/Select', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblScoreCondition->getId()));
        } else {
            return new Danger('Konnte nicht hinzugefügt werden.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Condition/Group/Select', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblScoreCondition->getId()));
        }
    }

    /**
     * @param TblScoreConditionGroupList $tblScoreConditionGroupList
     *
     * @return string
     */
    public function removeScoreConditionGroupList(TblScoreConditionGroupList $tblScoreConditionGroupList): string
    {
        $tblScoreCondition = $tblScoreConditionGroupList->getTblScoreCondition();
        if ((new Data($this->getBinding()))->removeScoreConditionGroupList($tblScoreConditionGroupList)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich entfernt.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Condition/Group/Select', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblScoreCondition->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht entfernt werden.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Condition/Group/Select', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblScoreCondition->getId()));
        }
    }

    /**
     * @param IFormInterface|null $form
     * @param TblScoreCondition|null $tblScoreCondition
     * @param $Period
     *
     * @return IFormInterface|string
     */
    public function updateScoreConditionRequirementPeriod(IFormInterface $form = null, TblScoreCondition $tblScoreCondition = null, $Period)
    {
        /**
         * Skip to Frontend
         */
        if (null === $tblScoreCondition || null === $Period) {
            return $form;
        }

        (new Data($this->getBinding()))->updateScoreCondition(
            $tblScoreCondition,
            $tblScoreCondition->getName(),
            $tblScoreCondition->getPriority(),
            $tblScoreCondition->getIsActive(),
            $Period < 0 ? null : $Period
        );

        return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Bedingung ist erfolgreich gespeichert worden')
            . new Redirect('/Education/Graduation/Grade/ScoreRule/Condition/GradeType/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblScoreCondition->getId()));
    }

    /**
     * @param TblGradeType $tblGradeType
     * @param TblScoreCondition $tblScoreCondition
     * @param $count
     *
     * @return string
     */
    public function addScoreConditionGradeTypeList(TblGradeType $tblGradeType, TblScoreCondition $tblScoreCondition, $count): string
    {
        if ((new Data($this->getBinding()))->addScoreConditionGradeTypeList($tblGradeType, $tblScoreCondition, $count)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich hinzugefügt.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Condition/GradeType/Select', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblScoreCondition->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht hinzugefügt werden.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Condition/GradeType/Select', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblScoreCondition->getId()));
        }
    }

    /**
     * @param TblScoreGroup $tblScoreGroup
     * @param TblScoreCondition $tblScoreCondition
     * @param $count
     *
     * @return string
     */
    public function addScoreConditionGroupRequirement(TblScoreGroup $tblScoreGroup, TblScoreCondition $tblScoreCondition, $count): string
    {
        if ((new Data($this->getBinding()))->addScoreConditionGroupRequirement($tblScoreGroup, $tblScoreCondition, $count)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich hinzugefügt.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Condition/GradeType/Select', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblScoreCondition->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht hinzugefügt werden.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Condition/GradeType/Select', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblScoreCondition->getId()));
        }
    }

    /**
     * @param TblScoreConditionGradeTypeList $tblScoreConditionGradeTypeList
     *
     * @return string
     */
    public function removeScoreConditionGradeTypeList(TblScoreConditionGradeTypeList $tblScoreConditionGradeTypeList): string
    {
        $tblScoreCondition = $tblScoreConditionGradeTypeList->getTblScoreCondition();
        if ((new Data($this->getBinding()))->removeScoreConditionGradeTypeList($tblScoreConditionGradeTypeList)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich entfernt.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Condition/GradeType/Select', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblScoreCondition->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht entfernt werden.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Condition/GradeType/Select', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblScoreCondition->getId()));
        }
    }

    /**
     * @param TblScoreConditionGroupRequirement $tblScoreConditionGroupRequirement
     *
     * @return string
     */
    public function removeScoreConditionGroupRequirement(TblScoreConditionGroupRequirement $tblScoreConditionGroupRequirement): string
    {
        $tblScoreCondition = $tblScoreConditionGroupRequirement->getTblScoreCondition();
        if ((new Data($this->getBinding()))->removeScoreConditionGroupRequirement($tblScoreConditionGroupRequirement)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich entfernt.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Condition/GradeType/Select', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblScoreCondition->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht entfernt werden.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Condition/GradeType/Select', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblScoreCondition->getId()));
        }
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return TblGradeType[]|bool
     */
    public function getGradeTypeAllByScoreCondition(TblScoreCondition $tblScoreCondition)
    {
        $tblGradeTypeList = array();
        if (($tblScoreConditionGroupList = $this->getScoreConditionGroupListByCondition($tblScoreCondition))) {
            foreach ($tblScoreConditionGroupList as $item) {
                if (($tblScoreGroup = $item->getTblScoreGroup())
                    && ($tblScoreGroupGradeTypeList = $this->getScoreGroupGradeTypeListByGroup($tblScoreGroup))
                ) {
                    foreach ($tblScoreGroupGradeTypeList as $subItem) {
                        if (($tblGradeType = $subItem->getTblGradeType())) {
                            $tblGradeTypeList[$tblGradeType->getId()] = $tblGradeType;
                        }
                    }
                }
            }
        }

        return empty($tblGradeTypeList) ? false : $tblGradeTypeList;
    }

    /**
     * @param $id
     *
     * @return false|TblScoreGroup
     */
    public function getScoreGroupById($id)
    {
        return (new Data($this->getBinding()))->getScoreGroupById($id);
    }

    /**
     * @return array
     */
    public function migrateScoreRules(): array
    {
        return (new Data($this->getBinding()))->migrateScoreRules();
    }

    /**
     * @param bool $withInActive
     *
     * @return false|TblScoreGroup[]
     */
    public function getScoreGroupAll(bool $withInActive = false)
    {
        return (new Data($this->getBinding()))->getScoreGroupAll($withInActive);
    }

    /**
     * @param TblScoreGroup $tblScoreGroup
     *
     * @return bool|TblScoreGroupGradeTypeList[]
     */
    public function getScoreGroupGradeTypeListByGroup(TblScoreGroup $tblScoreGroup)
    {
        return (new Data($this->getBinding()))->getScoreGroupGradeTypeListByGroup($tblScoreGroup);
    }

    /**
     * @param TblScoreGroup $tblScoreGroup
     *
     * @return bool
     */
    public function getIsScoreGroupUsed(TblScoreGroup $tblScoreGroup): bool
    {
        if ((new Data($this->getBinding()))->getScoreConditionGroupListByGroup($tblScoreGroup)) {
            return true;
        }
        if ((new Data($this->getBinding()))->getScoreConditionGroupRequirementAllByGroup($tblScoreGroup)) {
            return true;
        }

        return false;
    }

    /**
     * @param IFormInterface|null $form
     * @param                     $ScoreGroup
     *
     * @return IFormInterface|string
     */
    public function createScoreGroup(IFormInterface $form = null, $ScoreGroup = null)
    {
        /**
         * Skip to Frontend
         */
        if (null === $ScoreGroup) {
            return $form;
        }

        $Error = false;
        if (isset($ScoreGroup['Name']) && empty($ScoreGroup['Name'])) {
            $form->setError('ScoreGroup[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }
        if (isset($ScoreGroup['Multiplier']) && empty($ScoreGroup['Multiplier'])) {
            $form->setError('ScoreGroup[Multiplier]', 'Bitte geben sie einen Faktor an');
            $Error = true;
        } elseif (isset($ScoreGroup['Multiplier']) && !preg_match(self::PREG_MATCH_DECIMAL_NUMBER, $ScoreGroup['Multiplier'])) {
            $form->setError('ScoreGroup[Multiplier]', 'Bitte geben sie eine Zahl als Faktor an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createScoreGroup(
                $ScoreGroup['Name'],
                $ScoreGroup['Multiplier'],
                isset($ScoreGroup['IsEveryGradeASingleGroup'])
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Zensuren-Gruppe ist erfasst worden')
                . new Redirect('/Education/Graduation/Grade/ScoreRule/Group', Redirect::TIMEOUT_SUCCESS);
        }

        return $form;
    }

    /**
     * @param IFormInterface|null $form
     * @param $Id
     * @param $ScoreGroup
     *
     * @return IFormInterface|string
     */
    public function updateScoreGroup(IFormInterface $form = null, $Id, $ScoreGroup)
    {
        /**
         * Skip to Frontend
         */
        if (null === $ScoreGroup || null === $Id) {
            return $form;
        }

        $Error = false;
        if (isset($ScoreGroup['Name']) && empty($ScoreGroup['Name'])) {
            $form->setError('ScoreGroup[Name]', 'Bitte geben sie einen Namen an');
            $Error = true;
        }
        if (isset($ScoreGroup['Multiplier']) && empty($ScoreGroup['Multiplier'])) {
            $form->setError('ScoreGroup[Multiplier]', 'Bitte geben sie einen Faktor an');
            $Error = true;
        } elseif (isset($ScoreGroup['Multiplier']) && !preg_match(self::PREG_MATCH_DECIMAL_NUMBER, $ScoreGroup['Multiplier'])) {
            $form->setError('ScoreGroup[Multiplier]', 'Bitte geben sie eine Zahl als Faktor an');
            $Error = true;
        }

        $tblScoreGroup = $this->getScoreGroupById($Id);
        if (!$tblScoreGroup) {
            return new Danger(new Ban() . ' Zensuren-Gruppe nicht gefunden')
                . new Redirect('/Education/Graduation/Grade/ScoreRule/Group', Redirect::TIMEOUT_ERROR);
        }

        if (!$Error) {
            (new Data($this->getBinding()))->updateScoreGroup(
                $tblScoreGroup,
                $ScoreGroup['Name'],
                $ScoreGroup['Multiplier'],
                isset($ScoreGroup['IsEveryGradeASingleGroup']),
                $tblScoreGroup->getIsActive()
            );
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Zensuren-Gruppe ist erfolgreich gespeichert worden')
                . new Redirect('/Education/Graduation/Grade/ScoreRule/Group', Redirect::TIMEOUT_SUCCESS);
        }

        return $form;
    }

    /**
     * @param TblScoreGroup $tblScoreGroup
     * @param bool $IsActive
     *
     * @return bool
     */
    public function setScoreGroupActive(TblScoreGroup $tblScoreGroup, bool $IsActive = true): bool
    {
        return (new Data($this->getBinding()))->updateScoreGroup($tblScoreGroup, $tblScoreGroup->getName(), $tblScoreGroup->getMultiplier(),
            $tblScoreGroup->getIsEveryGradeASingleGroup(), $IsActive);
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreGroupGradeTypeList
     */
    public function getScoreGroupGradeTypeListById($Id)
    {
        return (new Data($this->getBinding()))->getScoreGroupGradeTypeListById($Id);
    }

    /**
     * @param TblGradeType $tblGradeType
     * @param TblScoreGroup $tblScoreGroup
     * @param $Multiplier
     *
     * @return string
     */
    public function addScoreGroupGradeTypeList(TblGradeType $tblGradeType, TblScoreGroup $tblScoreGroup, $Multiplier): string
    {
        if ((new Data($this->getBinding()))->addScoreGroupGradeTypeList($tblGradeType, $tblScoreGroup, $Multiplier)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich hinzugefügt.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Group/GradeType/Select', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblScoreGroup->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht hinzugefügt werden.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Group/GradeType/Select', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblScoreGroup->getId()));
        }
    }

    /**
     * @param TblScoreGroupGradeTypeList $tblScoreGroupGradeTypeList
     *
     * @return string
     */
    public function removeScoreGroupGradeTypeList(TblScoreGroupGradeTypeList $tblScoreGroupGradeTypeList): string
    {
        $tblScoreGroup = $tblScoreGroupGradeTypeList->getTblScoreGroup();
        if ((new Data($this->getBinding()))->removeScoreGroupGradeTypeList($tblScoreGroupGradeTypeList)) {
            return new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Erfolgreich entfernt.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Group/GradeType/Select', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblScoreGroup->getId()));
        } else {
            return new Danger(new Ban() . ' Konnte nicht entfernt werden.') .
                new Redirect('/Education/Graduation/Grade/ScoreRule/Group/GradeType/Select', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblScoreGroup->getId()));
        }
    }

    /**
     * @param TblScoreGroup $tblScoreGroup
     *
     * @return bool
     */
    public function destroyScoreGroup(TblScoreGroup $tblScoreGroup): bool
    {
        return (new Data($this->getBinding()))->destroyScoreGroup($tblScoreGroup);
    }
}