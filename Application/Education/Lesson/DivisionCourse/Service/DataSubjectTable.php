<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTable;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTableLink;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer as GatekeeperConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\System\Protocol\Protocol;

abstract class DataSubjectTable extends DataMigrate
{
    protected function setupDatabaseContentForSubjectTable()
    {
        if (GatekeeperConsumer::useService()->getConsumerTypeFromServerHost() == TblConsumer::TYPE_SACHSEN) {
            if (($tblSchoolTypePrimary = Type::useService()->getTypeByShortName('GS'))
                && !$this->getSubjectTableListBy($tblSchoolTypePrimary)
            ) {
                $this->setSachsenGsLevel1($tblSchoolTypePrimary);
                $this->setSachsenGsLevel2($tblSchoolTypePrimary);
                $this->setSachsenGsLevel3($tblSchoolTypePrimary);
                $this->setSachsenGsLevel4($tblSchoolTypePrimary);
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
        }

        // todo Berlin

        // todo berufsbildende Schulen in Sachsen
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
     * @param TblSubjectTable $tblSubjectTable
     *
     * @return TblSubjectTable
     */
    public function createSubjectTable(TblSubjectTable $tblSubjectTable): TblSubjectTable {
        $Manager = $this->getEntityManager();

        $Entity = $Manager->getEntity('TblSubjectTable')->findOneBy(array(
            TblSubjectTable::ATTR_SERVICE_TBL_SCHOOL_TYPE => $tblSubjectTable->getServiceTblSchoolType(),
            TblSubjectTable::ATTR_LEVEL => $tblSubjectTable->getLevel(),
            TblSubjectTable::ATTR_SERVICE_TBL_SUBJECT => $tblSubjectTable->getServiceTblSubject()
        ));

        if (null === $Entity) {
            $Entity = $tblSubjectTable;
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
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
     *
     * @return false|TblSubjectTable
     */
    public function setSubjectTable(TblType $tblSchoolType, int $level, ?string $subjectAcronym, string $typeName, int $ranking, ?int $hoursPerWeek,
        string $studentMetaIdentifier = '', bool $hasGrading = true)
    {
        $tblSubject = false;
        if ($subjectAcronym === null || ($tblSubject = Subject::useService()->getSubjectByVariantAcronym($subjectAcronym))) {
            $tblSubjectTable = TblSubjectTable::withParameter($tblSchoolType, $level, $tblSubject ?: null, $typeName, $ranking, $hoursPerWeek,
                $studentMetaIdentifier, $hasGrading);
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
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'EN', 'Pflichtbereich', $ranking++, 2, '', false);
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
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', $ranking++, 2, 'FS_2');
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
        // TC
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'INF', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', $ranking++, 3, 'FS_2');
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
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', $ranking++, 3, 'FS_2');
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
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', $ranking++, 3, 'FS_2');
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

        // WTH
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

        // TC
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'INF', 'Pflichtbereich', $ranking++, 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', $ranking++, 3, 'FS_2');
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
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', $ranking++, 3, 'FS_2');
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
        // 3. FS
    }

    private function setSachsenGyLevel7(TblType $tblSchoolTypeGy)
    {
        $level = 7;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'EN', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', $ranking++, 4, 'FS_2');
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
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', $ranking++, 3, 'FS_3'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
    }

    private function setSachsenGyLevel8(TblType $tblSchoolTypeGy)
    {
        $level = 8;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'EN', 'Pflichtbereich', $ranking++, 3);
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', $ranking++, 3, 'FS_2');
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
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', $ranking++, 3, 'FS_3'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
    }

    private function setSachsenGyLevel9(TblType $tblSchoolTypeGy)
    {
        $level = 9;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'EN', 'Pflichtbereich', $ranking++, 3);
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', $ranking++, 3, 'FS_2');
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
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', $ranking++, 3, 'FS_3'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
    }

    private function setSachsenGyLevel10(TblType $tblSchoolTypeGy)
    {
        $level = 10;
        $ranking = 1;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', $ranking++, 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'EN', 'Pflichtbereich', $ranking++, 3);
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', $ranking++, 3, 'FS_2');
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
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', $ranking++, 3, 'FS_3'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
    }
}