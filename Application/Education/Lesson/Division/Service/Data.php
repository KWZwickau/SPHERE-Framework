<?php
namespace SPHERE\Application\Education\Lesson\Division\Service;

use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionTeacher;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
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
        return false;
    }

    /**
     * @param TblYear  $tblYear
     * @param TblLevel $tblLevel
     * @param          $Name
     * @param string   $Description
     *
     * @return null|object|TblDivision
     */
    public function createDivision(TblYear $tblYear, TblLevel $tblLevel, $Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDivision')->findOneBy(array(
            TblDivision::ATTR_NAME  => $Name,
            TblDivision::ATTR_LEVEL => $tblLevel->getId(),
            TblDivision::ATTR_YEAR  => $tblYear->getId(),
        ));
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
        return false;
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
     * @param $Name
     * @param $tblLevel
     *
     * @return bool|TblDivision
     */
    public function getDivisionByGroupAndLevelAndYear($Name, TblLevel $tblLevel, TblYear $tblYear)
    {

        $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDivision', array(
            TblDivision::ATTR_NAME  => $Name,
            TblDivision::ATTR_LEVEL => $tblLevel->getId(),
            TblDivision::ATTR_YEAR  => $tblYear->getId(),
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
     * @param TblDivision $tblDivision
     * @param TblYear     $Year
     * @param TblLevel    $Level
     * @param string      $Name
     * @param string      $Description
     *
     * @return bool
     */
    public function updateDivision(TblDivision $tblDivision, TblYear $Year, TblLevel $Level, $Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblDivision $Entity */
        $Entity = $Manager->getEntityById('TblDivision', $tblDivision->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblYear($Year);
            $Entity->setTblLevel($Level);
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

    /***
     * @param TblLevel $tblLevel
     * @param TblType  $tblType
     * @param          $Name
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
}
