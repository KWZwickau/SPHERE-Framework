<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Platform\System\Protocol\Protocol;

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
            return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblDivisionCourse', array(TblDivisionCourse::ATTR_TBL_TYPE => $tblType->getId()));
        } else {
            return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblDivisionCourse');
        }
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