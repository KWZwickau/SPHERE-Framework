<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseLink;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
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
        $this->createDivisionCourseMemberType('Klassenlehrer', TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER);
        $this->createDivisionCourseMemberType('Tudor/Mentor', TblDivisionCourseMemberType::TYPE_TUDOR);
        $this->createDivisionCourseMemberType('Elternvertreter', TblDivisionCourseMemberType::TYPE_CUSTODY);
        $this->createDivisionCourseMemberType('Klassensprecher', TblDivisionCourseMemberType::TYPE_REPRESENTATIVE);

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
    public function getMemberTypeById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseMemberType', $Id);
    }

    /**
     * @param string $Identifier
     *
     * @return false|TblDivisionCourseMemberType
     */
    public function getMemberTypeByIdentifier(string $Identifier)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseMemberType',
            array(TblDivisionCourseMemberType::ATTR_IDENTIFIER => strtoupper($Identifier)));
    }
}