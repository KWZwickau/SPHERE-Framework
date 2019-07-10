<?php

namespace SPHERE\Application\Education\ClassRegister\Diary;

use SPHERE\Application\Education\ClassRegister\Diary\Service\Data;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Entity\TblDiary;
use SPHERE\Application\Education\ClassRegister\Diary\Service\Setup;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\ClassRegister\Diary
 */
class Service extends AbstractService
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

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return false|TblDiary
     */
    public function getDiaryById($Id)
    {
        return (new Data($this->getBinding()))->getDiaryAllByDivision($Id);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblDiary[]
     */
    public function getDiaryAllByDivision(TblDivision $tblDivision)
    {
        return (new Data($this->getBinding()))->getDiaryAllByDivision($tblDivision);
    }
}