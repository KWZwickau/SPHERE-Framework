<?php
namespace SPHERE\Application\Education\Lesson\Term\Service;

use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYearPeriod;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Education\Lesson\Term\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return TblYear
     */
    public function createYear($Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblYear')->findOneBy(array(
            TblYear::ATTR_NAME => $Name
        ));
        if (null === $Entity) {
            $Entity = new TblYear();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string $Name
     * @param string $From
     * @param string $To
     * @param string $Description
     *
     * @return TblPeriod
     */
    public function createPeriod($Name, $From, $To, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblPeriod')->findOneBy(array(
            TblPeriod::ATTR_NAME => $Name
        ));
        if (null === $Entity) {
            $Entity = new TblPeriod();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setFromDate(new \DateTime($From));
            $Entity->setToDate(new \DateTime($To));
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblYear   $tblYear
     * @param TblPeriod $tblPeriod
     *
     * @return TblYearPeriod
     */
    public function addYearPeriod(TblYear $tblYear, TblPeriod $tblPeriod)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblYearPeriod')
            ->findOneBy(array(
                TblYearPeriod::ATTR_TBL_YEAR   => $tblYear->getId(),
                TblYearPeriod::ATTR_TBL_PERIOD => $tblPeriod->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblYearPeriod();
            $Entity->setTblYear($tblYear);
            $Entity->setTblPeriod($tblPeriod);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblYear   $tblYear
     * @param TblPeriod $tblPeriod
     *
     * @return bool
     */
    public function removeYearPeriod(TblYear $tblYear, TblPeriod $tblPeriod)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblYearPeriod $Entity */
        $Entity = $Manager->getEntity('TblYearPeriod')
            ->findOneBy(array(
                TblYearPeriod::ATTR_TBL_YEAR   => $tblYear->getId(),
                TblYearPeriod::ATTR_TBL_PERIOD => $tblPeriod->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return bool|TblPeriod[]
     */
    public function getPeriodAllByYear(TblYear $tblYear)
    {

        /** @var TblYearPeriod[] $EntityList */
        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblYearPeriod')->findBy(array(
            TblYearPeriod::ATTR_TBL_YEAR => $tblYear->getId()
        ));
        array_walk($EntityList, function (TblYearPeriod &$V) {

            $V = $V->getTblPeriod();
        });
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @return bool|TblYear[]
     */
    public function getYearAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblYear');
    }

    /**
     * @return bool|TblPeriod[]
     */
    public function getPeriodAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPeriod');
    }

    /**
     * @param string $Name
     *
     * @return bool|TblYear
     */
    public function getYearByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblYear', array(
            TblYear::ATTR_NAME => $Name
        ));
    }

    /**
     * @param string $Name
     *
     * @return bool|TblPeriod
     */
    public function getPeriodByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPeriod', array(
            TblPeriod::ATTR_NAME => $Name
        ));
    }

    /**
     * @param int $Id
     *
     * @return bool|TblYear
     */
    public function getYearById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblYear', $Id);
    }
}
