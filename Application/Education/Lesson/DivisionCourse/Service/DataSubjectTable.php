<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service;

use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeText;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTable;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTableLink;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\System\Protocol\Protocol;

abstract class DataSubjectTable extends DataStudentSubject
{
    protected function setupDatabaseContentForSubjectTable()
    {
        if (GatekeeperConsumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_SACHSEN)) {
            if (($tblSchoolTypePrimary = Type::useService()->getTypeByShortName('GS'))) {
                if (!$this->getSubjectTableListBy($tblSchoolTypePrimary)) {
                    $this->setSachsenGsLevel1($tblSchoolTypePrimary);
                    $this->setSachsenGsLevel2($tblSchoolTypePrimary);
                    $this->setSachsenGsLevel3($tblSchoolTypePrimary);
                    $this->setSachsenGsLevel4($tblSchoolTypePrimary);
                } else {
                    if (($tblSubject = Subject::useService()->getSubjectByVariantAcronym('EN'))
                        && ($tblSubjectTable = $this->getSubjectTableBy($tblSchoolTypePrimary, 3, $tblSubject))
                    ) {
                        $this->updateSubjectTable(
                            $tblSubjectTable, $tblSubjectTable->getLevel(), $tblSubjectTable->getTypeName(), $tblSubject,
                            $tblSubjectTable->getStudentMetaIdentifier(), $tblSubjectTable->getHoursPerWeek(), $tblSubjectTable->getHasGrading(),
                            Grade::useService()->getGradeTextByName('teilgenommen') ?: null
                        );
                    }
                }
            }

            if (($tblSchoolTypeSecondary = Type::useService()->getTypeByShortName('OS'))
                && !$this->getSubjectTableListBy($tblSchoolTypeSecondary)
            ) {
                $this->setSachsenOsLevel5($tblSchoolTypeSecondary);
                $this->setSachsenOsLevel6($tblSchoolTypeSecondary);
                $this->setSachsenOsLevel7($tblSchoolTypeSecondary);
                $this->setSachsenOsLevel8($tblSchoolTypeSecondary);
                $this->setSachsenOsLevel9($tblSchoolTypeSecondary);
                $this->setSachsenOsLevel10($tblSchoolTypeSecondary);
            }

            if (($tblSchoolTypeGy = Type::useService()->getTypeByShortName('Gy'))
                && !$this->getSubjectTableListBy($tblSchoolTypeGy)
            ) {
                $this->setSachsenGyLevel5($tblSchoolTypeGy);
                $this->setSachsenGyLevel6($tblSchoolTypeGy);
                $this->setSachsenGyLevel7($tblSchoolTypeGy);
                $this->setSachsenGyLevel8($tblSchoolTypeGy);
                $this->setSachsenGyLevel9($tblSchoolTypeGy);
                $this->setSachsenGyLevel10($tblSchoolTypeGy);
            }
        } elseif (GatekeeperConsumer::useService()->getConsumerBySessionIsConsumerType(TblConsumer::TYPE_BERLIN)) {
            if (($tblSchoolTypePrimary = Type::useService()->getTypeByShortName('GS'))
                && !$this->getSubjectTableListBy($tblSchoolTypePrimary)
            ) {
                $this->setBerlinGsLevel1($tblSchoolTypePrimary);
                $this->setBerlinGsLevel2($tblSchoolTypePrimary);
                $this->setBerlinGsLevel3($tblSchoolTypePrimary);
                $this->setBerlinGsLevel4($tblSchoolTypePrimary);
                $this->setBerlinGsLevel5($tblSchoolTypePrimary);
                $this->setBerlinGsLevel6($tblSchoolTypePrimary);
            }

            if (($tblSchoolTypeSecondary = Type::useService()->getTypeByShortName('ISS'))
                && !$this->getSubjectTableListBy($tblSchoolTypeSecondary)
            ) {
                $this->setBerlinIssLevel7($tblSchoolTypeSecondary);
                $this->setBerlinIssLevel8($tblSchoolTypeSecondary);
                $this->setBerlinIssLevel9($tblSchoolTypeSecondary);
                $this->setBerlinIssLevel10($tblSchoolTypeSecondary);
            }

            if (($tblSchoolTypeGy = Type::useService()->getTypeByShortName('Gy'))
                && !$this->getSubjectTableListBy($tblSchoolTypeGy)
            ) {
                $this->setBerlinGyLevel7($tblSchoolTypeGy);
                $this->setBerlinGyLevel8($tblSchoolTypeGy);
                $this->setBerlinGyLevel9($tblSchoolTypeGy);
                $this->setBerlinGyLevel10($tblSchoolTypeGy);
            }
        }
    }

    /**
     * @param $Id
     *
     * @return false|TblSubjectTable
     */
    public function getSubjectTableById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblSubjectTable', $Id);
    }

    /**
     * @param TblType $tblSchoolType
     * @param int $level
     * @param TblSubject $tblSubject
     *
     * @return false|TblSubjectTable
     */
    public function getSubjectTableBy(TblType $tblSchoolType, int $level, TblSubject $tblSubject)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblSubjectTable', array(
            TblSubjectTable::ATTR_SERVICE_TBL_SCHOOL_TYPE => $tblSchoolType->getId(),
            TblSubjectTable::ATTR_LEVEL => $level,
            TblSubjectTable::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId()
        ));
    }

    /**
     * @param TblType $tblSchoolType
     * @param int $level
     * @param string $studentMetaIdentifier
     *
     * @return false|TblSubjectTable
     */
    public function getSubjectTableByStudentMetaIdentifier(TblType $tblSchoolType, int $level, string $studentMetaIdentifier)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblSubjectTable', array(
            TblSubjectTable::ATTR_SERVICE_TBL_SCHOOL_TYPE => $tblSchoolType->getId(),
            TblSubjectTable::ATTR_LEVEL => $level,
            TblSubjectTable::ATTR_STUDENT_META_IDENTIFIER => $studentMetaIdentifier
        ));
    }

    /**
     * @param TblType $tblSchoolType
     * @param int|null $level
     *
     * @return false|TblSubjectTable[]
     */
    public function getSubjectTableListBy(TblType $tblSchoolType, ?int $level = null)
    {
        $parameters[TblSubjectTable::ATTR_SERVICE_TBL_SCHOOL_TYPE] = $tblSchoolType->getId();
        if ($level !== null) {
            $parameters[TblSubjectTable::ATTR_LEVEL] = $level;
        }
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblSubjectTable', $parameters,
            array(TblSubjectTable::ATTR_LEVEL => self::ORDER_ASC, TblSubjectTable::ATTR_RANKING => self::ORDER_ASC));
    }

    /**
     * @param TblType $tblSchoolType
     * @param TblSubject|null $tblSubject
     * @param $studentMetaIdentifier
     *
     * @return int
     */
    public function getSubjectTableRankingForNewSubjectTable(TblType $tblSchoolType, ?TblSubject $tblSubject, $studentMetaIdentifier): int
    {
        if ($tblSubject) {
            /** @var TblSubjectTable $tblSubjectTable */
            if (($tblSubjectTable = $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblSubjectTable', array(
                TblSubjectTable::ATTR_SERVICE_TBL_SCHOOL_TYPE => $tblSchoolType->getId(),
                TblSubjectTable::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId()
            )))) {
                return $tblSubjectTable->getRanking();
            }
        } elseif ($studentMetaIdentifier) {
            /** @var TblSubjectTable $tblSubjectTable */
            if (($tblSubjectTable = $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblSubjectTable', array(
                TblSubjectTable::ATTR_SERVICE_TBL_SCHOOL_TYPE => $tblSchoolType->getId(),
                TblSubjectTable::ATTR_STUDENT_META_IDENTIFIER => $studentMetaIdentifier
            )))) {
                return $tblSubjectTable->getRanking();
            }
        }

        return $this->getNextRankingForNewSubjectTable($tblSchoolType);
    }

    /**
     * @param $Id
     *
     * @return false|TblSubjectTableLink
     */
    public function getSubjectTableLinkById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblSubjectTableLink', $Id);
    }

    /**
     * @param TblType $tblSchoolType
     *
     * @return false|TblSubjectTableLink
     */
    public function getSubjectTableLinkListBySchoolType(TblType $tblSchoolType)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('l')
            ->from(__NAMESPACE__ . '\Entity\TblSubjectTable', 's')
            ->join(__NAMESPACE__ . '\Entity\TblSubjectTableLink', 'l')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('s.Id', 'l.tblLessonSubjectTable'),
                    $queryBuilder->expr()->eq('s.serviceTblSchoolType', '?1')
                ),
            )
            ->setParameter(1, $tblSchoolType->getId())
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblSubjectTable $tblSubjectTable
     *
     * @return false|TblSubjectTableLink
     */
    public function getSubjectTableLinkBySubjectTable(TblSubjectTable $tblSubjectTable)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblSubjectTableLink', array(TblSubjectTableLink::ATTR_TBL_SUBJECT_TABLE => $tblSubjectTable->getId()));
    }

    /**
     * @param $LinkId
     *
     * @return false|TblSubjectTableLink[]
     */
    public function getSubjectTableLinkListByLinkId($LinkId)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblSubjectTableLink', array(TblSubjectTableLink::ATTR_TBL_LINK_ID => $LinkId));
    }

    /**
     * @param TblSubjectTable $tblSubjectTable
     *
     * @return TblSubjectTable
     */
    public function createSubjectTable(TblSubjectTable $tblSubjectTable): TblSubjectTable
    {
        $Manager = $this->getEntityManager();

        $Entity = $Manager->getEntity('TblSubjectTable')->findOneBy(array(
            TblSubjectTable::ATTR_SERVICE_TBL_SCHOOL_TYPE => $tblSubjectTable->getServiceTblSchoolType() ? $tblSubjectTable->getServiceTblSchoolType()->getId() : '',
            TblSubjectTable::ATTR_LEVEL => $tblSubjectTable->getLevel(),
            TblSubjectTable::ATTR_SERVICE_TBL_SUBJECT => $tblSubjectTable->getServiceTblSubject() ? $tblSubjectTable->getServiceTblSubject()->getId() : '',
            TblSubjectTable::ATTR_STUDENT_META_IDENTIFIER => $tblSubjectTable->getStudentMetaIdentifier()
        ));

        if (null === $Entity) {
            $Entity = $tblSubjectTable;
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSubjectTable $tblSubjectTable
     * @param int $level
     * @param string $typeName
     * @param TblSubject|null $tblSubject
     * @param string $studentMetaIdentifier
     * @param int|null $hoursPerWeek
     * @param bool $hasGrading
     * @param TblGradeText|null $tblGradeText
     *
     * @return bool
     */
    public function updateSubjectTable(TblSubjectTable $tblSubjectTable, int $level, string $typeName, ?TblSubject $tblSubject, string $studentMetaIdentifier,
        ?int $hoursPerWeek, bool $hasGrading, ?TblGradeText $tblGradeText): bool
    {
        $Manager = $this->getEntityManager();
        /** @var TblSubjectTable $Entity */
        $Entity = $Manager->getEntityById('TblSubjectTable', $tblSubjectTable->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setLevel($level);
            $Entity->setTypeName($typeName);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setStudentMetaIdentifier($studentMetaIdentifier);
            $Entity->setHoursPerWeek($hoursPerWeek);
            $Entity->setHasGrading($hasGrading);
            $Entity->setServiceTblGradeText($tblGradeText);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblSubjectTable $tblSubjectTable
     *
     * @return bool
     */
    public function destroySubjectTable(TblSubjectTable $tblSubjectTable): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSubjectTable $Entity */
        $Entity = $Manager->getEntityById('TblSubjectTable', $tblSubjectTable->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblType $tblSchoolType
     * @param int $level
     * @param string|null $subjectAcronym
     * @param string $typeName
     * @param int $ranking
     * @param int|null $hoursPerWeek
     * @param string $studentMetaIdentifier
     * @param bool $hasGrading
     * @param string|null $gradeTextName
     *
     * @return false|TblSubjectTable
     */
    public function setSubjectTable(TblType $tblSchoolType, int $level, ?string $subjectAcronym, string $typeName, int $ranking, ?int $hoursPerWeek,
        string $studentMetaIdentifier = '', bool $hasGrading = true, ?string $gradeTextName = null)
    {
        $tblSubject = false;
        if ($subjectAcronym === null || ($tblSubject = Subject::useService()->getSubjectByVariantAcronym($subjectAcronym))) {
            $tblGradeText = $gradeTextName ? Grade::useService()->getGradeTextByName($gradeTextName) : null;
            $tblSubjectTable = TblSubjectTable::withParameter($tblSchoolType, $level, $tblSubject ?: null, $typeName, $ranking, $hoursPerWeek,
                $studentMetaIdentifier, $hasGrading, $tblGradeText ?: null);
            $this->createSubjectTable($tblSubjectTable);

            return $tblSubjectTable;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getNextLinkId()
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('Max(t.LinkId) as MaxLinkId')
            ->from(__NAMESPACE__ . '\Entity\TblSubjectTableLink', 't')
            ->orderBy('t.LinkId', 'DESC')
            ->getQuery();

        $result = $query->getResult();
        if (is_array($result)) {
            $item = current($result);
            return intval($item['MaxLinkId']) + 1;
        }

        return 1;
    }

    /**
     * @param TblType $tblSchoolType
     *
     * @return int
     */
    public function getNextRankingForNewSubjectTable(TblType $tblSchoolType): int
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('Max(t.Ranking) as MaxRanking')
            ->from(__NAMESPACE__ . '\Entity\TblSubjectTable', 't')
            ->where($queryBuilder->expr()->eq('t.serviceTblSchoolType', '?1'))
            ->setParameter(1, $tblSchoolType->getId())
            ->orderBy('t.Ranking', 'DESC')
            ->getQuery();

        $result = $query->getResult();
        if (is_array($result)) {
            $item = current($result);
            return intval($item['MaxRanking']) + 1;
        }

        return 1;
    }

    /**
     * @param int $linkId
     * @param int $minCount
     * @param TblSubjectTable $tblSubjectTable
     *
     * @return TblSubjectTableLink
     */
    public function createSubjectTableLink(int $linkId, int $minCount, TblSubjectTable $tblSubjectTable): TblSubjectTableLink {
        $Manager = $this->getEntityManager();

        $Entity = $Manager->getEntity('TblSubjectTableLink')->findOneBy(array(
            TblSubjectTableLink::ATTR_TBL_SUBJECT_TABLE => $tblSubjectTable->getId()
        ));

        if (null === $Entity) {
            $Entity = new TblSubjectTableLink();
            $Entity->setLinkId($linkId);
            $Entity->setMinCount($minCount);
            $Entity->setTblSubjectTable($tblSubjectTable);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSubjectTableLink $tblSubjectTableLink
     * @param $minCount
     *
     * @return bool
     */
    public function updateSubjectTableLink(TblSubjectTableLink $tblSubjectTableLink, $minCount): bool
    {
        $Manager = $this->getEntityManager();
        /** @var TblSubjectTableLink $Entity */
        $Entity = $Manager->getEntityById('TblSubjectTableLink', $tblSubjectTableLink->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setMinCount($minCount);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblSubjectTableLink $tblSubjectTableLink
     *
     * @return bool
     */
    public function destroySubjectTableLink(TblSubjectTableLink $tblSubjectTableLink): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSubjectTable $Entity */
        $Entity = $Manager->getEntityById('TblSubjectTableLink', $tblSubjectTableLink->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    private function setSachsenGsLevel1(TblType $tblSchoolTypePrimary)
    {
        $level = 1;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'DE', 'Pflichtbereich', $ranking++, 7, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SU', 'Pflichtbereich', $ranking++, 2, '', false);
        $ranking++; // EN
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MA', 'Pflichtbereich', $ranking++, 5, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SPO', 'Pflichtbereich', $ranking++, 3, '', false);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'RE/e', 'Wahlpflichtbereich', $ranking++, 1, 'RELIGION', false))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'RE/k', 'Wahlpflichtbereich', $ranking++, 1, 'RELIGION', false))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'ETH', 'Wahlpflichtbereich', $ranking++, 1, 'RELIGION', false))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'KU', 'Pflichtbereich', $ranking++, 1, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MU', 'Pflichtbereich', $ranking++, 1, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'WE', 'Pflichtbereich', $ranking++, 1, '', false);
    }

    private function setSachsenGsLevel2(TblType $tblSchoolTypePrimary)
    {
        $level = 2;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'DE', 'Pflichtbereich', $ranking++, 6);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SU', 'Pflichtbereich', $ranking++, 3);
        $ranking++; // EN
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MA', 'Pflichtbereich', $ranking++, 5);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SPO', 'Pflichtbereich', $ranking++, 3, '', false);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'RE/e', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION', false))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'RE/k', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION', false))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'ETH', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION', false))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'KU', 'Pflichtbereich', $ranking++, 1, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MU', 'Pflichtbereich', $ranking++, 1, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'WE', 'Pflichtbereich', $ranking++, 1, '', false);
    }

    private function setSachsenGsLevel3(TblType $tblSchoolTypePrimary)
    {
        $level = 3;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'DE', 'Pflichtbereich', $ranking++, 7);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SU', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'EN', 'Pflichtbereich', $ranking++, 2, '', false, 'teilgenommen');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MA', 'Pflichtbereich', $ranking++, 5);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SPO', 'Pflichtbereich', $ranking++, 3);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'RE/e', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'RE/k', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'ETH', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'KU', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MU', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'WE', 'Pflichtbereich', $ranking++, 1);
    }

    private function setSachsenGsLevel4(TblType $tblSchoolTypePrimary)
    {
        $level = 4;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'DE', 'Pflichtbereich', $ranking++, 6);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SU', 'Pflichtbereich', $ranking++, 3);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'EN', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MA', 'Pflichtbereich', $ranking++, 5);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SPO', 'Pflichtbereich', $ranking++, 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'RE/e', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'RE/k', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'ETH', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'KU', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MU', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'WE', 'Pflichtbereich', $ranking++, 1);
    }

    private function setSachsenOsLevel5(TblType $tblSchoolTypeSecondary)
    {
        $level = 5;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'DE', 'Pflichtbereich', $ranking++, 5);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'EN', 'Pflichtbereich', $ranking++, 5);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'BIO', 'Pflichtbereich', $ranking++, 2);
        $ranking++; // CH
        $ranking++; // PH
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GE', 'Pflichtbereich', $ranking++, 1);
        $ranking++; // GK
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GEO', 'Pflichtbereich', $ranking++, 2);
        $ranking++; // WTH
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'SPO', 'Pflichtbereich', $ranking++, 3);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/e', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/k', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'ETH', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'KU', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MU', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'TC', 'Pflichtbereich', $ranking++, 2);
        // INF
        // 2. FS
    }

    private function setSachsenOsLevel6(TblType $tblSchoolTypeSecondary)
    {
        $level = 6;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'DE', 'Pflichtbereich', $ranking++, 5);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'EN', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MA', 'Pflichtbereich', $ranking++, 5);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'BIO', 'Pflichtbereich', $ranking++, 2);
        $ranking++; // CH
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'PH', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GE', 'Pflichtbereich', $ranking++, 2);
        $ranking++; // GK
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GEO', 'Pflichtbereich', $ranking++, 2);
        $ranking++; // WTH
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'SPO', 'Pflichtbereich', $ranking++, 3);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/e', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/k', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'ETH', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'KU', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MU', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'TC', 'Pflichtbereich', $ranking++, 1);
        $ranking++; // INF
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', $ranking++, 2, 'FOREIGN_LANGUAGE_2');
    }

    private function setSachsenOsLevel7(TblType $tblSchoolTypeSecondary)
    {
        $level = 7;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'EN', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'BIO', 'Pflichtbereich', $ranking++, 1);
        $ranking++; // CH
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'PH', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GE', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GK', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GEO', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'WTH', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'SPO', 'Pflichtbereich', $ranking++, 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/e', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/k', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'ETH', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'KU', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MU', 'Pflichtbereich', $ranking++, 1);
        $ranking++;// TC
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'INF', 'Pflichtbereich', $ranking++, 1);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_2'))) {
            $this->createSubjectTableLink($linkId, 0, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', $ranking++, 2, 'ORIENTATION'))) {
            $this->createSubjectTableLink($linkId, 0, $tblSubjectTable);
        }
    }

    private function setSachsenOsLevel8(TblType $tblSchoolTypeSecondary)
    {
        $level = 8;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'EN', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'BIO', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'CH', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'PH', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GE', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GK', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GEO', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'WTH', 'Pflichtbereich', $ranking++, 3);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'SPO', 'Pflichtbereich', $ranking++, 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/e', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/k', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'ETH', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'KU', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MU', 'Pflichtbereich', $ranking++, 1);
        $ranking++; // TC
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'INF', 'Pflichtbereich', $ranking++, 1);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_2'))) {
            $this->createSubjectTableLink($linkId, 0, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', $ranking++, 2, 'ORIENTATION'))) {
            $this->createSubjectTableLink($linkId, 0, $tblSubjectTable);
        }
    }

    private function setSachsenOsLevel9(TblType $tblSchoolTypeSecondary)
    {
        $level = 9;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'EN', 'Pflichtbereich', $ranking++, 3);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'BIO', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'CH', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'PH', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GE', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GK', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GEO', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'WTH', 'Pflichtbereich', $ranking++, 3);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'SPO', 'Pflichtbereich', $ranking++, 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/e', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/k', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'ETH', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'KU', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MU', 'Pflichtbereich', $ranking++, 1);
        $ranking++; // TC
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'INF', 'Pflichtbereich', $ranking++, 1);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_2'))) {
            $this->createSubjectTableLink($linkId, 0, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', $ranking++, 2, 'ORIENTATION'))) {
            $this->createSubjectTableLink($linkId, 0, $tblSubjectTable);
        }
    }

    private function setSachsenOsLevel10(TblType $tblSchoolTypeSecondary)
    {
        $level = 10;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'EN', 'Pflichtbereich', $ranking++, 3);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'BIO', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'CH', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'PH', 'Pflichtbereich', $ranking++, 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GE', 'Wahlpflichtbereich', $ranking++, 2, 'ELECTIVE'))) {
            $this->createSubjectTableLink($linkId, 2, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GK', 'Wahlpflichtbereich', $ranking++, 2, 'ELECTIVE'))) {
            $this->createSubjectTableLink($linkId, 2, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GEO', 'Wahlpflichtbereich', $ranking++, 2, 'ELECTIVE'))) {
            $this->createSubjectTableLink($linkId, 2, $tblSubjectTable);
        }

        $ranking++;// WTH
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'SPO', 'Pflichtbereich', $ranking++, 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/e', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/k', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'ETH', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'KU', 'Wahlpflichtbereich', $ranking++, 2, 'ELECTIVE'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MU', 'Wahlpflichtbereich', $ranking++, 2, 'ELECTIVE'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $ranking++;// TC
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'INF', 'Pflichtbereich', $ranking++, 1);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_2'))) {
            $this->createSubjectTableLink($linkId, 0, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', $ranking++, 2, 'ORIENTATION'))) {
            $this->createSubjectTableLink($linkId, 0, $tblSubjectTable);
        }
    }

    private function setSachsenGyLevel5(TblType $tblSchoolTypeGy)
    {
        $level = 5;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', $ranking++, 5);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'EN', 'Pflichtbereich', $ranking++, 5);
        $ranking++; // 2. FS
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'BIO', 'Pflichtbereich', $ranking++, 2);
        $ranking++; // CH
        $ranking++; // PH
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GE', 'Pflichtbereich', $ranking++, 1);
        $ranking++; // G/R/W
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GEO', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'SPO', 'Pflichtbereich', $ranking++, 3);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/e', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/k', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'ETH', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeGy, $level, 'KU', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MU', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'TC', 'Pflichtbereich', $ranking++, 1);
        // INF

        // Profil
        // 3. FS
    }

    private function setSachsenGyLevel6(TblType $tblSchoolTypeGy)
    {
        $level = 6;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'EN', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_2');
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'BIO', 'Pflichtbereich', $ranking++, 2);
        $ranking++; // CH
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'PH', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GE', 'Pflichtbereich', $ranking++, 2);
        $ranking++; // G/R/W
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GEO', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'SPO', 'Pflichtbereich', $ranking++, 3);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/e', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/k', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'ETH', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeGy, $level, 'KU', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MU', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'TC', 'Pflichtbereich', $ranking++, 1);
        // INF

        // Profil
        // 3. FOREIGN_LANGUAGE
    }

    private function setSachsenGyLevel7(TblType $tblSchoolTypeGy)
    {
        $level = 7;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'EN', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', $ranking++, 4, 'FOREIGN_LANGUAGE_2');
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'BIO', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'CH', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'PH', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GE', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'G/R/W', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GEO', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'SPO', 'Pflichtbereich', $ranking++, 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/e', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/k', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'ETH', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeGy, $level, 'KU', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MU', 'Pflichtbereich', $ranking++, 1);
        $ranking++; // TC
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'INF', 'Pflichtbereich', $ranking++, 1);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', $ranking++, 2, 'PROFILE'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_3'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
    }

    private function setSachsenGyLevel8(TblType $tblSchoolTypeGy)
    {
        $level = 8;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'EN', 'Pflichtbereich', $ranking++, 3);
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_2');
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'BIO', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'CH', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'PH', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GE', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'G/R/W', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GEO', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'SPO', 'Pflichtbereich', $ranking++, 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/e', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/k', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'ETH', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeGy, $level, 'KU', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MU', 'Pflichtbereich', $ranking++, 1);
        $ranking++; // TC
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'INF', 'Pflichtbereich', $ranking++, 1);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', $ranking++, 2, 'PROFILE'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_3'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
    }

    private function setSachsenGyLevel9(TblType $tblSchoolTypeGy)
    {
        $level = 9;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'EN', 'Pflichtbereich', $ranking++, 3);
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_2');
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'BIO', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'CH', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'PH', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GE', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'G/R/W', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GEO', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'SPO', 'Pflichtbereich', $ranking++, 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/e', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/k', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'ETH', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeGy, $level, 'KU', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MU', 'Pflichtbereich', $ranking++, 1);
        $ranking++; // TC
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'INF', 'Pflichtbereich', $ranking++, 1);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', $ranking++, 2, 'PROFILE'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_3'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
    }

    private function setSachsenGyLevel10(TblType $tblSchoolTypeGy)
    {
        $level = 10;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'EN', 'Pflichtbereich', $ranking++, 3);
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_2');
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'BIO', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'CH', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'PH', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GE', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'G/R/W', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GEO', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'SPO', 'Pflichtbereich', $ranking++, 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/e', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/k', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'ETH', 'Wahlpflichtbereich', $ranking++, 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeGy, $level, 'KU', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MU', 'Pflichtbereich', $ranking++, 1);
        $ranking++; // TC
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'INF', 'Pflichtbereich', $ranking++, 1);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', $ranking++, 2, 'PROFILE'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_3'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
    }

    private function setBerlinGsLevel1(TblType $tblSchoolTypePrimary)
    {
        $level = 1;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'DE', 'Pflichtbereich', $ranking++, 7, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MA', 'Pflichtbereich', $ranking++, 5, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SU', 'Pflichtbereich', $ranking++, 2, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'KU', 'Pflichtbereich', $ranking++, 2, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MU', 'Pflichtbereich', $ranking++, 2, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SPO', 'Pflichtbereich', $ranking++, 3, '', false);
        // 1. FS
    }

    private function setBerlinGsLevel2(TblType $tblSchoolTypePrimary)
    {
        $level = 2;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'DE', 'Pflichtbereich', $ranking++, 8, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MA', 'Pflichtbereich', $ranking++, 5, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SU', 'Pflichtbereich', $ranking++, 2, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'KU', 'Pflichtbereich', $ranking++, 2, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MU', 'Pflichtbereich', $ranking++, 2, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SPO', 'Pflichtbereich', $ranking++, 3, '', false);
        // 1. FS
    }

    private function setBerlinGsLevel3(TblType $tblSchoolTypePrimary)
    {
        $level = 3;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'DE', 'Pflichtbereich', $ranking++, 8, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MA', 'Pflichtbereich', $ranking++, 5, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SU', 'Pflichtbereich', $ranking++, 3, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'KU', 'Pflichtbereich', $ranking++, 2, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MU', 'Pflichtbereich', $ranking++, 2, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SPO', 'Pflichtbereich', $ranking++, 3, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, null, 'Wahlpflichtbereich', $ranking++, 2, 'FOREIGN_LANGUAGE_1');
    }

    private function setBerlinGsLevel4(TblType $tblSchoolTypePrimary)
    {
        $level = 4;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'DE', 'Pflichtbereich', $ranking++, 8, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MA', 'Pflichtbereich', $ranking++, 5, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SU', 'Pflichtbereich', $ranking++, 5, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'KU', 'Pflichtbereich', $ranking++, 2, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MU', 'Pflichtbereich', $ranking++, 2, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SPO', 'Pflichtbereich', $ranking++, 3, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, null, 'Wahlpflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_1');
    }

    private function setBerlinGsLevel5(TblType $tblSchoolTypePrimary)
    {
        $level = 5;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'DE', 'Pflichtbereich', $ranking++, 5, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MA', 'Pflichtbereich', $ranking++, 5, '');
        $ranking++; // SU
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'KU', 'Pflichtbereich', $ranking++, 2, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MU', 'Pflichtbereich', $ranking++, 2, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SPO', 'Pflichtbereich', $ranking++, 3, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, null, 'Wahlpflichtbereich', $ranking++, 4, 'FOREIGN_LANGUAGE_1');
    }

    private function setBerlinGsLevel6(TblType $tblSchoolTypePrimary)
    {
        $level = 6;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'DE', 'Pflichtbereich', $ranking++, 5, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MA', 'Pflichtbereich', $ranking++, 5, '');
        $ranking++; // SU
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'KU', 'Pflichtbereich', $ranking++, 2, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MU', 'Pflichtbereich', $ranking++, 2, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SPO', 'Pflichtbereich', $ranking++, 3, '');
        $this->setSubjectTable($tblSchoolTypePrimary, $level, null, 'Wahlpflichtbereich', $ranking++, 5, 'FOREIGN_LANGUAGE_1');
    }

    private function setBerlinIssLevel7(TblType $tblSchoolSecondary)
    {
        $level = 7;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolSecondary, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolSecondary, $level, null, 'Pflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_1');

        // Gesamt 3 Wochenstunden
        $this->setSubjectTable($tblSchoolSecondary, $level, 'BIO', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'PH', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'CH', 'Pflichtbereich', $ranking++, null);

        // Gesamt 8 Wochenstunden
        $this->setSubjectTable($tblSchoolSecondary, $level, 'GE', 'Pflichtbereich', $ranking++, null);
        $ranking++;
        $this->setSubjectTable($tblSchoolSecondary, $level, 'GEO', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'ETH', 'Pflichtbereich', $ranking++, null); // 'RELIGION'

        // Gesamt 2 Wochenstunden
        $this->setSubjectTable($tblSchoolSecondary, $level, 'MU', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'KU', 'Pflichtbereich', $ranking++, null);

        $this->setSubjectTable($tblSchoolSecondary, $level, 'SPO', 'Pflichtbereich', $ranking++, 3);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'WAT', 'Pflichtbereich', $ranking++, 2);

        $this->setSubjectTable($tblSchoolSecondary, $level, null, 'Wahlpflichtbereich', $ranking++, 3, 'PROFILE');
    }

    private function setBerlinIssLevel8(TblType $tblSchoolSecondary)
    {
        $level = 8;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolSecondary, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolSecondary, $level, null, 'Pflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_1');

        // Gesamt 3 Wochenstunden
        $this->setSubjectTable($tblSchoolSecondary, $level, 'BIO', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'PH', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'CH', 'Pflichtbereich', $ranking++, null);

        // Gesamt 8 Wochenstunden
        $this->setSubjectTable($tblSchoolSecondary, $level, 'GE', 'Pflichtbereich', $ranking++, null);
        $ranking++;
        $this->setSubjectTable($tblSchoolSecondary, $level, 'GEO', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'ETH', 'Pflichtbereich', $ranking++, null); // 'RELIGION'

        // Gesamt 2 Wochenstunden
        $this->setSubjectTable($tblSchoolSecondary, $level, 'MU', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'KU', 'Pflichtbereich', $ranking++, null);

        $this->setSubjectTable($tblSchoolSecondary, $level, 'SPO', 'Pflichtbereich', $ranking++, 3);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'WAT', 'Pflichtbereich', $ranking++, 2);

        $this->setSubjectTable($tblSchoolSecondary, $level, null, 'Wahlpflichtbereich', $ranking++, 3, 'PROFILE');
    }

    private function setBerlinIssLevel9(TblType $tblSchoolSecondary)
    {
        $level = 9;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolSecondary, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolSecondary, $level, null, 'Pflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_1');

        // Gesamt 5 Wochenstunden
        $this->setSubjectTable($tblSchoolSecondary, $level, 'BIO', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'PH', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'CH', 'Pflichtbereich', $ranking++, null);

        // Gesamt 8 Wochenstunden
        $this->setSubjectTable($tblSchoolSecondary, $level, 'GE', 'Pflichtbereich', $ranking++, null);
        $ranking++;
        $this->setSubjectTable($tblSchoolSecondary, $level, 'GEO', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'ETH', 'Pflichtbereich', $ranking++, null); // 'RELIGION'

        // Gesamt 2 Wochenstunden
        $this->setSubjectTable($tblSchoolSecondary, $level, 'MU', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'KU', 'Pflichtbereich', $ranking++, null);

        $this->setSubjectTable($tblSchoolSecondary, $level, 'SPO', 'Pflichtbereich', $ranking++, 3);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'WAT', 'Pflichtbereich', $ranking++, 2);

        $this->setSubjectTable($tblSchoolSecondary, $level, null, 'Wahlpflichtbereich', $ranking++, 3, 'PROFILE');
    }

    private function setBerlinIssLevel10(TblType $tblSchoolSecondary)
    {
        $level = 10;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolSecondary, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolSecondary, $level, null, 'Pflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_1');

        // Gesamt 5 Wochenstunden
        $this->setSubjectTable($tblSchoolSecondary, $level, 'BIO', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'PH', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'CH', 'Pflichtbereich', $ranking++, null);

        // Gesamt 8 Wochenstunden
        $this->setSubjectTable($tblSchoolSecondary, $level, 'GE', 'Pflichtbereich', $ranking++, null);
        $ranking++;
        $this->setSubjectTable($tblSchoolSecondary, $level, 'GEO', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'ETH', 'Pflichtbereich', $ranking++, null); // 'RELIGION'

        // Gesamt 2 Wochenstunden
        $this->setSubjectTable($tblSchoolSecondary, $level, 'MU', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'KU', 'Pflichtbereich', $ranking++, null);

        $this->setSubjectTable($tblSchoolSecondary, $level, 'SPO', 'Pflichtbereich', $ranking++, 3);
        $this->setSubjectTable($tblSchoolSecondary, $level, 'WAT', 'Pflichtbereich', $ranking++, 2);

        $this->setSubjectTable($tblSchoolSecondary, $level, null, 'Wahlpflichtbereich', $ranking++, 3, 'PROFILE');
    }

    private function setBerlinGyLevel7(TblType $tblSchoolTypeGy)
    {
        $level = 7;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_1');
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', $ranking++, 4, 'FOREIGN_LANGUAGE_2');

        // Gesamt 4 Wochenstunden
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'BIO', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'PH', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'CH', 'Pflichtbereich', $ranking++, null);

        // Gesamt 10 Wochenstunden
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GE', 'Pflichtbereich', $ranking++, null);
        $ranking++;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GEO', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'ETH', 'Pflichtbereich', $ranking++, null); // 'RELIGION'

        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MU', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'KU', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'SPO', 'Pflichtbereich', $ranking++, 3);

        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', $ranking++, 2, 'PROFILE');
    }

    private function setBerlinGyLevel8(TblType $tblSchoolTypeGy)
    {
        $level = 8;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_1');
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', $ranking++, 4, 'FOREIGN_LANGUAGE_2');

        // Gesamt 4 Wochenstunden
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'BIO', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'PH', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'CH', 'Pflichtbereich', $ranking++, null);

        // Gesamt 10 Wochenstunden
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GE', 'Pflichtbereich', $ranking++, null);
        $ranking++;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GEO', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'ETH', 'Pflichtbereich', $ranking++, null); // 'RELIGION'

        // Gesamt 3 Wochenstunden
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MU', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'KU', 'Pflichtbereich', $ranking++, null);

        $this->setSubjectTable($tblSchoolTypeGy, $level, 'SPO', 'Pflichtbereich', $ranking++, 3);

        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', $ranking++, 3, 'PROFILE');
    }

    private function setBerlinGyLevel9(TblType $tblSchoolTypeGy)
    {
        $level = 9;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_1');
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_2');

        $this->setSubjectTable($tblSchoolTypeGy, $level, 'BIO', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'PH', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'CH', 'Pflichtbereich', $ranking++, 2);

        // Gesamt 10 Wochenstunden
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GE', 'Pflichtbereich', $ranking++, null);
        $ranking++;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GEO', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'ETH', 'Pflichtbereich', $ranking++, null); // 'RELIGION'

        // Gesamt 2 Wochenstunden
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MU', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'KU', 'Pflichtbereich', $ranking++, null);

        $this->setSubjectTable($tblSchoolTypeGy, $level, 'SPO', 'Pflichtbereich', $ranking++, 3);

        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', $ranking++, 2, 'PROFILE');
    }

    private function setBerlinGyLevel10(TblType $tblSchoolTypeGy)
    {
        $level = 10;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MA', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_1');
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', $ranking++, 3, 'FOREIGN_LANGUAGE_2');

        $this->setSubjectTable($tblSchoolTypeGy, $level, 'BIO', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'PH', 'Pflichtbereich', $ranking++, 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'CH', 'Pflichtbereich', $ranking++, 2);

        // Gesamt 10 Wochenstunden
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GE', 'Pflichtbereich', $ranking++, null);
        $ranking++;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GEO', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'ETH', 'Pflichtbereich', $ranking++, null); // 'RELIGION'

        // Gesamt 2 Wochenstunden
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MU', 'Pflichtbereich', $ranking++, null);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'KU', 'Pflichtbereich', $ranking++, null);

        $this->setSubjectTable($tblSchoolTypeGy, $level, 'SPO', 'Pflichtbereich', $ranking++, 3);

        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', $ranking++, 2, 'PROFILE');
    }
}