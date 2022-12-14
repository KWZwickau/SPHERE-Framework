<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Education\Graduation\Grade\Service\Data;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreGroup;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreGroupGradeTypeList;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRule;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRuleSubject;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRuleSubjectDivisionCourse;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreTypeSubject;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;

abstract class ServiceScore extends ServiceGradeType
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
     * @param TblType|null $tblSchoolType
     *
     * @return false|TblScoreRuleSubject[]
     */
    public function getScoreRuleSubjectListByScoreRule(TblScoreRule $tblScoreRule, ?TblType $tblSchoolType)
    {
        return (new Data($this->getBinding()))->getScoreRuleSubjectListByScoreRule($tblScoreRule, $tblSchoolType);
    }

    /**
     * @param TblScoreRule $tblScoreRule
     *
     * @return false|TblScoreRuleSubjectDivisionCourse[]
     */
    public function getScoreRuleSubjectDivisionCourseListByScoreRule(TblScoreRule $tblScoreRule)
    {
        return (new Data($this->getBinding()))->getScoreRuleSubjectDivisionCourseListByScoreRule($tblScoreRule);
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
        // todo
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