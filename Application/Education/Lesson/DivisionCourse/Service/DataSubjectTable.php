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

    public function getSubjectTableListBy(TblType $tblSchoolType, ?int $level = null)
    {
        $parameters[TblSubjectTable::ATTR_SERVICE_TBL_SCHOOL_TYPE] = $tblSchoolType->getId();
        if ($level !== null) {
            $parameters[TblSubjectTable::ATTR_LEVEL] = $level;
        }
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblSubjectTable', $parameters);
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
     * @param int|null $hoursPerWeek
     * @param string $studentMetaIdentifier
     * @param bool $hasGrading
     *
     * @return false|TblSubjectTable
     */
    public function setSubjectTable(TblType $tblSchoolType, int $level, ?string $subjectAcronym, string $typeName, ?int $hoursPerWeek,
        string $studentMetaIdentifier = '', bool $hasGrading = true)
    {
        $tblSubject = false;
        if ($subjectAcronym === null || ($tblSubject = Subject::useService()->getSubjectByVariantAcronym($subjectAcronym))) {
            $tblSubjectTable = TblSubjectTable::withParameter($tblSchoolType, $level, $tblSubject ?: null, $typeName, $hoursPerWeek, $studentMetaIdentifier, $hasGrading);
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
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'DE', 'Pflichtbereich', 7, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SU', 'Pflichtbereich', 2, '', false);
        // EN
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MA', 'Pflichtbereich', 5, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SPO', 'Pflichtbereich', 3, '', false);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'RE/e', 'Wahlpflichtbereich', 1, 'RELIGION', false))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'RE/k', 'Wahlpflichtbereich', 1, 'RELIGION', false))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'ETH', 'Wahlpflichtbereich', 1, 'RELIGION', false))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'KU', 'Pflichtbereich', 1, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MU', 'Pflichtbereich', 1, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'WE', 'Pflichtbereich', 1, '', false);
    }

    private function setSachsenGsLevel2(TblType $tblSchoolTypePrimary)
    {
        $level = 2;
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'DE', 'Pflichtbereich', 6);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SU', 'Pflichtbereich', 3);
        // EN
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MA', 'Pflichtbereich', 5);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SPO', 'Pflichtbereich', 3, '', false);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'RE/e', 'Wahlpflichtbereich', 2, 'RELIGION', false))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'RE/k', 'Wahlpflichtbereich', 2, 'RELIGION', false))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'ETH', 'Wahlpflichtbereich', 2, 'RELIGION', false))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'KU', 'Pflichtbereich', 1, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MU', 'Pflichtbereich', 1, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'WE', 'Pflichtbereich', 1, '', false);
    }

    private function setSachsenGsLevel3(TblType $tblSchoolTypePrimary)
    {
        $level = 3;
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'DE', 'Pflichtbereich', 7);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SU', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'EN', 'Pflichtbereich', 2, '', false);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MA', 'Pflichtbereich', 5);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SPO', 'Pflichtbereich', 3);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'RE/e', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'RE/k', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'ETH', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'KU', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MU', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'WE', 'Pflichtbereich', 1);
    }

    private function setSachsenGsLevel4(TblType $tblSchoolTypePrimary)
    {
        $level = 4;
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'DE', 'Pflichtbereich', 6);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SU', 'Pflichtbereich', 3);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'EN', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MA', 'Pflichtbereich', 5);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'SPO', 'Pflichtbereich', 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'RE/e', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'RE/k', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypePrimary, $level, 'ETH', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'KU', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'MU', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypePrimary, $level, 'WE', 'Pflichtbereich', 1);
    }

    private function setSachsenOsLevel5(TblType $tblSchoolTypeSecondary)
    {
        $level = 5;
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'DE', 'Pflichtbereich', 5);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'EN', 'Pflichtbereich', 5);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MA', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'BIO', 'Pflichtbereich', 2);
        // CH
        // PH
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GE', 'Pflichtbereich', 1);
        // GK
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GEO', 'Pflichtbereich', 2);
        // WTH
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'SPO', 'Pflichtbereich', 3);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/e', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/k', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'ETH', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'KU', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MU', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'TC', 'Pflichtbereich', 2);
        // INF
        // 2. FS
    }

    private function setSachsenOsLevel6(TblType $tblSchoolTypeSecondary)
    {
        $level = 6;
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'DE', 'Pflichtbereich', 5);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'EN', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MA', 'Pflichtbereich', 5);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'BIO', 'Pflichtbereich', 2);
        // CH
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'PH', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GE', 'Pflichtbereich', 2);
        // GK
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GEO', 'Pflichtbereich', 2);
        // WTH
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'SPO', 'Pflichtbereich', 3);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/e', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/k', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'ETH', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'KU', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MU', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'TC', 'Pflichtbereich', 1);
        // INF
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', 2, 'FS_2');
    }

    private function setSachsenOsLevel7(TblType $tblSchoolTypeSecondary)
    {
        $level = 7;
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'DE', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'EN', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MA', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'BIO', 'Pflichtbereich', 1);
        // CH
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'PH', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GE', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GK', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GEO', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'WTH', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'SPO', 'Pflichtbereich', 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/e', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/k', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'ETH', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'KU', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MU', 'Pflichtbereich', 1);
        // TC
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'INF', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', 3, 'FS_2');
    }

    private function setSachsenOsLevel8(TblType $tblSchoolTypeSecondary)
    {
        $level = 8;
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'DE', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'EN', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MA', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'BIO', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'CH', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'PH', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GE', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GK', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GEO', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'WTH', 'Pflichtbereich', 3);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'SPO', 'Pflichtbereich', 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/e', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/k', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'ETH', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'KU', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MU', 'Pflichtbereich', 1);
        // TC
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'INF', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', 3, 'FS_2');
    }

    private function setSachsenOsLevel9(TblType $tblSchoolTypeSecondary)
    {
        $level = 9;
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'DE', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'EN', 'Pflichtbereich', 3);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MA', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'BIO', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'CH', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'PH', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GE', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GK', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GEO', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'WTH', 'Pflichtbereich', 3);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'SPO', 'Pflichtbereich', 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/e', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/k', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'ETH', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'KU', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MU', 'Pflichtbereich', 1);
        // TC
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'INF', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', 3, 'FS_2');
    }

    private function setSachsenOsLevel10(TblType $tblSchoolTypeSecondary)
    {
        $level = 10;
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'DE', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'EN', 'Pflichtbereich', 3);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MA', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'BIO', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'CH', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'PH', 'Pflichtbereich', 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GE', 'Wahlpflichtbereich', 2, 'ELECTIVE'))) {
            $this->createSubjectTableLink($linkId, 2, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GK', 'Wahlpflichtbereich', 2, 'ELECTIVE'))) {
            $this->createSubjectTableLink($linkId, 2, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'GEO', 'Wahlpflichtbereich', 2, 'ELECTIVE'))) {
            $this->createSubjectTableLink($linkId, 2, $tblSubjectTable);
        }

        // WTH
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'SPO', 'Pflichtbereich', 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/e', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'RE/k', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'ETH', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'KU', 'Wahlpflichtbereich', 2, 'ELECTIVE'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'MU', 'Wahlpflichtbereich', 2, 'ELECTIVE'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        // TC
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, 'INF', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeSecondary, $level, null, 'Wahlbereich', 3, 'FS_2');
    }

    private function setSachsenGyLevel5(TblType $tblSchoolTypeGy)
    {
        $level = 5;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', 5);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'EN', 'Pflichtbereich', 5);
        // 2. FS
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MA', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'BIO', 'Pflichtbereich', 2);
        // CH
        // PH
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GE', 'Pflichtbereich', 1);
        // G/R/W
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GEO', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'SPO', 'Pflichtbereich', 3);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/e', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/k', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'ETH', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeGy, $level, 'KU', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MU', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'TC', 'Pflichtbereich', 1);
        // INF

        // Profil
        // 3. FS
    }

    private function setSachsenGyLevel6(TblType $tblSchoolTypeGy)
    {
        $level = 6;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'EN', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', 3, 'FS_2');
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MA', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'BIO', 'Pflichtbereich', 2);
        // CH
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'PH', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GE', 'Pflichtbereich', 2);
        // G/R/W
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GEO', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'SPO', 'Pflichtbereich', 3);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/e', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/k', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'ETH', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeGy, $level, 'KU', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MU', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'TC', 'Pflichtbereich', 1);
        // INF

        // Profil
        // 3. FS
    }

    private function setSachsenGyLevel7(TblType $tblSchoolTypeGy)
    {
        $level = 7;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'EN', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', 4, 'FS_2');
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MA', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'BIO', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'CH', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'PH', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GE', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'G/R/W', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GEO', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'SPO', 'Pflichtbereich', 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/e', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/k', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'ETH', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeGy, $level, 'KU', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MU', 'Pflichtbereich', 1);
        // TC
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'INF', 'Pflichtbereich', 1);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', 2, 'PROFILE'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', 3, 'FS_3'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
    }

    private function setSachsenGyLevel8(TblType $tblSchoolTypeGy)
    {
        $level = 8;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'EN', 'Pflichtbereich', 3);
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', 3, 'FS_2');
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MA', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'BIO', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'CH', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'PH', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GE', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'G/R/W', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GEO', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'SPO', 'Pflichtbereich', 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/e', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/k', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'ETH', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeGy, $level, 'KU', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MU', 'Pflichtbereich', 1);
        // TC
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'INF', 'Pflichtbereich', 1);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', 2, 'PROFILE'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', 3, 'FS_3'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
    }

    private function setSachsenGyLevel9(TblType $tblSchoolTypeGy)
    {
        $level = 9;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'EN', 'Pflichtbereich', 3);
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', 3, 'FS_2');
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MA', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'BIO', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'CH', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'PH', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GE', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'G/R/W', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GEO', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'SPO', 'Pflichtbereich', 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/e', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/k', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'ETH', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeGy, $level, 'KU', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MU', 'Pflichtbereich', 1);
        // TC
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'INF', 'Pflichtbereich', 1);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', 2, 'PROFILE'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', 3, 'FS_3'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
    }

    private function setSachsenGyLevel10(TblType $tblSchoolTypeGy)
    {
        $level = 10;
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'DE', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'EN', 'Pflichtbereich', 3);
        $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Pflichtbereich', 3, 'FS_2');
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MA', 'Pflichtbereich', 4);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'BIO', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'CH', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'PH', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GE', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'G/R/W', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'GEO', 'Pflichtbereich', 2);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'SPO', 'Pflichtbereich', 2);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/e', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'RE/k', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, 'ETH', 'Wahlpflichtbereich', 2, 'RELIGION'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }

        $this->setSubjectTable($tblSchoolTypeGy, $level, 'KU', 'Pflichtbereich', 1);
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'MU', 'Pflichtbereich', 1);
        // TC
        $this->setSubjectTable($tblSchoolTypeGy, $level, 'INF', 'Pflichtbereich', 1);

        $linkId = $this->getNextLinkId();
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', 2, 'PROFILE'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
        if (($tblSubjectTable = $this->setSubjectTable($tblSchoolTypeGy, $level, null, 'Wahlpflichtbereich', 3, 'FS_3'))) {
            $this->createSubjectTableLink($linkId, 1, $tblSubjectTable);
        }
    }
}