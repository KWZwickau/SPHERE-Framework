<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Data;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Setup;
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
     * @param $Id
     *
     * @return false|TblDivisionCourse
     */
    public function getDivisionCourseById($Id)
    {
        return (new Data($this->getBinding()))->getDivisionCourseById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseType
     */
    public function getDivisionCourseTypeById($Id)
    {
        return (new Data($this->getBinding()))->getDivisionCourseTypeById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseMemberType
     */
    public function getMemberTypeById($Id)
    {
        return (new Data($this->getBinding()))->getMemberTypeById($Id);
    }
}