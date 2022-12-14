<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service;

use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreGroup;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRule;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreTypeSubject;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Platform\System\Protocol\Protocol;

abstract class DataScore extends DataMigrate
{
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
     * @param $identifier
     *
     * @return false|TblScoreType
     */
    public function getScoreTypeByIdentifier($identifier)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblScoreType', array(TblScoreType::ATTR_IDENTIFIER => $identifier));
    }

    /**
     * @return false|TblScoreType[]
     */
    public function getScoreTypeAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblScoreType');
    }

    /**
     * @param TblScoreType $tblScoreType
     * @param TblType|null $tblSchoolType
     *
     * @return false|TblScoreTypeSubject[]
     */
    public function getScoreTypeSubjectListByScoreType(TblScoreType $tblScoreType, ?TblType $tblSchoolType)
    {
        $parameters[TblScoreTypeSubject::ATTR_TBL_SCORE_TYPE] = $tblScoreType->getId();
        if ($tblSchoolType) {
            $parameters[TblScoreTypeSubject::ATTR_SERVICE_TBL_SCHOOL_TYPE] = $tblSchoolType->getId();
        }

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScoreTypeSubject', $parameters);
    }

    /**
     * @param TblType $tblSchoolType
     *
     * @return false|TblScoreTypeSubject[]
     */
    public function getScoreTypeSubjectListBySchoolType(TblType $tblSchoolType)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScoreTypeSubject', array(
            TblScoreTypeSubject::ATTR_SERVICE_TBL_SCHOOL_TYPE => $tblSchoolType->getId()
        ));
    }

    /**
     * @param TblType $tblSchoolType
     * @param int $level
     * @param TblSubject $tblSubject
     *
     * @return false|TblScoreTypeSubject
     */
    public function getScoreTypeSubjectBySchoolTypeAndLevelAndSubject(TblType $tblSchoolType, int $level, TblSubject $tblSubject)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblScoreTypeSubject', array(
            TblScoreTypeSubject::ATTR_SERVICE_TBL_SCHOOL_TYPE => $tblSchoolType->getId(),
            TblScoreTypeSubject::ATTR_LEVEL => $level,
            TblScoreTypeSubject::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
        ));
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
     * @param $id
     *
     * @return false|TblScoreRule
     */
    public function getScoreRuleById($id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblScoreRule', $id);
    }

    /**
     * @param bool $withInActive
     *
     * @return false|TblScoreRule[]
     */
    public function getScoreRuleAll(bool $withInActive = false)
    {
        return $withInActive
            ? $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblScoreRule')
            : $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScoreRule', array(TblScoreRule::ATTR_IS_ACTIVE => true));
    }

    /**
     * @param $id
     *
     * @return false|TblScoreCondition
     */
    public function getScoreConditionById($id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblScoreCondition', $id);
    }

    /**
     * @param $id
     *
     * @return false|TblScoreGroup
     */
    public function getScoreGroupById($id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblScoreGroup', $id);
    }
}