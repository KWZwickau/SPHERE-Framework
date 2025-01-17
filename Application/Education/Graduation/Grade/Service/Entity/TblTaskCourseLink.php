<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGraduationTaskCourseLink")
 * @Cache(usage="READ_ONLY")
 */
class TblTaskCourseLink extends Element
{
    const ATTR_TBL_TASK = 'tblGraduationTask';
    const ATTR_SERVICE_TBL_DIVISION_COURSE = 'serviceTblDivisionCourse';

    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationTask;
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblDivisionCourse;

    /**
     * @param TblTask $tblTask
     * @param TblDivisionCourse $tblDivisionCourse
     */
    public function __construct(TblTask $tblTask, TblDivisionCourse $tblDivisionCourse)
    {
        $this->tblGraduationTask = $tblTask->getId();
        $this->serviceTblDivisionCourse = $tblDivisionCourse->getId();
    }

    /**
     * @return TblTask
     */
    public function getTblTask(): TblTask
    {
        return Grade::useService()->getTaskById($this->tblGraduationTask);
    }

    /**
     * @param TblTask $tblTask
     */
    public function setTblTask(TblTask $tblTask)
    {
        $this->tblGraduationTask = $tblTask->getId();
    }

    /**
     * @return false|TblDivisionCourse
     */
    public function getServiceTblDivisionCourse()
    {
        return DivisionCourse::useService()->getDivisionCourseById($this->serviceTblDivisionCourse);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     */
    public function setServiceTblDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        $this->serviceTblDivisionCourse = $tblDivisionCourse->getId();
    }
}