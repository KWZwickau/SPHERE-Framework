<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service;

use DateTime;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblMinimumGradeCount;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreConditionGradeTypeList;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreGroupGradeTypeList;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTaskGradeTypeLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTestCourseLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTestGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Element;

class Data extends DataTask
{
    public function setupDatabaseContent()
    {
        $this->createScoreType('Noten (1-6) mit Tendenz', 'GRADES', '^([1-6]{1}|[1-5]{1}[+-]{1})$');
        $this->createScoreType('Noten (1-6) mit Komma', 'GRADES_COMMA', '^(6((\.|,)0+)?|[1-5]{1}((\.|,)[0-9]+)?)$');
        $this->createScoreType('Noten (1-5) mit Tendenz', 'GRADES_BEHAVIOR_TASK', '^([1-5]{1}|[1-4]{1}[+-]{1})$');
        $this->createScoreType('Noten (1-5) mit Komma', 'GRADES_V1', '^(5((\.|,)0+)?|[1-4]{1}((\.|,)[0-9]+)?)$');
        $this->createScoreType('Punkte (0-15)', 'POINTS', '^([0-9]{1}|1[0-5]{1})$');
        $this->createScoreType('Verbale Bewertung', 'VERBAL', '');

        if (!$this->getGradeTypeAll(true)) {
            if (Gradebook::useService()->getGradeTypeAll()) {
                // alte Daten migrieren
                $this->migrateTblGradeType();
            } else {
                $this->createGradeType('Betragen', 'KBE', 'Kopfnote Betragen', true, false, false, true);
                $this->createGradeType('Fleiß', 'KFL', 'Kopfnote Fleiß', true, false, false, true);
                $this->createGradeType('Mitarbeit', 'KMI', 'Kopfnote Mitarbeit', true, false, false, true);
                $this->createGradeType('Ordnung', 'KOR', 'Kopfnote Ordnung', true, false, false, true);
            }
        }

        $this->createGradeText('nicht erteilt', 'n.e.', 'NOT_GRANTED');
        $this->createGradeText('teilgenommen', 't.','ATTENDED');
        $this->createGradeText('keine Benotung', 'k.B.', 'NO_GRADING');
        $this->createGradeText('befreit', 'b', 'LIBERATED');
        $this->createGradeText('&ndash;', '&ndash;', 'DASH');
    }

    /**
     * @param $id
     *
     * @return false|TblGradeType
     */
    public function getGradeTypeById($id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblGradeType', $id);
    }

