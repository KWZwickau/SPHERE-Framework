<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Data;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTable;
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
}