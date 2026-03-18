<?php

namespace SPHERE\Application\Education\Competence\Skill\Service;

use SPHERE\Application\Education\Competence\Skill\Service\Entity\TblSkill;
use SPHERE\Application\Education\Competence\Skill\Service\Entity\TblSkillArea;
use SPHERE\Application\Education\Competence\Skill\Service\Entity\TblSkillGrid;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupportFocusType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

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
            $parameters[TblSkillGrid::LEVEL] = $level;
        }
        if ($tblSubject !== null) {
            $parameters[TblSkillGrid::SERVICE_TBL_SUBJECT] = $tblSubject->getId();
        }

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblSkillGrid', $parameters);
    }

    /**
     * @param TblType $tblSchoolType
     * @param string $name
     * @param bool $isAverage
     * @param int $level
     * @param TblSubject|null $tblSubject
     * @param TblCourse|null $tblCourse
     * @param TblSupportFocusType|null $tblSupportFocusType
     *
     * @return TblSkillGrid
     */
    public function createSkillGrid(TblType $tblSchoolType, string $name, bool $isAverage,
        int $level, ?TblSubject $tblSubject, ?TblCourse $tblCourse, ?TblSupportFocusType $tblSupportFocusType
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
        // TODO: setServiceTblScoreType

        $manager->saveEntity($entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $entity);

        return $entity;
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
     * @param string $name
     * @param int $sortOrder
     *
     * @return TblSkillArea
     */
    public function createSkillArea(TblSkillGrid $tblSkillGrid, string $name, int $sortOrder): TblSkillArea
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
        $entity->setSkill($skill);
        $entity->setSortOrder($sortOrder);

        $manager->saveEntity($entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $entity);

        return $entity;
    }
}