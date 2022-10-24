<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblLessonDivisionCourseLink")
 * @Cache(usage="READ_ONLY")
 */
class TblDivisionCourseLink extends Element
{
    const ATTR_TBL_DIVISION_COURSE = 'tblLessonDivisionCourse';
    const ATTR_TBL_SUB_DIVISION_COURSE = 'tblSubLessonDivisionCourse';

    /**
     * @Column(type="bigint")
     */
    protected int $tblLessonDivisionCourse;

    /**
     * @Column(type="bigint")
     */
    protected int $tblSubLessonDivisionCourse;

    /**
     * @return TblDivisionCourse|false
     */
    public function getTblDivisionCourse()
    {
        if ($this->tblLessonDivisionCourse) {
            return DivisionCourse::useService()->getDivisionCourseById($this->tblLessonDivisionCourse);
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblLessonDivisionCourse
     */
    public function setTblDivisionCourse(TblDivisionCourse $tblLessonDivisionCourse): void
    {
        $this->tblLessonDivisionCourse = $tblLessonDivisionCourse->getId();
    }

    /**
     * @return TblDivisionCourse|false
     */
    public function getTblSubDivisionCourse()
    {
        if ($this->tblSubLessonDivisionCourse) {
            return DivisionCourse::useService()->getDivisionCourseById($this->tblSubLessonDivisionCourse);
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblSubLessonDivisionCourse
     */
    public function setTblSubDivisionCourse(TblDivisionCourse $tblSubLessonDivisionCourse): void
    {
        $this->tblSubLessonDivisionCourse = $tblSubLessonDivisionCourse->getId();
    }
}