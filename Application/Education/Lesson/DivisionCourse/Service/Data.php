<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

class Data  extends AbstractData
{
    public function setupDatabaseContent()
    {
        $this->createDivisionCourseType('Klasse', 'DIVISION');
        $this->createDivisionCourseType('Stammgruppe', 'CORE_GROUP');
        $this->createDivisionCourseType('Unterrichtsgruppe', 'TEACHING_GROUP');

        $this->createDivisionCourseMemberType('Schüler', 'STUDENT');
        $this->createDivisionCourseMemberType('Klassenlehrer', 'TEACHER');
        $this->createDivisionCourseMemberType('Tudor/Mentor', 'TUDOR');
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
     * @param $Id
     *
     * @return false|TblDivisionCourseType
     */
    public function getDivisionCourseTypeById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblDivisionCourseType', $Id);
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
}