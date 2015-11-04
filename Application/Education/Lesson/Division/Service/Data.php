<?php
namespace SPHERE\Application\Education\Lesson\Division\Service;

use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

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
     * @param string $Name
     * @param string $Description
     *
     * @return TblLevel
     */
    public function createLevel(TblType $tblType, $Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblLevel')->findOneBy(array(
            TblLevel::ATTR_NAME => $Name,
            TblLevel::SERVICE_TBL_TYPE => $tblType->getId()
        ));
        if (null === $Entity) {
            $Entity = new TblLevel();
            $Entity->setServiceTblType($tblType);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
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
     * @param TblType $tblType
     * @param string $Name
     *
     * @return bool
     */
    public function checkLevelExists(TblType $tblType, $Name)
    {

        $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblLevel', array(
            TblLevel::ATTR_NAME => $Name,
            TblLevel::SERVICE_TBL_TYPE => $tblType->getId()
        ));
        return ($Entity ? true : false);
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
     * @return bool|TblPerson[]
     */
    public function getStudentAllByDivision(TblDivision $tblDivision)
    {

        $TempList = $this->getConnection()->getEntityManager()->getEntity('TblDivisionStudent')->findBy(array(
            TblDivisionStudent::ATTR_TBL_DIVISION => $tblDivision->getId()
        ));

        $EntityList = array();

        if (!empty ($TempList)) {
            /** @var TblDivisionStudent $tblDivisionStudent */
            foreach ($TempList as $tblDivisionStudent) {
                array_push($EntityList, $tblDivisionStudent->getServiceTblPerson());
            }
        }
        return empty($EntityList) ? false : $EntityList;
    }
}
