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
     * SEKI
     *
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
     * SEKII
     *
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     *
     * @return false|TblStudentSubject
     */
    public function getStudentSubjectByPersonAndYearAndSubjectForCourseSystem(TblPerson $tblPerson, TblYear $tblYear, TblSubject $tblSubject)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t')
            ->from(TblStudentSubject::class, 't')
            ->join(TblDivisionCourse::class, 'c')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t.tblLessonDivisionCourse', 'c.Id'),
                    $queryBuilder->expr()->eq('t.serviceTblPerson', '?1'),
                    $queryBuilder->expr()->eq('t.serviceTblYear', '?2'),
                    $queryBuilder->expr()->eq('c.serviceTblSubject', '?3'),
                ),
            )
            ->setParameter(1, $tblPerson->getId())
            ->setParameter(2, $tblYear->getId())
            ->setParameter(3, $tblSubject->getId())
            ->distinct()
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : current($resultList);
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
     * @param TblDivisionCourse $tblSubjectDivisionCourse
     *
     * @return false|TblStudentSubject[]
     */
    public function getStudentSubjectListBySubjectDivisionCourse(TblDivisionCourse $tblSubjectDivisionCourse)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblStudentSubject', array(
            TblStudentSubject::ATTR_TBL_DIVISION_COURSE => $tblSubjectDivisionCourse->getId(),
        ));
    }

    /**
     * @param TblDivisionCourse $tblSubjectDivisionCourse
     * @param $Period
     *
     * @return false|TblStudentSubject[]
     */
    public function getStudentSubjectListBySubjectDivisionCourseAndPeriod(TblDivisionCourse $tblSubjectDivisionCourse, $Period)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblStudentSubject', 't')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t.tblLessonDivisionCourse', '?1'),
                    $queryBuilder->expr()->like('t.PeriodIdentifier', '?2')
                )
            )
            ->setParameter(1, $tblSubjectDivisionCourse->getId())
            ->setParameter(2, '%/' . $Period)
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
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