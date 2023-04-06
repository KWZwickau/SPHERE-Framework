<?php

namespace SPHERE\Application\Education\ClassRegister\Diary\ServiceOld;

use SPHERE\Application\Education\ClassRegister\Diary\ServiceOld\Entity\TblDiary;
use SPHERE\Application\Education\ClassRegister\Diary\ServiceOld\Entity\TblDiaryDivision;
use SPHERE\Application\Education\ClassRegister\Diary\ServiceOld\Entity\TblDiaryStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * @deprecated
 *
 * Class Data
 *
 * @package SPHERE\Application\Education\ClassRegister\Diary\Service\Entity
 */
class Data extends AbstractData
{
    public function setupDatabaseContent()
    {

    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblDiary[]
     */
    public function getDiaryListByYear(TblYear $tblYear)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDiary', array(
           TblDiary::ATTR_SERVICE_TBL_YEAR => $tblYear->getId()
        ));
    }

    /**
     * @param $Id
     *
     * @return false|TblDiary
     */
    public function getDiaryById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblDiary', $Id);
    }

    /**
     * @param TblDiary $tblDiary
     *
     * @return false|TblDiaryStudent[]
     */
    public function getDiaryStudentAllByDiary(TblDiary $tblDiary)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDiaryStudent', array(
            TblDiaryStudent::ATTR_TBL_DIARY => $tblDiary->getId()
        ));
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblDiaryDivision[]
     */
    public function getDiaryDivisionByDivision(TblDivision $tblDivision)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDiaryDivision', array(
            TblDiaryDivision::ATTR_SERVICE_TBL_DIVISION => $tblDivision->getId()
        ));
    }
}