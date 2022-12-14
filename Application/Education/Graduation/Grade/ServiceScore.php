<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Education\Graduation\Grade\Service\Data;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreGroup;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRule;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreTypeSubject;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

abstract class ServiceScore extends ServiceGradeType
{
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
     * @param $id
     *
     * @return false|TblScoreCondition
     */
    public function getScoreConditionById($id)
    {
        return (new Data($this->getBinding()))->getScoreConditionById($id);
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
}