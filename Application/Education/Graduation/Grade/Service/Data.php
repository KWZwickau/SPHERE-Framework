<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service;

use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTestGrade;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;

class Data extends DataMigrate
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

        $this->createGradeText('nicht erteilt', 'NOT_GRANTED');
        $this->createGradeText('teilgenommen', 'ATTENDED');
        $this->createGradeText('keine Benotung', 'NO_GRADING');
        $this->createGradeText('befreit', 'LIBERATED');
        $this->createGradeText('&ndash;', 'DASH');
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
     * @param bool $withInActive
     *
     * @return false|TblGradeType[]
     */
    public function getGradeTypeAll(bool $withInActive = false)
    {
        return $withInActive
            ? $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblGradeType')
            : $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblGradeType', array(TblGradeType::ATTR_IS_ACTIVE => false));
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
     * @param $id
     *
     * @return false|TblScoreType
     */
    public function getScoreTypeById($id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblScoreType', $id);
    }

    /**
     * @return false|TblScoreType[]
     */
    public function getScoreTypeAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblScoreType');
    }

    /**
     * @param $id
     *
     * @return false|TblTask
     */
    public function getTaskById($id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblTask', $id);
    }

    /**
     * @param string $name
     * @param string $identifier
     * @param string $pattern
     *
     * @return TblScoreType
     */
    public function createScoreType(string $name, string $identifier, string $pattern): TblScoreType
    {
        $Manager = $this->getEntityManager();
        $identifier = strtoupper($identifier);
        $Entity = $Manager->getEntity('TblScoreType')->findOneBy(array(TblScoreType::ATTR_IDENTIFIER => $identifier));
        if (null === $Entity) {
            $Entity = new TblScoreType();
            $Entity->setName($name);
            $Entity->setIdentifier($identifier);
            $Entity->setPattern($pattern);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param string $name
     * @param string $identifier
     *
     * @return TblGradeText
     */
    public function createGradeText(string $name, string $identifier): TblGradeText
    {
        $Manager = $this->getEntityManager();
        $identifier = strtoupper($identifier);
        $Entity = $Manager->getEntity('TblGradeText')->findOneBy(array(TblGradeText::ATTR_IDENTIFIER => $identifier));
        if (null === $Entity) {
            $Entity = new TblGradeText();
            $Entity->setName($name);
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
}