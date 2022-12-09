<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use SPHERE\Application\Education\Graduation\Grade\Service\Data;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\Score\TblScoreTypeSubject;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;

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
}