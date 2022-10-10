<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Data;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTable;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\System\Database\Binding\AbstractService;

abstract class ServiceSubjectTable extends AbstractService
{
    /**
     * @param $Id
     *
     * @return false|TblSubjectTable
     */
    public function getSubjectTableById($Id)
    {
        return (new Data($this->getBinding()))->getSubjectTableById($Id);
    }

    /**
     * @param TblType $tblSchoolType
     * @param int|null $level
     *
     * @return false|TblSubjectTable[]
     */
    public function getSubjectTableListBy(TblType $tblSchoolType, ?int $level = null)
    {
        return (new Data($this->getBinding()))->getSubjectTableListBy($tblSchoolType, $level);
    }
}