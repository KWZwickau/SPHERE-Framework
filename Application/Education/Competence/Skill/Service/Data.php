<?php

namespace SPHERE\Application\Education\Competence\Skill\Service;

use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Competence\Skill\Service\Entity\TblSkill;
use SPHERE\Application\Education\Competence\Skill\Service\Entity\TblSkillArea;
use SPHERE\Application\Education\Competence\Skill\Service\Entity\TblSkillGrid;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupportFocusType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

class Data extends AbstractData
{
    /**
     * @return void
     */
    public function setupDatabaseContent(): void
    {

    }

    /**
     * @param $id
     *
     * @return TblSkillGrid|false
     */
    public function getSkillGridById($id): TblSkillGrid|false
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblSkillGrid', $id);
    }

    /**
     * @param TblType $tblSchoolType
     * @param int|null $level
     * @param TblSubject|null $tblSubject
     *
     * @return TblSkillGrid[]|false
     */
    public function getSkillGridListBy(TblType $tblSchoolType, ?int $level = null, ?TblSubject $tblSubject = null): array|false
    {
        $parameters[TblSkillGrid::SERVICE_TBL_SCHOOL_TYPE] = $tblSchoolType->getId();
        if ($level !== null) {
            $parameters[TblSkillGrid::ATTR_LEVEL] = $level;
        }
        if ($tblSubject !== null) {
            $parameters[TblSkillGrid::SERVICE_TBL_SUBJECT] = $tblSubject->getId();
        }

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblSkillGrid', $parameters);
    }

    /**
     * @param TblScoreType $tblScoreType
     *
     * @return bool
     */
    public function getIsScoreTypeUsedInAnySkillGrid(TblScoreType $tblScoreType): bool
    {
        return (bool) $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblSkillGrid',
            [TblSkillGrid::SERVICE_TBL_SCORE_TYPE => $tblScoreType->getId()]);
    }

    /**
     * @param TblType $tblSchoolType
     * @param string $name
     * @param bool $isAverage
     * @param int $level
     * @param TblSubject|null $tblSubject
     * @param TblCourse|null $tblCourse
     * @param TblSupportFocusType|null $tblSupportFocusType
     * @param TblScoreType|null $tblScoreType
     *
     * @return TblSkillGrid
     */
    public function createSkillGrid(TblType $tblSchoolType, string $name, bool $isAverage,
        int $level, ?TblSubject $tblSubject, ?TblCourse $tblCourse, ?TblSupportFocusType $tblSupportFocusType, ?TblScoreType $tblScoreType
    ): TblSkillGrid {
        $manager = $this->getEntityManager();

        $entity = new TblSkillGrid();
        $entity->setServiceTblSchoolType($tblSchoolType);
        $entity->setLevel($level);
        $entity->setServiceTblSubject($tblSubject);
        $entity->setName($name);
        $entity->setIsAverage($isAverage);
        $entity->setServiceTblCourse($tblCourse);
        $entity->setServiceTblSupportFocusType($tblSupportFocusType);
        $entity->setServiceTblScoreType($tblScoreType);

        $manager->saveEntity($entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $entity);

        return $entity;
    }

    /**
     * @param TblSkillGrid $tblSkillGrid
     * @param string $name
     * @param bool $isAverage
     * @param int $level
     * @param TblSubject|null $tblSubject
     * @param TblCourse|null $tblCourse
     * @param TblSupportFocusType|null $tblSupportFocusType
     * @param TblScoreType|null $tblScoreType
     *
     * @return bool
     */
    public function updateSkillGrid(TblSkillGrid $tblSkillGrid, string $name, bool $isAverage,
        int $level, ?TblSubject $tblSubject, ?TblCourse $tblCourse, ?TblSupportFocusType $tblSupportFocusType, ?TblScoreType $tblScoreType
    ): bool {
        $manager = $this->getEntityManager();
        /** @var TblSkillGrid $entity */
        $entity = $manager->getEntityById('TblSkillGrid', $tblSkillGrid->getId());
        $protocol = clone $entity;
        if (null !== $entity) {
            $entity->setLevel($level);
            $entity->setServiceTblSubject($tblSubject);
            $entity->setName($name);
            $entity->setIsAverage($isAverage);
            $entity->setServiceTblCourse($tblCourse);
            $entity->setServiceTblSupportFocusType($tblSupportFocusType);
            $entity->setServiceTblScoreType($tblScoreType);

            $manager->saveEntity($entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $protocol, $entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblSkillGrid $tblSkillGrid
     *
     * @return bool
     */
    public function destroySkillGrid(TblSkillGrid $tblSkillGrid): bool
    {
        $manager = $this->getConnection()->getEntityManager();
        /** @var Element $entity */
        $entity = $manager->getEntityById('TblSkillGrid', $tblSkillGrid->getId());
        if (null !== $entity) {
            $manager->killEntity($entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $entity);

            return true;
        }

        return false;
    }

    /**
     * @param $id
     *
     * @return TblSkillArea|false
     */
    public function getSkillAreaById($id): TblSkillArea|false
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblSkillArea', $id);
    }

    /**
     * @param TblSkillGrid $tblSkillGrid
     *
     * @return TblSkillArea[]
     */
    public function getSkillAreaListBySkillGrid(TblSkillGrid $tblSkillGrid): array
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblSkillArea',
            [TblSkillArea::ATTR_TBL_SKILL_GRID => $tblSkillGrid->getId()], [TblSkillArea::ATTR_SORT_ORDER => self::ORDER_ASC]) ?: [];
    }

    /**
     * @param TblSkillGrid $tblSkillGrid
     * @param string|null $name
     * @param int $sortOrder
     *
     * @return TblSkillArea
     */
    public function createSkillArea(TblSkillGrid $tblSkillGrid, ?string $name, int $sortOrder): TblSkillArea
    {
        $manager = $this->getEntityManager();

        $entity = new TblSkillArea();
        $entity->setTblSkillGrid($tblSkillGrid);
        $entity->setName($name);
        $entity->setSortOrder($sortOrder);

        $manager->saveEntity($entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $entity);

        return $entity;
    }

    /**
     * @param array $tblSkillAreaList
     *
     * @return bool
     */
    public function destroySkillAreaBulkList(array $tblSkillAreaList): bool
    {
        $manager = $this->getEntityManager();

        foreach ($tblSkillAreaList as $tblSkillArea) {
            /** @var Element $entity */
            $entity = $manager->getEntityById('TblSkillArea', $tblSkillArea->getId());
            if (null !== $entity) {
                $manager->bulkKillEntity($entity);
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $entity, true);
            }
        }

        $manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param TblSkillGrid $tblSkillGrid
     *
     * @return TblSkill[]
     */
    public function getSkillListBySkillGrid(TblSkillGrid $tblSkillGrid): array
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('s')
            ->from(TblSkill::class, 's')
            ->join(TblSkillArea::class, 'a')
            ->join(TblSkillGrid::class, 'g')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('s.tblCompetenceSkillArea', 'a.Id'),
                    $queryBuilder->expr()->eq('a.tblCompetenceSkillGrid', 'g.Id'),
                    $queryBuilder->expr()->eq('g.Id', '?1'),
                ),
            )
            ->setParameter(1, $tblSkillGrid->getId())
            ->orderBy('a.SortOrder', 'ASC')
            ->addOrderBy('s.SortOrder', 'ASC')
            ->getQuery();

        $resultList = $query->getResult();

        return $resultList ?: [];
    }

    /**
     * @param TblSkillArea $tblSkillArea
     * @param string|null $level
     * @param string $skill
     * @param int $sortOrder
     *
     * @return TblSkill
     */
    public function createSkill(TblSkillArea $tblSkillArea, ?string $level, string $skill, int $sortOrder): TblSkill
    {
        $manager = $this->getEntityManager();

        $entity = new TblSkill();
        $entity->setTblSkillArea($tblSkillArea);
        $entity->setLevel($level);
        $entity->setSkill($skill);
        $entity->setSortOrder($sortOrder);

        $manager->saveEntity($entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $entity);

        return $entity;
    }

    /**
     * @param array $tblSkillList
     *
     * @return bool
     */
    public function destroySkillBulkList(array $tblSkillList): bool
    {
        $manager = $this->getEntityManager();

        foreach ($tblSkillList as $tblSkill) {
            /** @var Element $entity */
            $entity = $manager->getEntityById('TblSkill', $tblSkill->getId());
            if (null !== $entity) {
                $manager->bulkKillEntity($entity);
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $entity, true);
            }
        }

        $manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }
}