<?php
namespace SPHERE\Application\Corporation\Company\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Corporation\Company\Service
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
     * @return TblCompany
     */
    public function createCompany($Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblCompany();
        $Entity->setName($Name);
        $Entity->setDescription($Description);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param TblCompany $tblCompany
     * @param string     $Name
     * @param string     $Description
     *
     * @return TblCompany
     */
    public function updateCompany(
        TblCompany $tblCompany,
        $Name,
        $Description = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblCompany $Entity */
        $Entity = $Manager->getEntityById('TblCompany', $tblCompany->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @return bool|TblCompany[]
     */
    public function getCompanyAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCompany');
    }

    /**
     * @return int
     */
    public function countCompanyAll()
    {

        return $this->getConnection()->getEntityManager()->getEntity('TblCompany')->count();
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblCompany
     */
    public function getCompanyById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblCompany', $Id);
        return ( null === $Entity ? false : $Entity );
    }
}