    /**
     * @param string $Code
     *
     * @return bool|TblGradeType
     */
    public function getGradeTypeByCode(string $Code)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblGradeType', array(TblGradeType::ATTR_CODE => $Code));
    }

    /**
     * @param bool $withInActive
     *
     * @return false|TblGradeType[]
     */
    public function getGradeTypeAll(bool $withInActive = false)
    {
        return $withInActive
            ? $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblGradeType')
            : $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblGradeType', array(TblGradeType::ATTR_IS_ACTIVE => true));
    }

    /**
     * @param bool $isTypeBehavior
     *
     * @return false|TblGradeType[]
     */
    public function getGradeTypeList(bool $isTypeBehavior = false)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblGradeType', array(
            TblGradeType::ATTR_IS_TYPE_BEHAVIOR => $isTypeBehavior,
            TblGradeType::ATTR_IS_ACTIVE => true
        ), array(TblGradeType::ATTR_NAME => self::ORDER_ASC));
    }

    /**
     * @param $id
     *
     * @return false|TblGradeText
     */
    public function getGradeTextById($id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblGradeText', $id);
    }

    /**
     * @param string $name
     *
     * @return false|TblGradeText
     */
    public function getGradeTextByName(string $name)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblGradeText', array(
            TblGradeText::ATTR_NAME => $name
        ));
    }

    /**
     * @return false|TblGradeText[]
     */
    public function getGradeTextAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblGradeText');
    }

    /**
     * @param $id
     *
     * @return false|TblTest
     */
    public function getTestById($id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblTest', $id);
    }

    /**
     * @param TblTest $tblTest
     *
     * @return false|TblDivisionCourse[]
     */
    public function getDivisionCourseListByTest(TblTest $tblTest)
    {
        $resultList = array();
        if (($tempList = $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblTestCourseLink',
            array(TblTestCourseLink::ATTR_TBL_TEST => $tblTest->getId())))
        ) {
            /** @var TblTestCourseLink $tblTestCourseLink */
            foreach ($tempList as $tblTestCourseLink) {
                if (($tblDivisionCourse = $tblTestCourseLink->getServiceTblDivisionCourse())) {
                    $resultList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblTest $tblTest
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblTestCourseLink
     */
    public function getTestCourseLinkBy(TblTest $tblTest, TblDivisionCourse $tblDivisionCourse)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblTestCourseLink', array(
            TblTestCourseLink::ATTR_TBL_TEST => $tblTest->getId(),
            TblTestCourseLink::ATTR_SERVICE_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
        ));
    }

    /**
     * @param TblTest $tblTest
     *
     * @return false|TblTestCourseLink[]
     */
    public function getTestCourseLinkListByTest(TblTest $tblTest)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblTestCourseLink', array(
            TblTestCourseLink::ATTR_TBL_TEST => $tblTest->getId()
        ));
    }

    /**
     * @param string $name
     * @param string $shortName
     * @param string $identifier
     *
     * @return TblGradeText
     */
    public function createGradeText(string $name, string $shortName, string $identifier): TblGradeText
    {
        $Manager = $this->getEntityManager();
        $identifier = strtoupper($identifier);
        $Entity = $Manager->getEntity('TblGradeText')->findOneBy(array(TblGradeText::ATTR_IDENTIFIER => $identifier));
        if (null === $Entity) {
            $Entity = new TblGradeText();
            $Entity->setName($name);
            $Entity->setShortName($shortName);
            $Entity->setIdentifier($identifier);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param string $code
     * @param string $name
     * @param string $description
     * @param bool $isTypeBehavior
     * @param bool $isHighlighted
     * @param bool $isPartGrade
     * @param bool $isActive
     * @param int|null $id
     *
     * @return TblGradeType
     */
    public function createGradeType(string $code, string $name, string $description,
        bool $isTypeBehavior, bool $isHighlighted, bool $isPartGrade, bool $isActive, ?int $id = null): TblGradeType
    {
        $Manager = $this->getEntityManager();
        $code = strtoupper($code);
        $Entity = $Manager->getEntity('TblGradeType')->findOneBy(array(TblGradeType::ATTR_CODE => $code));
        if (null === $Entity) {
            $Entity = new TblGradeType($code, $name, $description, $isTypeBehavior, $isHighlighted, $isPartGrade, $isActive, $id);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblGradeType $tblGradeType
     * @param string $code
     * @param string $name
     * @param string $description
     * @param bool $isTypeBehavior
     * @param bool $isHighlighted
     * @param bool $isPartGrade
     * @param bool $isActive
     *
     * @return bool
     */
    public function updateGradeType(TblGradeType $tblGradeType, string $code, string $name, string $description,
        bool $isTypeBehavior, bool $isHighlighted, bool $isPartGrade, bool $isActive): bool
    {
        $Manager = $this->getEntityManager();
        /** @var TblGradeType $Entity */
        $Entity = $Manager->getEntityById('TblGradeType', $tblGradeType->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setCode($code);
            $Entity->setName($name);
            $Entity->setDescription($description);
            $Entity->setIsTypeBehavior($isTypeBehavior);
            $Entity->setIsHighlighted($isHighlighted);
            $Entity->setIsPartGrade($isPartGrade);
            $Entity->setIsActive($isActive);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function deleteGradeType(TblGradeType $tblGradeType): bool
    {
        $Manager = $this->getEntityManager();

        /** @var TblGradeType $Entity */
        $Entity = $Manager->getEntityById('TblGradeType', $tblGradeType->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function getIsGradeTypeUsedInGradeBook(TblGradeType $tblGradeType): bool
    {
        if ($this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblTest',
            array(TblTest::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId()))
        ) {
            return true;
        }

        if ($this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblScoreConditionGradeTypeList',
            array(TblScoreConditionGradeTypeList::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId()))
        ) {
            return true;
        }

        if ($this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblScoreGroupGradeTypeList',
            array(TblScoreGroupGradeTypeList::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId()))
        ) {
            return true;
        }

        if ($this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblMinimumGradeCount',
            array(TblMinimumGradeCount::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId()))
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param TblGradeType $tblGradeType
     *
     * @return bool
     */
    public function getIsGradeTypeUsedInTask(TblGradeType $tblGradeType): bool
    {
        if ($this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblTaskGradeTypeLink',
            array(TblTaskGradeTypeLink::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId()))
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     * @param TblGradeType $tblGradeType
     * @param DateTime|null $Date
     * @param DateTime|null $FinishDate
     * @param DateTime|null $CorrectionDate
     * @param DateTime|null $ReturnDate
     * @param bool $IsContinues
     * @param string $Description
     * @param TblPerson|null $tblTeacher
     *
     * @return TblTest
     */
    public function createTest(TblYear $tblYear, TblSubject $tblSubject, TblGradeType $tblGradeType,
        ?DateTime $Date, ?DateTime $FinishDate, ?DateTime $CorrectionDate, ?DateTime $ReturnDate, bool $IsContinues, string $Description,
        ?TblPerson $tblTeacher): TblTest
    {
        $Manager = $this->getEntityManager();

        $Entity = new TblTest($tblYear, $tblSubject, $tblGradeType, $Date, $FinishDate, $CorrectionDate, $ReturnDate, $IsContinues, $Description, $tblTeacher);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblTest $tblTest
     * @param TblGradeType $tblGradeType
     * @param DateTime|null $Date
     * @param DateTime|null $FinishDate
     * @param DateTime|null $CorrectionDate
     * @param DateTime|null $ReturnDate
     * @param bool $IsContinues
     * @param string $Description
     *
     * @return bool
     */
    public function updateTest(TblTest $tblTest, TblGradeType $tblGradeType,
        ?DateTime $Date, ?DateTime $FinishDate, ?DateTime $CorrectionDate, ?DateTime $ReturnDate, bool $IsContinues, string $Description): bool
    {
        $Manager = $this->getEntityManager();
        /** @var TblTest $Entity */
        $Entity = $Manager->getEntityById('TblTest', $tblTest->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setTblGradeType($tblGradeType);
            $Entity->setDate($Date);
            $Entity->setFinishDate($FinishDate);
            $Entity->setCorrectionDate($CorrectionDate);
            $Entity->setReturnDate($ReturnDate);
            $Entity->setIsContinues($IsContinues);
            $Entity->setDescription($Description);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function createEntityListBulk(array $tblEntityList): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        foreach ($tblEntityList as $tblEntity) {
            $Manager->bulkSaveEntity($tblEntity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $tblEntity, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function updateEntityListBulk(array $tblEntityList): bool
    {
        $Manager = $this->getEntityManager();

        /** @var Element $tblElement */
        foreach ($tblEntityList as $tblElement) {
            $Manager->bulkSaveEntity($tblElement);
            /** @var Element $Entity */
            $Entity = $Manager->getEntityById($tblElement->getEntityShortName(), $tblElement->getId());
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Entity, $tblElement, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function deleteEntityListBulk(array $tblEntityList): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        /** @var Element $tblElement */
        foreach ($tblEntityList as $tblElement) {

            /** @var Element $Entity */
            $Entity = $Manager->getEntityById($tblElement->getEntityShortName(), $tblElement->getId());

            $Manager->bulkKillEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function softRemoveEntityList(array $tblEntityList): bool
    {
        $Manager = $this->getEntityManager();

        /** @var Element $tblElement */
        foreach ($tblEntityList as $tblElement) {

            /** @var Element $Entity */
            $Entity = $Manager->getEntityById($tblElement->getEntityShortName(), $tblElement->getId());

            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
            $Manager->removeEntity($Entity);
        }

        return true;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     *
     * @return TblTest[]|false
     */
    public function getTestListByDivisionCourseAndSubject(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblTest', 't')
            ->join(__NAMESPACE__ . '\Entity\TblTestCourseLink', 'l')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t.Id', 'l.tblGraduationTest'),
                    $queryBuilder->expr()->eq('l.serviceTblDivisionCourse', '?1'),
                    $queryBuilder->expr()->eq('t.serviceTblSubject', '?2'),
                    $queryBuilder->expr()->isNull('t.EntityRemove'),
                ),
            )
            ->setParameter(1, $tblDivisionCourse->getId())
            ->setParameter(2, $tblSubject->getId())
            ->orderBy('t.Date', 'ASC')
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param DateTime $FromDate
     * @param DateTime $ToDate
     *
     * @return TblTest[]|false
     */
    public function getTestListBetween(TblDivisionCourse $tblDivisionCourse, DateTime $FromDate, DateTime $ToDate)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t')
            ->from(TblTest::class, 't')
            ->join(TblTestCourseLink::class, 'l')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t.Id', 'l.tblGraduationTest'),
                    $queryBuilder->expr()->eq('l.serviceTblDivisionCourse', '?1'),
                    $queryBuilder->expr()->gte('t.Date', '?2'),
                    $queryBuilder->expr()->lte('t.Date', '?3'),

                    $queryBuilder->expr()->isNull('t.EntityRemove'),
                ),
            )
            ->setParameter(1, $tblDivisionCourse->getId())
            ->setParameter(2, $FromDate)
            ->setParameter(3, $ToDate)
            ->orderBy('t.Date', 'ASC')
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     *
     * @return TblTestGrade[]|false
     */
    public function getTestGradeListByPersonAndYearAndSubject(TblPerson $tblPerson, TblYear $tblYear, TblSubject $tblSubject)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('g')
            ->from(__NAMESPACE__ . '\Entity\TblTestGrade', 'g')
            ->join(__NAMESPACE__ . '\Entity\TblTest', 't')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('g.tblGraduationTest', 't.Id'),
                    $queryBuilder->expr()->eq('g.serviceTblPerson', '?1'),
                    $queryBuilder->expr()->eq('t.serviceTblYear', '?2'),
                    $queryBuilder->expr()->eq('t.serviceTblSubject', '?3'),
                    $queryBuilder->expr()->isNull('g.EntityRemove'),
                ),
            )
            ->setParameter(1, $tblPerson->getId())
            ->setParameter(2, $tblYear->getId())
            ->setParameter(3, $tblSubject->getId())
            ->orderBy('t.Date', 'ASC')
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     * @param DateTime $fromDate
     * @param DateTime $toDate
     *
     * @return TblTestGrade[]|false
     */
    public function getTestGradeListBetweenDateTimesByPersonAndYearAndSubject(TblPerson $tblPerson, TblYear $tblYear, TblSubject $tblSubject,
        DateTime $fromDate, DateTime $toDate)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('g')
            ->from(__NAMESPACE__ . '\Entity\TblTestGrade', 'g')
            ->join(__NAMESPACE__ . '\Entity\TblTest', 't')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('g.tblGraduationTest', 't.Id'),
                    $queryBuilder->expr()->eq('g.serviceTblPerson', '?1'),
                    $queryBuilder->expr()->eq('t.serviceTblYear', '?2'),
                    $queryBuilder->expr()->eq('t.serviceTblSubject', '?3'),
                    $queryBuilder->expr()->isNull('g.EntityRemove'),
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->isNotNull('t.Date'),
                            $queryBuilder->expr()->gte('t.Date', '?4'),
                            $queryBuilder->expr()->lte('t.Date', '?5'),
                        ),
                        $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->isNotNull('t.FinishDate'),
                            $queryBuilder->expr()->gte('t.FinishDate', '?4'),
                            $queryBuilder->expr()->lte('t.FinishDate', '?5'),
                        ),
                        $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->isNotNull('g.Date'),
                            $queryBuilder->expr()->gte('g.Date', '?4'),
                            $queryBuilder->expr()->lte('g.Date', '?5'),
                        ),
                    )
                ),
            )
            ->setParameter(1, $tblPerson->getId())
            ->setParameter(2, $tblYear->getId())
            ->setParameter(3, $tblSubject->getId())
            ->setParameter(4, $fromDate)
            ->setParameter(5, $toDate)
            ->orderBy('t.Date', 'ASC')
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblSubject $tblSubject
     * @param DateTime $toDate
     *
     * @return TblTestGrade[]|false
     */
    public function getTestGradeListToDateTimeByPersonAndSubject(TblPerson $tblPerson, TblSubject $tblSubject, DateTime $toDate)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('g')
            ->from(__NAMESPACE__ . '\Entity\TblTestGrade', 'g')
            ->join(__NAMESPACE__ . '\Entity\TblTest', 't')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('g.tblGraduationTest', 't.Id'),
                    $queryBuilder->expr()->eq('g.serviceTblPerson', '?1'),
                    $queryBuilder->expr()->eq('t.serviceTblSubject', '?2'),
                    $queryBuilder->expr()->isNull('g.EntityRemove'),
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->isNotNull('t.Date'),
                            $queryBuilder->expr()->lte('t.Date', '?3'),
                        ),
                        $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->isNotNull('t.FinishDate'),
                            $queryBuilder->expr()->lte('t.FinishDate', '?3'),
                        ),
                        $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->isNotNull('g.Date'),
                            $queryBuilder->expr()->lte('g.Date', '?3'),
                        ),
                    )
                ),
            )
            ->setParameter(1, $tblPerson->getId())
            ->setParameter(2, $tblSubject->getId())
            ->setParameter(3, $toDate)
            ->orderBy('t.Date', 'ASC')
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblTest $tblTest
     *
     * @return false|TblTestGrade[]
     */
    public function getTestGradeListByTest(TblTest $tblTest)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblTestGrade', array(
            TblTestGrade::ATTR_TBL_TEST => $tblTest->getId()
        ));
    }

    /**
     * @param TblTest $tblTest
     * @param TblPerson $tblPerson
     *
     * @return false|TblTestGrade
     */
    public function getTestGradeByTestAndPerson(TblTest $tblTest, TblPerson $tblPerson)
    {
        return $this->getForceEntityBy(__METHOD__, $this->getEntityManager(), 'TblTestGrade', array(
            TblTestGrade::ATTR_TBL_TEST => $tblTest->getId(),
            TblTestGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return integer
     */
    public function getCountPersonTestGrades(TblPerson $tblPerson): int
    {
        if (($list = $this->getForceEntityListBy(__METHOD__, $this->getEntityManager(), 'TblTestGrade', array(
            TblTestGrade::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        )))) {
            return count($list);
        }

        return 0;
    }
}