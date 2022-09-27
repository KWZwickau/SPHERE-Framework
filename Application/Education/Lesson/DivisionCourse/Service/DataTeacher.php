<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

abstract class DataTeacher extends MigrateData
{
    /**
     * @param TblYear|null $tblYear
     * @param TblPerson|null $tblTeacher
     * @param TblDivisionCourse|null $tblDivisionCourse
     * @param TblSubject|null $tblSubject
     *
     * @return false|TblTeacherLectureship[]
     */
    public function getTeacherLectureshipListBy(TblYear $tblYear = null, TblPerson $tblTeacher = null, TblDivisionCourse $tblDivisionCourse = null, TblSubject $tblSubject = null)
    {
        $parameterList = array();
        if ($tblYear) {
            $parameterList[TblTeacherLectureship::ATTR_SERVICE_TBL_YEAR] = $tblYear->getId();
        }
        if ($tblTeacher) {
            $parameterList[TblTeacherLectureship::ATTR_SERVICE_TBL_PERSON] = $tblTeacher->getId();
        }
        if ($tblDivisionCourse) {
            $parameterList[TblTeacherLectureship::ATTR_TBL_DIVISION_COURSE] = $tblDivisionCourse->getId();
        }
        if ($tblSubject) {
            $parameterList[TblTeacherLectureship::ATTR_SERVICE_TBL_SUBJECT] = $tblSubject->getId();
        }

        if ($parameterList) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblTeacherLectureship', $parameterList);
        } else {
            return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblTeacherLectureship');
        }
    }
}