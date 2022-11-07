<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblTeacherLectureship;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;

abstract class DataTeacher extends DataSubjectTable
{
    /**
     * @param $Id
     *
     * @return false|TblTeacherLectureship
     */
    public function getTeacherLectureshipById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblTeacherLectureship', $Id);
    }

    /**
     * @param TblYear|null $tblYear
     * @param TblPerson|null $tblPerson
     * @param TblDivisionCourse|null $tblDivisionCourse
     * @param TblSubject|null $tblSubject
     *
     * @return false|TblTeacherLectureship[]
     */
    public function getTeacherLectureshipListBy(TblYear $tblYear = null, TblPerson $tblPerson = null, TblDivisionCourse $tblDivisionCourse = null, TblSubject $tblSubject = null)
    {
        $parameterList = array();
        if ($tblYear) {
            $parameterList[TblTeacherLectureship::ATTR_SERVICE_TBL_YEAR] = $tblYear->getId();
        }
        if ($tblPerson) {
            $parameterList[TblTeacherLectureship::ATTR_SERVICE_TBL_PERSON] = $tblPerson->getId();
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

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     * @param string $groupName
     *
     * @return TblTeacherLectureship
     */
    public function createTeacherLectureship(TblPerson $tblPerson, TblYear $tblYear, TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject,
        string $groupName = ''): TblTeacherLectureship
    {
        $Manager = $this->getConnection()->getEntityManager(false);
        $Entity = $Manager->getEntity('TblTeacherLectureship')->findOneBy(array(
            TblTeacherLectureship::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblTeacherLectureship::ATTR_SERVICE_TBL_YEAR => $tblYear->getId(),
            TblTeacherLectureship::ATTR_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
            TblTeacherLectureship::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
            TblTeacherLectureship::ATTR_GROUP_NAME => $groupName
        ));

        if (null === $Entity) {
            $Entity = TblTeacherLectureship::withParameter($tblPerson, $tblYear, $tblDivisionCourse, $tblSubject, $groupName);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblTeacherLectureship $tblTeacherLectureship
     *
     * @return bool
     */
    public function destroyTeacherLectureship(TblTeacherLectureship $tblTeacherLectureship): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblTeacherLectureship $Entity */
        $Entity = $Manager->getEntityById('TblTeacherLectureship', $tblTeacherLectureship->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return false|TblSubject[]
     */
    public function getSubjectListByTeacherAndYear(TblPerson $tblPerson, TblYear $tblYear)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t.serviceTblSubject')
            ->from(__NAMESPACE__ . '\Entity\TblTeacherLectureship', 't')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t.serviceTblPerson', '?1'),
                    $queryBuilder->expr()->eq('t.serviceTblYear', '?2')
                )
            )
            ->setParameter(1, $tblPerson->getId())
            ->setParameter(2, $tblYear->getId())
            ->distinct()
            ->groupBy('t.serviceTblSubject')
            ->getQuery();

        $resultList = $query->getResult();

        if (empty($resultList)) {
            return false;
        } else {
            $subjectList = array();
            foreach ($resultList as $item) {
                if (($tblSubject = Subject::useService()->getSubjectById($item['serviceTblSubject']))) {
                    $subjectList[$tblSubject->getId()] = $tblSubject;
                }
            }

            return $subjectList;
        }
    }
}