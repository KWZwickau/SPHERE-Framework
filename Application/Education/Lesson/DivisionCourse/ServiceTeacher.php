<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Data;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

abstract class ServiceTeacher extends AbstractService
{
    /**
     * @param TblYear|null $tblYear
     * @param TblPerson|null $tblTeacher
     * @param TblSubject|null $tblSubject
     *
     * @return false|TblTeacherLectureship[]
     */
    public function getTeacherLectureshipListBy(TblYear $tblYear = null, TblPerson $tblTeacher = null, TblSubject $tblSubject = null)
    {
        return (new Data($this->getBinding()))->getTeacherLectureshipListBy($tblYear, $tblTeacher, $tblSubject);
    }
}