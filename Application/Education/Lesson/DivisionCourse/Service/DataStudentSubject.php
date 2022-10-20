<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentSubject;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTable;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Element;

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

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     *
     * @return false|TblStudentSubject
     */
    public function getStudentSubjectByPersonAndYearAndSubject(TblPerson $tblPerson, TblYear $tblYear, TblSubject $tblSubject)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblStudentSubject', array(
            TblStudentSubject::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblStudentSubject::ATTR_SERVICE_TBL_YEAR => $tblYear->getId(),
            TblStudentSubject::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
        ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubjectTable $tblSubjectTable
     *
     * @return false|TblStudentSubject
     */
    public function getStudentSubjectByPersonAndYearAndSubjectTable(TblPerson $tblPerson, TblYear $tblYear, TblSubjectTable $tblSubjectTable)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblStudentSubject', array(
            TblStudentSubject::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblStudentSubject::ATTR_SERVICE_TBL_YEAR => $tblYear->getId(),
            TblStudentSubject::ATTR_SERVICE_TBL_SUBJECT_TABLE => $tblSubjectTable->getId(),
        ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblDivisionCourse $tblSubjectDivisionCourse
     *
     * @return false|TblStudentSubject[]
     */
    public function getStudentSubjectListByPersonAndYearAndDivisionCourse(TblPerson $tblPerson, TblYear $tblYear, TblDivisionCourse $tblSubjectDivisionCourse)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblStudentSubject', array(
            TblStudentSubject::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblStudentSubject::ATTR_SERVICE_TBL_YEAR => $tblYear->getId(),
            TblStudentSubject::ATTR_TBL_DIVISION_COURSE => $tblSubjectDivisionCourse->getId(),
        ));
    }

    /**
     * @param array $tblStudentSubjectList
     *
     * @return bool
     */
    public function createStudentSubjectBulkList(array $tblStudentSubjectList): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        foreach ($tblStudentSubjectList as $tblStudentSubject) {
            $Manager->bulkSaveEntity($tblStudentSubject);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $tblStudentSubject, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param array $tblStudentSubjectList
     *
     * @return bool
     */
    public function updateStudentSubjectBulkList(array $tblStudentSubjectList): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        foreach ($tblStudentSubjectList as $tblStudentSubject) {
            $Manager->bulkSaveEntity($tblStudentSubject);
            /** @var TblStudentSubject $Entity */
            $Entity = $Manager->getEntityById('TblStudentSubject', $tblStudentSubject->getId());
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Entity, $tblStudentSubject, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }
    
    /**
     * @param array $tblStudentSubjectList
     * 
     * @return bool
     */
    public function destroyStudentSubjectBulkList(array $tblStudentSubjectList): bool 
    {
        $Manager = $this->getEntityManager();

        foreach ($tblStudentSubjectList as $tblStudentSubject) {
            /** @var Element $Entity */
            $Entity = $Manager->getEntityById('TblStudentSubject', $tblStudentSubject->getId());
            if (null !== $Entity) {
                $Manager->bulkKillEntity($Entity);
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
            }
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }
}