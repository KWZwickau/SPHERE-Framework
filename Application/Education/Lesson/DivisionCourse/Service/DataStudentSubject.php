<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;

abstract class DataStudentSubject extends DataMigrate
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
        $parameters[TblStudentSubject::ATTR_SERVICE_TBL_PERSON] = $tblPerson->getId();
        $parameters[TblStudentSubject::ATTR_SERVICE_TBL_YEAR] = $tblYear->getId();
        if ($hasGrading !== null) {
            $parameters[TblStudentSubject::ATTR_HAS_GRADING] = $hasGrading;
        }

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblStudentSubject', $parameters);
    }
}