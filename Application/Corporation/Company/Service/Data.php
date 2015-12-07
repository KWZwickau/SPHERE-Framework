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
        $Entity = $Manager->getEntity('TblCompany')->findOneBy(array(
            TblCompany::ATTR_NAME => $Name,
        ));
        if (null === $Entity) {
            $Entity = new TblCompany();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblCompany $tblCompany
     * @param string $Name
     * @param string $Description
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

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCompany', $Id);
    }

    /**
     * @param string $Description
     *
     * @return bool|TblCompany
     */
    public function getCompanyByDescription($Description)
    {

        $list = $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblCompany',
            array(TblCompany::ATTR_DESCRIPTION => $Description));

        if ($list){
            if (count($list) === 1){
                return $list[0];
            }
        }

        return false;
    }
}
