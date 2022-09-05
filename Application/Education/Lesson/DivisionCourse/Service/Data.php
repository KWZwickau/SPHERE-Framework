<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service;

use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseLink;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Extension\Extension;

class Data extends MigrateData
{
    public function setupDatabaseContent()
    {
        $this->createDivisionCourseType('Klasse', TblDivisionCourseType::TYPE_DIVISION);
        $this->createDivisionCourseType('Stammgruppe', TblDivisionCourseType::TYPE_CORE_GROUP);
        $this->createDivisionCourseType('Unterrichtsgruppe', TblDivisionCourseType::TYPE_TEACHING_GROUP);

        $this->createDivisionCourseMemberType('Schüler', TblDivisionCourseMemberType::TYPE_STUDENT);
        $this->createDivisionCourseMemberType('Gruppenleiter', TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER);
        $this->createDivisionCourseMemberType('Elternvertreter', TblDivisionCourseMemberType::TYPE_CUSTODY);
        $this->createDivisionCourseMemberType('Schülersprecher', TblDivisionCourseMemberType::TYPE_REPRESENTATIVE);

        /**
         * Migration der alten Klassen-Daten in die neue DB-Struktur
         */
        $this->migrateAll();
    }

    /**
     * @param string $Name
     * @param string $Identifier
     *
     * @return TblDivisionCourseType
     */
    public function createDivisionCourseType(string $Name, string  $Identifier): TblDivisionCourseType
    {
        $Manager = $this->getConnection()->getEntityManager(false);
        $Entity = $Manager->getEntity('TblDivisionCourseType')->findOneBy(array(
            TblDivisionCourseType::ATTR_IDENTIFIER => $Identifier
        ));

        if (null === $Entity) {
            $Entity = new TblDivisionCourseType();
            $Entity->setName($Name);
            $Entity->setIdentifier($Identifier);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param string $Name
     * @param string $Identifier
     *
     * @return TblDivisionCourseMemberType
     */
    public function createDivisionCourseMemberType(string $Name, string  $Identifier): TblDivisionCourseMemberType
    {
        $Manager = $this->getConnection()->getEntityManager(false);
        $Entity = $Manager->getEntity('TblDivisionCourseMemberType')->findOneBy(array(
            TblDivisionCourseMemberType::ATTR_IDENTIFIER => $Identifier
        ));

        if (null === $Entity) {
            $Entity = new TblDivisionCourseMemberType();
            $Entity->setName($Name);
            $Entity->setIdentifier($Identifier);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblDivisionCourseType $tblType
     * @param TblYear $tblYear
     * @param string $name
     * @param string $description
     * @param bool $isShownInPersonData
     * @param bool $isReporting
     * @param bool $isUcs
     *
     * @return TblDivisionCourse
     */
    public function createDivisionCourse(TblDivisionCourseType $tblType, TblYear $tblYear, string $name, string $description,
        bool $isShownInPersonData, bool $isReporting, bool $isUcs): TblDivisionCourse
    {
        $Manager = $this->getEntityManager();

        $Entity = TblDivisionCourse::withParameter($tblType, $tblYear, $name, $description, $isShownInPersonData, $isReporting, $isUcs);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param string $name
     * @param string $description
     * @param bool $isShownInPersonData
     * @param bool $isReporting
     * @param bool $isUcs
     *
     * @return bool
     */
    public function updateDivisionCourse(TblDivisionCourse $tblDivisionCourse, string $name, string $description,
        bool $isShownInPersonData, bool $isReporting, bool $isUcs): bool
    {
        $Manager = $this->getEntityManager();
        /** @var TblDivisionCourse $Entity */
        $Entity = $Manager->getEntityById('TblDivisionCourse', $tblDivisionCourse->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($name);
            $Entity->setDescription($description);
            $Entity->setIsShownInPersonData($isShownInPersonData);
            $Entity->setIsReporting($isReporting);
            $Entity->setIsUcs($isUcs);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return bool
     */
    public function destroyDivisionCourse(TblDivisionCourse $tblDivisionCourse): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblDivisionCourse $Entity */
        $Entity = $Manager->getEntityById('TblDivisionCourse', $tblDivisionCourse->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourse
     */
    public function getDivisionCourseById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblDivisionCourse', $Id);
    }

    /**
     * @param string|null $TypeIdentifier
     *
     * @return false|TblDivisionCourse[]
     */
    public function getDivisionCourseAll(?string $TypeIdentifier = '')
    {
        if ($TypeIdentifier && ($tblType = $this->getDivisionCourseTypeByIdentifier($TypeIdentifier))) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourse', array(TblDivisionCourse::ATTR_TBL_TYPE => $tblType->getId()));
        } else {
            return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblDivisionCourse');
        }
    }

    /**
     * @param TblYear|null $tblYear
     * @param string|null $TypeIdentifier
     *
     * @return false|TblDivisionCourse[]
     */
    public function getDivisionCourseListBy(TblYear $tblYear = null, ?string $TypeIdentifier = '')
    {
        $parameterList = array();
        if ($TypeIdentifier && ($tblType = $this->getDivisionCourseTypeByIdentifier($TypeIdentifier))) {
            $parameterList[TblDivisionCourse::ATTR_TBL_TYPE] = $tblType->getId();
        }
        if ($tblYear) {
            $parameterList[TblDivisionCourse::SERVICE_TBL_YEAR] = $tblYear->getId();
        }

        if ($parameterList) {
            return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourse', $parameterList);
        } else {
            return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblDivisionCourse');
        }
    }

    /**
     * @param string $name
     * @param array|null $tblYearList
     *
     * @return TblDivisionCourse[]|false
     */
    public function getDivisionCourseListByLikeName(string $name, ?array $tblYearList = null)
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        if ($tblYearList) {
            $count = 2;
            $or = $queryBuilder->expr()->orX();
            foreach ($tblYearList as $tblYear) {
                $or->add($queryBuilder->expr()->eq('t.serviceTblYear', '?' . $count));
                $queryBuilder->setParameter($count, $tblYear->getId());
                $count++;
            }
            $query = $queryBuilder->select('t')
                ->from(__NAMESPACE__ . '\Entity\TblDivisionCourse', 't')
                ->where(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->like('t.Name', '?1'),
                        $or
                    )
                )
                ->setParameter(1, '%' . $name . '%')
                ->getQuery();
        } else {
            $query = $queryBuilder->select('t')
                ->from(__NAMESPACE__ . '\Entity\TblDivisionCourse', 't')
                ->where(
                    $queryBuilder->expr()->like('t.Name', '?1')
                )
                ->setParameter(1, '%' . $name . '%')
                ->getQuery();
        }

        $resultList = $query->getResult();

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourse $tblSubDivisionCourse
     *
     * @return TblDivisionCourseLink
     */
    public function addSubDivisionCourseToDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblDivisionCourse $tblSubDivisionCourse)
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivisionCourseLink')
            ->findOneBy(array(
                TblDivisionCourseLink::ATTR_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
                TblDivisionCourseLink::ATTR_TBL_SUB_DIVISION_COURSE => $tblSubDivisionCourse->getId()
            ));

        if (null === $Entity) {
            $Entity = new TblDivisionCourseLink();
            $Entity->setTblDivisionCourse($tblDivisionCourse);
            $Entity->setTblSubDivisionCourse($tblSubDivisionCourse);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourse $tblSubDivisionCourse
     *
     * @return bool
     */
    public function removeSubDivisionCourseFromDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblDivisionCourse $tblSubDivisionCourse): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        $EntityList = $Manager->getEntity('TblDivisionCourseLink')
            ->findBy(array(
                TblDivisionCourseLink::ATTR_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
                TblDivisionCourseLink::ATTR_TBL_SUB_DIVISION_COURSE => $tblSubDivisionCourse->getId()
            ));
        if ($EntityList) {
            foreach ($EntityList as $Entity) {
                $Manager->killEntity($Entity);
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            }

            return true;
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblDivisionCourse[]|false
     */
    public function getSubDivisionCourseListByDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        $resultList = array();
        if (($list = $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseLink',
            array(TblDivisionCourseLink::ATTR_TBL_DIVISION_COURSE => $tblDivisionCourse->getId())))
        ) {
            /** @var TblDivisionCourseLink $tblDivisionCourseLink */
            foreach ($list as $tblDivisionCourseLink) {
                if (($tblSubDivisionCourse = $tblDivisionCourseLink->getTblSubDivisionCourse())) {
                    $resultList[] = $tblSubDivisionCourse;
                }
            }
        }

        if ($resultList) {
            return (new Extension())->getSorter($resultList)->sortObjectBy('Name');
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseLink
     */
    public function getDivisionCourseLinkById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseLink', $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseType
     */
    public function getDivisionCourseTypeById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseType', $Id);
    }

    /**
     * @param string $Identifier
     *
     * @return false|TblDivisionCourseType
     */
    public function getDivisionCourseTypeByIdentifier(string $Identifier)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseType',
            array(TblDivisionCourseType::ATTR_IDENTIFIER => strtoupper($Identifier)));
    }

    /**
     * @return false|TblDivisionCourseType[]
     */
    public function getDivisionCourseTypeAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseType');
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseMemberType
     */
    public function getDivisionCourseMemberTypeById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseMemberType', $Id);
    }

    /**
     * @param string $Identifier
     *
     * @return false|TblDivisionCourseMemberType
     */
    public function getDivisionCourseMemberTypeByIdentifier(string $Identifier)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseMemberType',
            array(TblDivisionCourseMemberType::ATTR_IDENTIFIER => strtoupper($Identifier)));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblDivisionCourseMember[]|false
     */
    public function getDivisionCourseMemberStudentByDivision(TblDivisionCourse $tblDivisionCourse)
    {
        $tempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
                TblStudentEducation::ATTR_TBL_DIVISION => $tblDivisionCourse->getId()
            ));

        $resultList = array();
        if ($tempList
            && ($tblDivisionCourseMemberType = $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT))
        ) {
            /** @var TblStudentEducation $tblStudentEducation */
            foreach ($tempList as $tblStudentEducation) {
                if (($tblPerson = $tblStudentEducation->getServiceTblPerson())) {
                    $resultList[] = TblDivisionCourseMember::withParameter($tblDivisionCourse, $tblDivisionCourseMemberType, $tblPerson, '',
                        $tblStudentEducation->getLeaveDateTime(), $tblStudentEducation->getDivisionSortOrder());
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblDivisionCourseMember[]|false
     */
    public function getDivisionCourseMemberStudentByCoreGroup(TblDivisionCourse $tblDivisionCourse)
    {
        $tempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
            TblStudentEducation::ATTR_TBL_CORE_GROUP => $tblDivisionCourse->getId()
        ));

        $resultList = array();
        if ($tempList
            && ($tblDivisionCourseMemberType = $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT))
        ) {
            /** @var TblStudentEducation $tblStudentEducation */
            foreach ($tempList as $tblStudentEducation) {
                if (($tblPerson = $tblStudentEducation->getServiceTblPerson())) {
                    $resultList[] = TblDivisionCourseMember::withParameter($tblDivisionCourse, $tblDivisionCourseMemberType, $tblPerson, '',
                        $tblStudentEducation->getLeaveDateTime(), $tblStudentEducation->getCoreGroupSortOrder());
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     * @param DateTime|null $leaveDate
     *
     * @return false|TblStudentEducation
     */
    public function getStudentEducationByDivision(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson, ?DateTime $leaveDate)
    {
        return $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
            TblStudentEducation::ATTR_TBL_DIVISION => $tblDivisionCourse->getId(),
            TblStudentEducation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblStudentEducation::ATTR_LEAVE_DATE => $leaveDate
        ));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     *
     * @return false|TblStudentEducation[]
     */
    public function getStudentEducationListByDivision(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson)
    {
        return $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
            TblStudentEducation::ATTR_TBL_DIVISION => $tblDivisionCourse->getId(),
            TblStudentEducation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     * @param DateTime|null $leaveDate
     *
     * @return false|TblStudentEducation
     */
    public function getStudentEducationByCoreGroup(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson, ?DateTime $leaveDate)
    {
        return $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
            TblStudentEducation::ATTR_TBL_CORE_GROUP => $tblDivisionCourse->getId(),
            TblStudentEducation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblStudentEducation::ATTR_LEAVE_DATE => $leaveDate
        ));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     *
     * @return false|TblStudentEducation[]
     */
    public function getStudentEducationListByCoreGroup(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson)
    {
        return $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
            TblStudentEducation::ATTR_TBL_CORE_GROUP => $tblDivisionCourse->getId(),
            TblStudentEducation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return false|TblStudentEducation
     */
    public function getStudentEducationByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear)
    {
        return $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
            TblStudentEducation::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblStudentEducation::ATTR_SERVICE_TBL_YEAR => $tblYear->getId(),
            TblStudentEducation::ATTR_LEAVE_DATE => null
        ));
    }

    /**
     * @param TblStudentEducation $tblStudentEducation
     *
     * @return TblStudentEducation
     */
    public function createStudentEducation(TblStudentEducation $tblStudentEducation): TblStudentEducation
    {
        $Manager = $this->getEntityManager();

        $Manager->saveEntity($tblStudentEducation);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $tblStudentEducation);

        return $tblStudentEducation;
    }

    /**
     * @param TblStudentEducation $tblStudentEducation
     *
     * @return bool
     */
    public function updateStudentEducation(TblStudentEducation $tblStudentEducation): bool
    {
        $Manager = $this->getEntityManager();
        /** @var TblStudentEducation $Protocol */
        $Protocol = $Manager->getEntityById('TblStudentEducation', $tblStudentEducation->getId());
        if (null !== $Protocol) {
            $Manager->saveEntity($tblStudentEducation);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $tblStudentEducation);

            return true;
        }

        return false;
    }

    /**
     * @param TblStudentEducation $tblStudentEducation
     * @param TblDivisionCourse|null $tblDivision
     * @param $divisionSortOrder
     * @param TblDivisionCourse|null $tblCoreGroup
     * @param $coreGroupSortOrder
     * @param DateTime|null $leaveDate
     *
     * @return bool
     */
    public function updateStudentEducationByProperties(TblStudentEducation $tblStudentEducation, ?TblDivisionCourse $tblDivision, $divisionSortOrder,
        ?TblDivisionCourse $tblCoreGroup, $coreGroupSortOrder, ?DateTime $leaveDate): bool
    {
        $Manager = $this->getEntityManager();
        /** @var TblStudentEducation $Entity */
        $Entity = $Manager->getEntityById('TblStudentEducation', $tblStudentEducation->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setTblDivision($tblDivision);
            $Entity->setDivisionSortOrder($divisionSortOrder);
            $Entity->setTblCoreGroup($tblCoreGroup);
            $Entity->setCoreGroupSortOrder($coreGroupSortOrder);
            $Entity->setLeaveDate($leaveDate);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * zählt die aktiven Schüler einer Klasse
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return int
     */
    public function getCountStudentByDivision(TblDivisionCourse $tblDivisionCourse): int
    {
        if (($tempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
            TblStudentEducation::ATTR_TBL_DIVISION => $tblDivisionCourse->getId(),
            TblStudentEducation::ATTR_LEAVE_DATE => null
        )))) {
            return count($tempList);
        }

        return 0;
    }

    /**
     * zählt die inaktiven Schüler einer Klasse
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return int
     */
    public function getCountInActiveStudentByDivision(TblDivisionCourse $tblDivisionCourse): int
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblStudentEducation', 't')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t.tblDivision', '?1'),
                    $queryBuilder->expr()->isNotNull('t.LeaveDate')
                )
            )
            ->setParameter(1, $tblDivisionCourse->getId())
            ->getQuery();

        $resultList = $query->getResult();

        return count($resultList);

        // funktioniert leider nicht
