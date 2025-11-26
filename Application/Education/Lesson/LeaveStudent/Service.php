<?php

namespace SPHERE\Application\Education\Lesson\LeaveStudent;

use SPHERE\Application\Education\Lesson\LeaveStudent\Service\Data;
use SPHERE\Application\Education\Lesson\LeaveStudent\Service\Entity\TblLeaveStudent;
use SPHERE\Application\Education\Lesson\LeaveStudent\Service\Setup;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\System\Database\Binding\AbstractService;

class Service extends AbstractService
{
    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8): string
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
     * @param TblType $tblSchoolType
     * @param TblYear $tblYear
     *
     * @return false|TblLeaveStudent
     */
    public function getLeaveStudentBy(TblType $tblSchoolType, TblYear $tblYear): false|TblLeaveStudent
    {
        return (new Data($this->getBinding()))->getLeaveStudentBy($tblSchoolType, $tblYear);
    }

    /**
     * @param TblType $tblSchoolType
     * @param TblYear $tblYear
     * @param array $Data
     *
     * @return TblLeaveStudent
     */
    public function updateLeaveStudent(TblType $tblSchoolType, TblYear $tblYear, array $Data): TblLeaveStudent
    {
        return (new Data($this->getBinding()))->updateLeaveStudent($tblSchoolType, $tblYear, $Data);
    }
}