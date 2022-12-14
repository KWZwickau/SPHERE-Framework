<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service;

use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreGroup;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreGroupGradeTypeList;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRule;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRuleSubject;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRuleSubjectDivisionCourse;
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
     * @param TblScoreRule $tblScoreRule
     * @param TblType|null $tblSchoolType
     *
     * @return false|TblScoreRuleSubject[]
     */
    public function getScoreRuleSubjectListByScoreRule(TblScoreRule $tblScoreRule, ?TblType $tblSchoolType)
    {
        $parameters[TblScoreRuleSubject::ATTR_TBL_SCORE_RULE] = $tblScoreRule->getId();
        if ($tblSchoolType) {
            $parameters[TblScoreRuleSubject::ATTR_SERVICE_TBL_SCHOOL_TYPE] = $tblSchoolType->getId();
        }

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScoreRuleSubject', $parameters);
    }

    /**
     * @param TblScoreRule $tblScoreRule
     *
     * @return false|TblScoreRuleSubjectDivisionCourse[]
     */
    public function getScoreRuleSubjectDivisionCourseListByScoreRule(TblScoreRule $tblScoreRule)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScoreRuleSubjectDivisionCourse', array(
            TblScoreRuleSubjectDivisionCourse::ATTR_TBL_SCORE_RULE => $tblScoreRule->getId()
        ));
    }

    /**
     * @param TblScoreRule $tblScoreRule
     *
     * @return bool
     */
    public function getIsScoreRuleUsed(TblScoreRule $tblScoreRule): bool
    {
        if ($this->getScoreRuleSubjectListByScoreRule($tblScoreRule, null)) {
            return true;
        }
        if ($this->getScoreRuleSubjectDivisionCourseListByScoreRule($tblScoreRule)) {
            return true;
        }
        return false;
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
     * @param bool $withInActive
     *
     * @return false|TblScoreCondition[]
     */
    public function getScoreConditionAll(bool $withInActive = false)
    {
        return $withInActive
            ? $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblScoreCondition')
            : $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScoreCondition', array(TblScoreCondition::ATTR_IS_ACTIVE => true));
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

    /**
     * @param bool $withInActive
     *
     * @return false|TblScoreGroup[]
     */
    public function getScoreGroupAll(bool $withInActive = false)
    {
        return $withInActive
            ? $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblScoreGroup')
            : $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScoreGroup', array(TblScoreGroup::ATTR_IS_ACTIVE => true));
    }

    /**
     * @param TblScoreGroup $tblScoreGroup
     *
     * @return bool|TblScoreGroupGradeTypeList[]
     */
    public function getScoreGroupGradeTypeListByGroup(TblScoreGroup $tblScoreGroup)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScoreGroupGradeTypeList', array(
            TblScoreGroupGradeTypeList::ATTR_TBL_SCORE_GROUP => $tblScoreGroup->getId()
        ));
    }

    /**
     * @param string $name
     * @param string $multiplier
     * @param bool $isEveryGradeASingleGroup
     *
     * @return TblScoreGroup
     */
    public function createScoreGroup(string $name, string $multiplier, bool $isEveryGradeASingleGroup): TblScoreGroup
    {
        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity('TblScoreGroup')->findOneBy(array(TblScoreGroup::ATTR_NAME => $name));

        if (null === $Entity) {
            $Entity = new TblScoreGroup($name, $multiplier, $isEveryGradeASingleGroup);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblScoreGroup $tblScoreGroup
     * @param string $Name
     * @param string $Multiplier
     * @param bool $IsEveryGradeASingleGroup
     * @param bool $IsActive
     *
     * @return bool
     */
    public function updateScoreGroup(TblScoreGroup $tblScoreGroup, string $Name, string $Multiplier, bool $IsEveryGradeASingleGroup, bool $IsActive): bool
    {
        $Manager = $this->getEntityManager();
        /** @var TblScoreGroup $Entity */
        $Entity = $Manager->getEntityById('TblScoreGroup', $tblScoreGroup->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setMultiplier($Multiplier);
            $Entity->setIsEveryGradeASingleGroup($IsEveryGradeASingleGroup);
            $Entity->setIsActive($IsActive);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreGroupGradeTypeList
     */
    public function getScoreGroupGradeTypeListById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblScoreGroupGradeTypeList', $Id);
    }

    /**
     * @param TblGradeType $tblGradeType
     * @param TblScoreGroup $tblScoreGroup
     * @param               $Multiplier
     *
     * @return TblScoreGroupGradeTypeList
     */
    public function addScoreGroupGradeTypeList(TblGradeType $tblGradeType, TblScoreGroup $tblScoreGroup, $Multiplier): TblScoreGroupGradeTypeList
    {
        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity('TblScoreGroupGradeTypeList')
            ->findOneBy(array(
                TblScoreGroupGradeTypeList::ATTR_TBL_SCORE_GROUP => $tblScoreGroup->getId(),
                TblScoreGroupGradeTypeList::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId(),
            ));

        if (null === $Entity) {
            $Entity = new TblScoreGroupGradeTypeList($Multiplier, $tblGradeType, $tblScoreGroup);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblScoreGroupGradeTypeList $tblScoreGroupGradeTypeList
     *
     * @return bool
     */
    public function removeScoreGroupGradeTypeList(TblScoreGroupGradeTypeList $tblScoreGroupGradeTypeList): bool
    {
        $Manager = $this->getEntityManager();
        /** @var TblScoreGroupGradeTypeList $Entity */
        $Entity = $Manager->getEntityById('TblScoreGroupGradeTypeList', $tblScoreGroupGradeTypeList->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblScoreGroup $tblScoreGroup
     *
     * @return bool
     */
    public function destroyScoreGroup(TblScoreGroup $tblScoreGroup): bool
    {
        $Manager = $this->getEntityManager();
        /** @var TblScoreGroup $Entity */
        $Entity = $Manager->getEntityById('TblScoreGroup', $tblScoreGroup->getId());
        if (null !== $Entity) {
            if (($list = $this->getScoreGroupGradeTypeListByGroup($tblScoreGroup))) {
                foreach ($list as $item) {
                    $this->removeScoreGroupGradeTypeList($item);
                }
            }

            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);

            return true;
        }
        return false;
    }
}