<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Data;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

abstract class ServiceStudentSubject extends AbstractService
{
    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param bool|null $hasGrading
     *
     * @return false|TblStudentSubject[]
     */
    public function getStudentSubjectListByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear, ?bool $hasGrading = null)
    {
        return (new Data($this->getBinding()))->getStudentSubjectListByPersonAndYear($tblPerson, $tblYear);
    }
}