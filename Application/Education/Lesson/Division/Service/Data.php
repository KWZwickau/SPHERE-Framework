<?php
namespace SPHERE\Application\Education\Lesson\Division\Service;

use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionSubject;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionTeacher;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectTeacher;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Lesson\Division\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param TblType $tblType
     * @param string  $Name
     * @param string  $Description
     *
     * @return TblLevel
     */
    public function createLevel(TblType $tblType, $Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblLevel')->findOneBy(array(
            TblLevel::ATTR_NAME        => $Name,
            TblLevel::SERVICE_TBL_TYPE => $tblType->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblLevel();
            $Entity->setServiceTblType($tblType);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
            return $Entity;
        }
        return $Entity;
    }

    /**
     * @param TblYear       $tblYear
     * @param TblLevel|null $tblLevel
     * @param string        $Name
     * @param string        $Description
     *
     * @return null|object|TblDivision
     */
    public function createDivision(TblYear $tblYear, TblLevel $tblLevel = null, $Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        if ($tblLevel === null) {
            $Entity = $Manager->getEntity('TblDivision')->findOneBy(array(
                TblDivision::ATTR_NAME => $Name,
                TblDivision::ATTR_YEAR => $tblYear->getId(),
            ));
        } else {
            $Entity = $Manager->getEntity('TblDivision')->findOneBy(array(
                TblDivision::ATTR_NAME  => $Name,
                TblDivision::ATTR_LEVEL => $tblLevel->getId(),
                TblDivision::ATTR_YEAR  => $tblYear->getId(),
            ));
        }

        if (null === $Entity) {
            $Entity = new TblDivision();
            $Entity->setServiceTblYear($tblYear);
            $Entity->setTblLevel($tblLevel);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
            return $Entity;
        }
        return $Entity;
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return TblSubjectGroup
     */
    public function createSubjectGroup($Name, $Description)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblSubjectGroup')->findOneBy(array(
            TblSubjectGroup::ATTR_NAME        => $Name,
            TblSubjectGroup::ATTR_DESCRIPTION => $Description
        ));
        if (null === $Entity) {
            $Entity = new TblSubjectGroup();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
            return $Entity;
        }
        return $Entity;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblLevel
     */
    public function getLevelById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblLevel', $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblDivision
     */
    public function getDivisionById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivision', $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblSubjectGroup
     */
    public function getSubjectGroupById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubjectGroup', $Id);
    }

    /**
     * @param $Id
     *
     * @return false|Element
     */
    public function getDivisionSubjectById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivisionSubject', $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblSubjectStudent
     */
    public function getSubjectStudentById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubjectStudent', $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblSubjectTeacher
     */
    public function getSubjectTeacherById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubjectTeacher', $Id);
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblDivisionSubject[]
     */
    public function getDivisionSubjectByDivision(TblDivision $tblDivision)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblDivisionSubject')->findBy(array(
            TblDivisionSubject::ATTR_TBL_DIVISION => $tblDivision->getId(),
        ));

        return empty( $EntityList ) ? false : $EntityList;
    }

    public function getDivisionSubjectBySubject(TblSubject $tblSubject, TblDivision $tblDivision)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblDivisionSubject')->findBy(array(
            TblDivisionSubject::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
            TblDivisionSubject::ATTR_TBL_DIVISION        => $tblDivision->getId(),
        ));

        return empty( $EntityList ) ? false : $EntityList;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool|TblSubjectTeacher[]
     */
    public function getSubjectTeacherByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblSubjectTeacher')->findBy(array(
            TblSubjectTeacher::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId()
        ));

        return empty( $EntityList ) ? false : $EntityList;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool|TblSubjectStudent[]
     */
    public function getSubjectStudentByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblSubjectStudent')->findBy(array(
            TblSubjectStudent::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId()
        ));

        return empty( $EntityList ) ? false : $EntityList;
    }

    /**
     * @param TblLevel $tblLevel
     *
     * @return bool|TblDivision[]
     */
    public function getDivisionByLevel(TblLevel $tblLevel)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblDivision')->findBy(array(
            TblDivision::ATTR_LEVEL => $tblLevel->getId()
        ));

        return empty( $EntityList ) ? false : $EntityList;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return bool|TblDivision[]
     */
    public function getDivisionByYear(TblYear $tblYear)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblDivision')->findBy(array(
            TblDivision::ATTR_YEAR => $tblYear->getId()
        ));

        return empty( $EntityList ) ? false : $EntityList;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblSubjectStudent[]
     */
    public function getSubjectStudentByPerson(TblPerson $tblPerson)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblSubjectStudent')->findBy(array(
            TblSubjectStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
        ));

        return empty( $EntityList ) ? false : $EntityList;
    }

    /**
     * @param               $Name
     * @param TblLevel|null $tblLevel
     * @param TblYear       $tblYear
     *
     * @return bool|false|Element
     */
    public function getDivisionByGroupAndLevelAndYear($Name, TblLevel $tblLevel = null, TblYear $tblYear)
    {

        if ($tblLevel === null) {
            $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivision', array(
                TblDivision::ATTR_NAME => $Name,
                TblDivision::ATTR_YEAR => $tblYear->getId(),
            ));
        } else {
            $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivision', array(
                TblDivision::ATTR_NAME  => $Name,
                TblDivision::ATTR_LEVEL => $tblLevel->getId(),
                TblDivision::ATTR_YEAR  => $tblYear->getId(),
            ));
        }

        return ( $Entity ? $Entity : false );
    }

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return bool|TblLevel
     */
    public function checkSubjectExists($Name, $Description = '')
    {

        $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblLevel', array(
            TblSubjectGroup::ATTR_NAME        => $Name,
            TblSubjectGroup::ATTR_DESCRIPTION => $Description
        ));
        return ( $Entity ? $Entity : false );
    }

    /**
     * @param TblType $tblType
     * @param string  $Name
     *
     * @return bool|TblLevel
     */
    public function checkLevelExists(TblType $tblType, $Name)
    {

        $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblLevel', array(
            TblLevel::ATTR_NAME        => $Name,
            TblLevel::SERVICE_TBL_TYPE => $tblType->getId()
        ));
        return ( $Entity ? $Entity : false );
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return bool|TblSubjectGroup
     */
    public function checkSubjectGroupExists($Name, $Description)
    {

        $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubjectGroup', array(
            TblSubjectGroup::ATTR_NAME        => $Name,
            TblSubjectGroup::ATTR_DESCRIPTION => $Description
        ));
        return ( $Entity ? $Entity : false );
    }

    /**
     * @return bool|TblLevel[]
     */
    public function getLevelAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblLevel');
    }

    /**
     * @return bool|TblDivision[]
     */
    public function getDivisionAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivision');
    }

    /**
     * @return false|TblSubjectGroup[]
     */
    public function getSubjectGroupAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSubjectGroup');
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblPerson[]
     */
    public function getStudentAllByDivision(TblDivision $tblDivision)
    {

        $TempList = $this->getConnection()->getEntityManager()->getEntity('TblDivisionStudent')->findBy(array(
            TblDivisionStudent::ATTR_TBL_DIVISION => $tblDivision->getId()
        ));

        $EntityList = array();

        if (!empty ( $TempList )) {
            /** @var TblDivisionStudent $tblDivisionStudent */
            foreach ($TempList as $tblDivisionStudent) {
                array_push($EntityList, $tblDivisionStudent->getServiceTblPerson());
            }
        }
        return empty( $EntityList ) ? false : $EntityList;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblPerson[]
     */
    public function getTeacherAllByDivision(TblDivision $tblDivision)
    {

        $TempList = $this->getConnection()->getEntityManager()->getEntity('TblDivisionTeacher')->findBy(array(
            TblDivisionTeacher::ATTR_TBL_DIVISION => $tblDivision->getId()
        ));
        $EntityList = array();

        if (!empty ( $TempList )) {
            /** @var TblDivisionTeacher $tblDivisionTeacher */
            foreach ($TempList as $tblDivisionTeacher) {
                array_push($EntityList, $tblDivisionTeacher->getServiceTblPerson());
            }
        }
        return empty( $EntityList ) ? false : $EntityList;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool|TblSubjectTeacher[]
     */
    public function getTeacherAllByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        $TempList = $this->getConnection()->getEntityManager()->getEntity('TblSubjectTeacher')->findBy(array(
            TblSubjectTeacher::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId()
        ));
        $EntityList = array();

        if (!empty ( $TempList )) {
            /** @var TblSubjectTeacher $tblSubjectTeacher */
            foreach ($TempList as $tblSubjectTeacher) {
                array_push($EntityList, $tblSubjectTeacher->getServiceTblPerson());
            }
        }
        return empty( $EntityList ) ? false : $EntityList;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|TblSubject[]
     */
    public function getSubjectAllByDivision(TblDivision $tblDivision)
    {

        $TempList = $this->getConnection()->getEntityManager()->getEntity('TblDivisionSubject')->findBy(array(
            TblDivisionSubject::ATTR_TBL_DIVISION => $tblDivision->getId()
        ));
        $EntityList = array();

        if (!empty ( $TempList )) {
            /** @var TblDivisionSubject $tblDivisionSubject */
            foreach ($TempList as $tblDivisionSubject) {
                if (!$tblDivisionSubject->getTblSubjectGroup()) {
                    array_push($EntityList, $tblDivisionSubject->getServiceTblSubject());
                }
            }
        }
        return empty( $EntityList ) ? false : $EntityList;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson   $tblPerson
     *
     * @return TblDivisionStudent
     */
    public function addDivisionStudent(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivisionStudent')
            ->findOneBy(array(
                TblDivisionStudent::ATTR_TBL_DIVISION       => $tblDivision->getId(),
                TblDivisionStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblDivisionStudent();
            $Entity->setTblDivision($tblDivision);
            $Entity->setServiceTblPerson($tblPerson);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson   $tblPerson
     *
     * @return null|object|TblDivisionTeacher
     */
    public function addDivisionTeacher(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivisionTeacher')
            ->findOneBy(array(
                TblDivisionTeacher::ATTR_TBL_DIVISION       => $tblDivision->getId(),
                TblDivisionTeacher::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblDivisionTeacher();
            $Entity->setTblDivision($tblDivision);
            $Entity->setServiceTblPerson($tblPerson);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblDivision          $tblDivision
     * @param TblSubject           $tblSubject
     * @param TblSubjectGroup|null $tblSubjectGroup
     *
     * @return null|object|TblDivisionSubject
     */
    public function addDivisionSubject(TblDivision $tblDivision, TblSubject $tblSubject, TblSubjectGroup $tblSubjectGroup = null)
    {

        $Manager = $this->getConnection()->getEntityManager();
        if ($tblSubjectGroup === null) {
            $Entity = $Manager->getEntity('TblDivisionSubject')
                ->findOneBy(array(
                    TblDivisionSubject::ATTR_TBL_DIVISION        => $tblDivision->getId(),
                    TblDivisionSubject::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId()
                ));
        } else {
            $Entity = $Manager->getEntity('TblDivisionSubject')
                ->findOneBy(array(
                    TblDivisionSubject::ATTR_TBL_DIVISION        => $tblDivision->getId(),
                    TblDivisionSubject::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId(),
                    TblDivisionSubject::ATTR_TBL_SUBJECT_GROUP   => $tblSubjectGroup->getId()
                ));
        }

        if (null === $Entity) {
            $Entity = new TblDivisionSubject();
            $Entity->setTblDivision($tblDivision);
            $Entity->setServiceTblSubject($tblSubject);
            $Entity->setTblSubjectGroup($tblSubjectGroup);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblPerson          $tblPerson
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return null|object|TblSubjectStudent
     */
    public function addSubjectStudent(TblPerson $tblPerson, TblDivisionSubject $tblDivisionSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblSubjectStudent')
            ->findOneBy(array(
                TblSubjectStudent::ATTR_SERVICE_TBL_PERSON   => $tblPerson->getId(),
                TblSubjectStudent::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblSubjectStudent();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setTblDivisionSubject($tblDivisionSubject);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     * @param TblPerson          $tblPerson
     *
     * @return null|object|TblSubjectTeacher
     */
    public function addSubjectTeacher(TblDivisionSubject $tblDivisionSubject, TblPerson $tblPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblSubjectTeacher')
            ->findOneBy(array(
                TblSubjectTeacher::ATTR_SERVICE_TBL_PERSON   => $tblPerson->getId(),
                TblSubjectTeacher::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId(),
            ));

        if (null === $Entity) {
            $Entity = new TblSubjectTeacher();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setTblDivisionSubject($tblDivisionSubject);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson   $tblPerson
     *
     * @return bool
     */
    public function removeStudentToDivision(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivisionStudent')
            ->findOneBy(array(
                TblDivisionStudent::ATTR_TBL_DIVISION       => $tblDivision->getId(),
                TblDivisionStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblPerson   $tblPerson
     *
     * @return bool
     */
    public function removeTeacherToDivision(TblDivision $tblDivision, TblPerson $tblPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivisionTeacher')
            ->findOneBy(array(
                TblDivisionTeacher::ATTR_TBL_DIVISION       => $tblDivision->getId(),
                TblDivisionTeacher::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDivision $tblDivision
     * @param TblSubject  $tblSubject
     *
     * @return bool
     */
    public function removeSubjectToDivision(TblDivision $tblDivision, TblSubject $tblSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivisionSubject')
            ->findOneBy(array(
                TblDivisionSubject::ATTR_TBL_DIVISION        => $tblDivision->getId(),
                TblDivisionSubject::ATTR_SERVICE_TBL_SUBJECT => $tblSubject->getId()
            ));
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function removeDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblDivisionSubject', $tblDivisionSubject->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSubjectStudent $tblSubjectStudent
     *
     * @return bool
     */
    public function removeSubjectStudent(TblSubjectStudent $tblSubjectStudent)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblSubjectStudent', $tblSubjectStudent->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function removeSubjectStudentByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $EntityList = $Manager->getEntity('TblSubjectStudent')->findBy(
            array(TblSubjectStudent::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId()));
        if ($EntityList) {
            foreach ($EntityList as $Entity) {
                /** @var Element $Entity */
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
            }
            return true;
        }
        return false;
    }

    /**
     * @param TblDivisionSubject $tblDivisionSubject
     *
     * @return bool
     */
    public function removeSubjectTeacherByDivisionSubject(TblDivisionSubject $tblDivisionSubject)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $EntityList = $Manager->getEntity('TblSubjectTeacher')->findBy(
            array(TblSubjectTeacher::ATTR_TBL_DIVISION_SUBJECT => $tblDivisionSubject->getId()));
        if ($EntityList) {
            foreach ($EntityList as $Entity) {
                /** @var Element $Entity */
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
            }
            return true;
        }
        return false;
    }

    /**
     * @param TblSubjectTeacher $tblSubjectTeacher
     *
     * @return bool
     */
    public function removeSubjectTeacher(TblSubjectTeacher $tblSubjectTeacher)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblSubjectTeacher', $tblSubjectTeacher->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSubjectGroup $tblSubjectGroup
     *
     * @return bool
     */
    public function removeSubjectGroup(TblSubjectGroup $tblSubjectGroup)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblSubjectGroup', $tblSubjectGroup->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDivision $tblDivision
     * @param string      $Description
     *
     * @return bool
     */
    public function updateDivision(TblDivision $tblDivision, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblDivision $Entity */
        $Entity = $Manager->getEntityById('TblDivision', $tblDivision->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblLevel $tblLevel
     * @param TblType  $tblType
     * @param string   $Name
     * @param string   $Description
     *
     * @return bool
     */
    public function updateLevel(TblLevel $tblLevel, TblType $tblType, $Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblLevel $Entity */
        $Entity = $Manager->getEntityById('TblLevel', $tblLevel->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblType($tblType);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSubjectGroup $tblSubjectGroup
     * @param string          $Name
     * @param string          $Description
     *
     * @return bool
     */
    public function updateSubjectGroup(TblSubjectGroup $tblSubjectGroup, $Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblSubjectGroup $Entity */
        $Entity = $Manager->getEntityById('TblSubjectGroup', $tblSubjectGroup->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool
     */
    public function destroyDivision(TblDivision $tblDivision)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivision')->findOneBy(array('Id' => $tblDivision->getId()));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblLevel $tblLevel
     *
     * @return bool
     */
    public function destroyLevel(TblLevel $tblLevel)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblLevel')->findOneBy(array('Id' => $tblLevel->getId()));
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblSubjectGroup $tblSubjectGroup
     *
     * @return bool
     */
    public function destroySubjectGroup(TblSubjectGroup $tblSubjectGroup)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblSubjectGroup', $tblSubjectGroup->getId());
        if (null !== $Entity) {
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblDivisionStudent[]
     */
    public function getDivisionStudentAllByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDivisionStudent', array(
                TblDivisionStudent::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
    }
}
