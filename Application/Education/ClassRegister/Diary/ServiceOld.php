<?php

namespace SPHERE\Application\Education\ClassRegister\Diary;

use SPHERE\Application\Education\ClassRegister\Diary\ServiceOld\Data;
use SPHERE\Application\Education\ClassRegister\Diary\ServiceOld\Entity\TblDiary;
use SPHERE\Application\Education\ClassRegister\Diary\ServiceOld\Entity\TblDiaryDivision;
use SPHERE\Application\Education\ClassRegister\Diary\ServiceOld\Entity\TblDiaryStudent;
use SPHERE\Application\Education\ClassRegister\Diary\ServiceOld\Setup;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * @deprecated
 *
 * Class Service
 *
 * @package SPHERE\Application\Education\ClassRegister\Diary
 */
class ServiceOld extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol = '';
        if (!$withData) {
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblDiary[]
     */
    public function getDiaryListByYear(TblYear $tblYear)
    {
        return (new Data($this->getBinding()))->getDiaryListByYear($tblYear);
    }

    /**
     * @param $Id
     *
     * @return false|TblDiary
     */
    public function getDiaryById($Id)
    {
        return (new Data($this->getBinding()))->getDiaryById($Id);
    }

    /**
     * @param TblDiary $tblDiary
     *
     * @return false|TblDiaryStudent[]
     */
    public function getDiaryStudentAllByDiary(TblDiary $tblDiary)
    {
        return (new Data($this->getBinding()))->getDiaryStudentAllByDiary($tblDiary);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblDiaryDivision[]
     */
    public function getDiaryDivisionByDivision(TblDivision $tblDivision)
    {
        return (new Data($this->getBinding()))->getDiaryDivisionByDivision($tblDivision);
    }
}
