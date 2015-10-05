<?php
namespace SPHERE\Application\Corporation\Company\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Cacheable;

/**
 * Class Data
 *
 * @package SPHERE\Application\Corporation\Company\Service
 */
class Data extends Cacheable
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    function __construct(Binding $Connection)
    {

        $this->Connection = $Connection;
    }

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

        $Manager = $this->Connection->getEntityManager();
        $Entity = new TblCompany();
        $Entity->setName($Name);
        $Entity->setDescription($Description);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
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

        $Manager = $this->Connection->getEntityManager();
        /** @var TblCompany $Entity */
        $Entity = $Manager->getEntityById('TblCompany', $tblCompany->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @return bool|TblCompany[]
     */
    public function getCompanyAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->Connection->getEntityManager(), 'TblCompany');
    }

    /**
     * @return int
     */
    public function countCompanyAll()
    {

        return $this->Connection->getEntityManager()->getEntity('TblCompany')->count();
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblCompany
     */
    public function getCompanyById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblCompany', $Id);
        return ( null === $Entity ? false : $Entity );
    }
}