//        if (($tempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
//            TblStudentEducation::ATTR_TBL_DIVISION => $tblDivisionCourse->getId(),
//            TblStudentEducation::ATTR_LEAVE_DATE => !null
//        )))) {
//            return count($tempList);
//        }
//
//        return 0;
    }

    /**
     * zählt die aktiven Schüler einer Stammgruppe
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return int
     */
    public function getCountStudentByCoreGroup(TblDivisionCourse $tblDivisionCourse): int
    {
        if (($tempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblStudentEducation', array(
            TblStudentEducation::ATTR_TBL_CORE_GROUP => $tblDivisionCourse->getId(),
            TblStudentEducation::ATTR_LEAVE_DATE => null
        )))) {
            return count($tempList);
        }

        return 0;
    }

    /**
     * zählt die inaktiven Schüler einer Stammgruppe
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return int
     */
    public function getCountInActiveStudentByCoreGroup(TblDivisionCourse $tblDivisionCourse): int
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblStudentEducation', 't')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t.tblCoreGroup', '?1'),
                    $queryBuilder->expr()->isNotNull('t.LeaveDate')
                )
            )
            ->setParameter(1, $tblDivisionCourse->getId())
            ->getQuery();

        $resultList = $query->getResult();

        return count($resultList);
    }

    /**
     * zählt die aktiven Schüler einer Unterrichtsgruppe
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return int
     */
    public function getCountStudentByDivisionCourse(TblDivisionCourse $tblDivisionCourse): int
    {
        if (($tempList = $this->getCachedEntityListBy(__Method__, $this->getConnection()->getEntityManager(), 'TblDivisionCourseMember', array(
            TblDivisionCourseMember::ATTR_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
            TblDivisionCourseMember::ATTR_TBL_MEMBER_TYPE => $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT),
            TblDivisionCourseMember::ATTR_LEAVE_DATE => null
        )))) {
            return count($tempList);
        }

        return 0;
    }

    /**
     * zählt die inaktiven Schüler einer Unterrichtsgruppe
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return int
     */
    public function getCountInActiveStudentByDivisionCourse(TblDivisionCourse $tblDivisionCourse): int
    {
        $Manager = $this->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();

        $query = $queryBuilder->select('t')
            ->from(__NAMESPACE__ . '\Entity\TblDivisionCourseMember', 't')
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('t.tblLessonDivisionCourse', '?1'),
                    $queryBuilder->expr()->eq('t.tblLessonDivisionCourseMemberType', '?2'),
                    $queryBuilder->expr()->isNotNull('t.LeaveDate')
                )
            )
            ->setParameter(1, $tblDivisionCourse->getId())
            ->setParameter(2, $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT)->getId())
            ->getQuery();

        $resultList = $query->getResult();

        return count($resultList);
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseMember
     */
    public function getDivisionCourseMemberById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseMember', $Id);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourseMemberType $tblMemberType
     *
     * @return TblDivisionCourseMember[]|false
     */
    public function getDivisionCourseMemberListBy(TblDivisionCourse $tblDivisionCourse, TblDivisionCourseMemberType $tblMemberType)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseMember', array(
            TblDivisionCourseMember::ATTR_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
            TblDivisionCourseMember::ATTR_TBL_MEMBER_TYPE => $tblMemberType->getId()
        ));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourseMemberType $tblMemberType
     * @param TblPerson $tblPerson
     *
     * @return false|TblDivisionCourseMember
     */
    public function getDivisionCourseMemberByPerson(TblDivisionCourse $tblDivisionCourse, TblDivisionCourseMemberType $tblMemberType, TblPerson $tblPerson)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseMember', array(
            TblDivisionCourseMember::ATTR_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
            TblDivisionCourseMember::ATTR_TBL_MEMBER_TYPE => $tblMemberType->getId(),
            TblDivisionCourseMember::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourseMemberType $tblMemberType
     * @param TblPerson $tblPerson
     * @param string $description
     *
     * @return TblDivisionCourseMember
     */
    public function addDivisionCourseMemberToDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblDivisionCourseMemberType $tblMemberType,
        TblPerson $tblPerson, string $description = ''): TblDivisionCourseMember
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivisionCourseMember')
            ->findOneBy(array(
                TblDivisionCourseMember::ATTR_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
                TblDivisionCourseMember::ATTR_TBL_MEMBER_TYPE => $tblMemberType->getId(),
                TblDivisionCourseMember::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));

        if (null === $Entity) {
            $Entity = TblDivisionCourseMember::withParameter($tblDivisionCourse, $tblMemberType, $tblPerson, $description);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblDivisionCourseMember $tblDivisionCourseMember
     *
     * @return bool
     */
    public function removeDivisionCourseMemberFromDivisionCourse(TblDivisionCourseMember $tblDivisionCourseMember): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblDivisionCourse $Entity */
        $Entity = $Manager->getEntityById('TblDivisionCourseMember', $tblDivisionCourseMember->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return bool
     */
    public function removeDivisionCourseMemberAllFromDivisionCourse(TblDivisionCourse $tblDivisionCourse): bool
    {
        $Manager = $this->getConnection()->getEntityManager();
        $EntityList = $Manager->getEntity('TblDivisionCourseMember')
            ->findBy(array(
                TblDivisionCourseMember::ATTR_TBL_DIVISION_COURSE => $tblDivisionCourse->getId(),
            ));
        if ($EntityList) {
            foreach ($EntityList as $Entity) {
                $Manager->bulkKillEntity($Entity);
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity, true);
            }
            $Manager->flushCache();
            Protocol::useService()->flushBulkEntries();

            return true;
        }

        return false;
    }

    /**
     * @param array $tblDivisionCourseMemberList
     *
     * @return bool
     */
    public function updateDivisionCourseMemberBulk(array $tblDivisionCourseMemberList): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        foreach ($tblDivisionCourseMemberList as $tblDivisionCourseMember) {
            $Manager->bulkSaveEntity($tblDivisionCourseMember);
            /** @var TblDivisionCourseMember $Entity */
            $Entity = $Manager->getEntityById('TblDivisionCourseMember', $tblDivisionCourseMember->getId());
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Entity, $tblDivisionCourseMember, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }

    /**
     * @param array $tblStudentEducationList
     *
     * @return bool
     */
    public function updateStudentEducationBulk(array $tblStudentEducationList): bool
    {
        $Manager = $this->getConnection()->getEntityManager();

        foreach ($tblStudentEducationList as $tblStudentEducation) {
            $Manager->bulkSaveEntity($tblStudentEducation);
            /** @var TblStudentEducation $Entity */
            $Entity = $Manager->getEntityById('TblStudentEducation', $tblStudentEducation->getId());
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Entity, $tblStudentEducation, true);
        }

        $Manager->flushCache();
        Protocol::useService()->flushBulkEntries();

        return true;
    }
}