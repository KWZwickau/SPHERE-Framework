<?php

namespace SPHERE\Application\Education\ClassRegister\Diary\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblClassRegisterDiaryPredecessorDivisionCourse")
 * @Cache(usage="READ_ONLY")
 */
class TblDiaryPredecessorDivisionCourse extends Element
{
    const ATTR_SERVICE_TBL_DIVISION_COURSE = 'serviceTblDivisionCourse';
    const ATTR_SERVICE_TBL_PREDECESSOR_DIVISION_COURSE = 'serviceTblPredecessorDivisionCourse';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivisionCourse;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPredecessorDivisionCourse;

    /**
     * @return bool|TblDivisionCourse
     */
    public function getServiceTblDivisionCourse()
    {
        if (null === $this->serviceTblDivisionCourse) {
            return false;
        } else {
            return DivisionCourse::useService()->getDivisionCourseById($this->serviceTblDivisionCourse);
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     */
    public function setServiceTblDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        $this->serviceTblDivisionCourse = $tblDivisionCourse->getId();
    }

    /**
     * @return bool|TblDivisionCourse
     */
    public function getServiceTblPredecessorDivisionCourse()
    {
        if (null === $this->serviceTblPredecessorDivisionCourse) {
            return false;
        } else {
            return DivisionCourse::useService()->getDivisionCourseById($this->serviceTblPredecessorDivisionCourse);
        }
    }

    /**
     * @param TblDivisionCourse $tblPredecessorDivisionCourse
     */
    public function setServiceTblPredecessorDivisionCourse(TblDivisionCourse $tblPredecessorDivisionCourse)
    {
        $this->serviceTblPredecessorDivisionCourse = $tblPredecessorDivisionCourse->getId();
    }
}