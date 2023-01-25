<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service;

use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreCondition;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreConditionGradeTypeList;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreConditionGroupList;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreConditionGroupRequirement;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreGroup;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreGroupGradeTypeList;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRule;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRuleConditionList;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRuleSubject;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreRuleSubjectDivisionCourse;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreTypeSubject;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Platform\System\Protocol\Protocol;

abstract class DataScore extends DataMinimumGradeCount
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
     * @param TblYear $tblYear
     * @param TblType $tblSchoolType
     *
     * @return false|TblScoreRuleSubject[]
     */
    public function getScoreRuleSubjectListByYearAndSchoolType(TblYear $tblYear, TblType $tblSchoolType)
    {
        $parameters[TblScoreRuleSubject::ATTR_SERVICE_TBL_YEAR] = $tblYear->getId();
        $parameters[TblScoreRuleSubject::ATTR_SERVICE_TBL_SCHOOL_TYPE] = $tblSchoolType->getId();

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScoreRuleSubject', $parameters);
    }

    /**
     * @param TblScoreRule $tblScoreRule
     * @param TblYear $tblYear
     * @param TblType $tblSchoolType
     *
     * @return false|TblScoreRuleSubject[]
     */
    public function getScoreRuleSubjectListByScoreRuleAndYearAndSchoolType(TblScoreRule $tblScoreRule, TblYear $tblYear, TblType $tblSchoolType)
    {
        $parameters[TblScoreRuleSubject::ATTR_TBL_SCORE_RULE] = $tblScoreRule->getId();
        $parameters[TblScoreRuleSubject::ATTR_SERVICE_TBL_YEAR] = $tblYear->getId();
        $parameters[TblScoreRuleSubject::ATTR_SERVICE_TBL_SCHOOL_TYPE] = $tblSchoolType->getId();

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScoreRuleSubject', $parameters);
    }

    /**
     * @param TblScoreRule $tblScoreRule
     *
     * @return false|TblScoreRuleSubject[]
     */
    public function getScoreRuleSubjectListByScoreRule(TblScoreRule $tblScoreRule)
    {
        $parameters[TblScoreRuleSubject::ATTR_TBL_SCORE_RULE] = $tblScoreRule->getId();

        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScoreRuleSubject', $parameters);
    }

    /**
     * @param TblYear $tblYear
     * @param TblType $tblSchoolType
     * @param int $level
     * @param TblSubject $tblSubject
     *
     * @return false|TblScoreRuleSubject
     */
    public function getScoreRuleSubjectByYearAndSchoolTypeAndLevelAndSubject(TblYear $tblYear, TblType $tblSchoolType, int $level, TblSubject $tblSubject)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblScoreRuleSubject', array(
            TblScoreRuleSubject::ATTR_SERVICE_TBL_YEAR => $tblYear->getId(),
            TblScoreRuleSubject::ATTR_SERVICE_TBL_SCHOOL_TYPE => $tblSchoolType->getId(),
            TblScoreRuleSubject::ATTR_LEVEL => $level,
            TblScoreRuleSubject::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
        ));
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblScoreRuleSubjectDivisionCourse[]
     */
    public function getScoreRuleSubjectDivisionCourseListByYear(TblYear $tblYear)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t')
            ->from(TblScoreRuleSubjectDivisionCourse::class, 't')
            ->join(TblDivisionCourse::class, 'c')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t.serviceTblDivisionCourse', 'c.Id'),
                    $queryBuilder->expr()->eq('c.serviceTblYear', '?1')
                ),
            )
            ->setParameter(1, $tblYear->getId())
            ->distinct()
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblScoreRule $tblScoreRule
     * @param TblYear $tblYear
     *
     * @return false|TblScoreRuleSubjectDivisionCourse[]
     */
    public function getScoreRuleSubjectDivisionCourseListByScoreRuleAndYear(TblScoreRule $tblScoreRule, TblYear $tblYear)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t')
            ->from(TblScoreRuleSubjectDivisionCourse::class, 't')
            ->join(TblDivisionCourse::class, 'c')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t.serviceTblDivisionCourse', 'c.Id'),
                    $queryBuilder->expr()->eq('t.tblGraduationScoreRule', '?1'),
                    $queryBuilder->expr()->eq('c.serviceTblYear', '?2')
                ),
            )
            ->setParameter(1, $tblScoreRule->getId())
            ->setParameter(2, $tblYear->getId())
            ->distinct()
            ->getQuery();

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
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
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     *
     * @return false|TblScoreRuleSubjectDivisionCourse
     */
    public function getScoreRuleSubjectDivisionCourseByDivisionCourseAndSubject(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblScoreRuleSubjectDivisionCourse', array(
            TblScoreRuleSubjectDivisionCourse::ATTR_SERVICE_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
            TblScoreRuleSubjectDivisionCourse::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
        ));
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreRuleConditionList
     */
    public function getScoreRuleConditionListById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblScoreRuleConditionList', $Id);
    }

    /**
     * @param TblScoreRule $tblScoreRule
     *
     * @return bool|TblScoreRuleConditionList[]
     */
    public function getScoreRuleConditionListByScoreRule(TblScoreRule $tblScoreRule)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScoreRuleConditionList',
            array(TblScoreRuleConditionList::ATTR_TBL_SCORE_RULE => $tblScoreRule->getId())
        );
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return bool|TblScoreRuleConditionList[]
     */
    public function getScoreRuleConditionListByScoreCondition(TblScoreCondition $tblScoreCondition)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScoreRuleConditionList',
            array(TblScoreRuleConditionList::ATTR_TBL_SCORE_CONDITION => $tblScoreCondition->getId())
        );
    }

    /**
     * @param TblScoreRule $tblScoreRule
     *
     * @return bool
     */
    public function getIsScoreRuleUsed(TblScoreRule $tblScoreRule): bool
    {
        if ($this->getScoreRuleSubjectListByScoreRule($tblScoreRule)) {
            return true;
        }
        if ($this->getScoreRuleSubjectDivisionCourseListByScoreRule($tblScoreRule)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param string $DescriptionForExtern
     *
     * @return TblScoreRule
     */
    public function createScoreRule(string $Name, string $Description, string $DescriptionForExtern): TblScoreRule
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblScoreRule')->findOneBy(array(TblScoreRule::ATTR_NAME => $Name));

        if (null === $Entity) {
            $Entity = new TblScoreRule($Name, $Description, $DescriptionForExtern);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblScoreRule $tblScoreRule
     * @param string $Name
     * @param string $Description
     * @param string $DescriptionForExtern
     * @param bool $IsActive
     *
     * @return bool
     */
    public function updateScoreRule(TblScoreRule $tblScoreRule, string $Name, string $Description, string $DescriptionForExtern, bool $IsActive): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblScoreRule $Entity */
        $Entity = $Manager->getEntityById('TblScoreRule', $tblScoreRule->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setDescriptionForExtern($DescriptionForExtern);
            $Entity->setIsActive($IsActive);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblScoreRule $tblScoreRule
     *
     * @return bool
     */
    public function destroyScoreRule(TblScoreRule $tblScoreRule): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblScoreRule $Entity */
        $Entity = $Manager->getEntityById('TblScoreRule', $tblScoreRule->getId());
        if (null !== $Entity) {
            if (($tblScoreConditionsByScoreRule = $this->getScoreRuleConditionListByScoreRule($tblScoreRule))) {
                foreach ($tblScoreConditionsByScoreRule as $tblScoreRuleConditionList) {
                    $this->removeScoreRuleConditionList($tblScoreRuleConditionList);
                }
            }

            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);

            return true;
        }
        return false;
    }

    /**
     * @param TblScoreRule $tblScoreRule
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return TblScoreRuleConditionList
     */
    public function addScoreRuleConditionList(TblScoreRule $tblScoreRule, TblScoreCondition $tblScoreCondition): TblScoreRuleConditionList
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblScoreRuleConditionList')
            ->findOneBy(array(
                TblScoreRuleConditionList::ATTR_TBL_SCORE_RULE => $tblScoreRule->getId(),
                TblScoreRuleConditionList::ATTR_TBL_SCORE_CONDITION => $tblScoreCondition->getId(),
            ));

        if (null === $Entity) {
            $Entity = new TblScoreRuleConditionList($tblScoreCondition, $tblScoreRule);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblScoreRuleConditionList $tblScoreRuleConditionList
     *
     * @return bool
     */
    public function removeScoreRuleConditionList(TblScoreRuleConditionList $tblScoreRuleConditionList): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblScoreConditionGroupList $Entity */
        $Entity = $Manager->getEntityById('TblScoreRuleConditionList', $tblScoreRuleConditionList->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);

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
     * @param $Id
     *
     * @return bool|TblScoreConditionGradeTypeList
     */
    public function getScoreConditionGradeTypeListById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblScoreConditionGradeTypeList', $Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblScoreConditionGroupList
     */
    public function getScoreConditionGroupListById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblScoreConditionGroupList', $Id);
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return bool|TblScoreConditionGroupList[]
     */
    public function getScoreConditionGroupListByCondition(TblScoreCondition $tblScoreCondition)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScoreConditionGroupList',
            array(TblScoreConditionGroupList::ATTR_TBL_SCORE_CONDITION => $tblScoreCondition->getId())
        );
    }

    /**
     * @param TblScoreGroup $tblScoreGroup
     *
     * @return bool|TblScoreConditionGroupList[]
     */
    public function getScoreConditionGroupListByGroup(TblScoreGroup $tblScoreGroup)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblScoreConditionGroupList',
            array(TblScoreConditionGroupList::ATTR_TBL_SCORE_GROUP => $tblScoreGroup->getId())
        );
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return bool|TblScoreConditionGradeTypeList[]
     */
    public function getScoreConditionGradeTypeListByCondition(TblScoreCondition $tblScoreCondition)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblScoreConditionGradeTypeList',
            array(TblScoreConditionGradeTypeList::ATTR_TBL_SCORE_CONDITION => $tblScoreCondition->getId())
        );
    }

    /**
     * @param $Id
     *
     * @return false|TblScoreConditionGroupRequirement
     */
    public function getScoreConditionGroupRequirementById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblScoreConditionGroupRequirement', $Id);
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     *
     * @return bool|TblScoreConditionGroupRequirement[]
     */
    public function getScoreConditionGroupRequirementAllByCondition(TblScoreCondition $tblScoreCondition)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblScoreConditionGroupRequirement',
            array(TblScoreConditionGroupRequirement::ATTR_TBL_SCORE_CONDITION => $tblScoreCondition->getId())
        );
    }

    /**
     * @param TblScoreGroup $tblScoreGroup
     *
     * @return bool|TblScoreConditionGroupRequirement[]
     */
    public function getScoreConditionGroupRequirementAllByGroup(TblScoreGroup $tblScoreGroup)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblScoreConditionGroupRequirement',
            array(TblScoreConditionGroupRequirement::ATTR_TBL_SCORE_GROUP => $tblScoreGroup->getId())
        );
    }

    /**
     * @param string $Name
     * @param string $Priority
     *
     * @return TblScoreCondition
     */
    public function createScoreCondition(string $Name, string $Priority): TblScoreCondition
    {
        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity('TblScoreCondition')->findOneBy(array(TblScoreCondition::ATTR_NAME => $Name));

        if (null === $Entity) {
            $Entity = new TblScoreCondition($Name, $Priority);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     * @param $Name
     * @param $Priority
     * @param $IsActive
     * @param null $Period
     *
     * @return bool
     */
    public function updateScoreCondition(TblScoreCondition $tblScoreCondition, $Name, $Priority, $IsActive, $Period = null): bool
    {
        $Manager = $this->getEntityManager();
        /** @var TblScoreCondition $Entity */
        $Entity = $Manager->getEntityById('TblScoreCondition', $tblScoreCondition->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setPriority($Priority);
            $Entity->setIsActive($IsActive);
            $Entity->setPeriod($Period);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     * @param TblScoreGroup $tblScoreGroup
     *
     * @return TblScoreConditionGroupList
     */
    public function addScoreConditionGroupList(TblScoreCondition $tblScoreCondition, TblScoreGroup $tblScoreGroup): TblScoreConditionGroupList
    {
        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity('TblScoreConditionGroupList')
            ->findOneBy(array(
                TblScoreConditionGroupList::ATTR_TBL_SCORE_GROUP => $tblScoreGroup->getId(),
                TblScoreConditionGroupList::ATTR_TBL_SCORE_CONDITION => $tblScoreCondition->getId(),
            ));

        if (null === $Entity) {
            $Entity = new TblScoreConditionGroupList($tblScoreGroup, $tblScoreCondition);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblScoreConditionGroupList $tblScoreConditionGroupList
     *
     * @return bool
     */
    public function removeScoreConditionGroupList(TblScoreConditionGroupList $tblScoreConditionGroupList): bool
    {
        $Manager = $this->getEntityManager();
        /** @var TblScoreConditionGroupList $Entity */
        $Entity = $Manager->getEntityById('TblScoreConditionGroupList', $tblScoreConditionGroupList->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblGradeType $tblGradeType
     * @param TblScoreCondition $tblScoreCondition
     * @param $count
     *
     * @return TblScoreConditionGradeTypeList
     */
    public function addScoreConditionGradeTypeList(TblGradeType $tblGradeType, TblScoreCondition $tblScoreCondition, $count): TblScoreConditionGradeTypeList
    {
        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity('TblScoreConditionGradeTypeList')
            ->findOneBy(array(
                TblScoreConditionGradeTypeList::ATTR_TBL_GRADE_TYPE => $tblGradeType->getId(),
                TblScoreConditionGradeTypeList::ATTR_TBL_SCORE_CONDITION => $tblScoreCondition->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblScoreConditionGradeTypeList($count, $tblGradeType, $tblScoreCondition);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblScoreGroup $tblScoreGroup
     * @param TblScoreCondition $tblScoreCondition
     * @param $count
     *
     * @return TblScoreConditionGroupRequirement
     */
    public function addScoreConditionGroupRequirement(TblScoreGroup $tblScoreGroup, TblScoreCondition $tblScoreCondition, $count): TblScoreConditionGroupRequirement
    {
        $Manager = $this->getEntityManager();
        $Entity = $Manager->getEntity('TblScoreConditionGroupRequirement')
            ->findOneBy(array(
                TblScoreConditionGroupRequirement::ATTR_TBL_SCORE_GROUP => $tblScoreGroup->getId(),
                TblScoreConditionGroupRequirement::ATTR_TBL_SCORE_CONDITION => $tblScoreCondition->getId(),
            ));

        if (null === $Entity) {
            $Entity = new TblScoreConditionGroupRequirement($count, $tblScoreGroup, $tblScoreCondition);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblScoreConditionGradeTypeList $tblScoreConditionGradeTypeList
     *
     * @return bool
     */
    public function removeScoreConditionGradeTypeList(TblScoreConditionGradeTypeList $tblScoreConditionGradeTypeList): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblScoreConditionGradeTypeList $Entity */
        $Entity = $Manager->getEntityById('TblScoreConditionGradeTypeList', $tblScoreConditionGradeTypeList->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblScoreConditionGroupRequirement $tblScoreConditionGroupRequirement
     *
     * @return bool
     */
    public function removeScoreConditionGroupRequirement(TblScoreConditionGroupRequirement $tblScoreConditionGroupRequirement): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblScoreConditionGroupRequirement $Entity */
        $Entity = $Manager->getEntityById('TblScoreConditionGroupRequirement', $tblScoreConditionGroupRequirement->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
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