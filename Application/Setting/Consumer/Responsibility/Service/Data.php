<?php
namespace SPHERE\Application\Setting\Consumer\Responsibility\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity\TblResponsibility;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Setting\Consumer\Responsibility\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param integer $Id
     *
     * @return bool|TblResponsibility
     */
    public function getResponsibilityById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblResponsibility',
            $Id);
    }

    /**
     * @return bool|TblResponsibility[]
     */
    public function getResponsibilityAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblResponsibility');
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return TblResponsibility|bool
     */
    public function addResponsibility(TblCompany $tblCompany)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblResponsibility')
            ->findOneBy(array(
                TblResponsibility::SERVICE_TBL_COMPANY => $tblCompany->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblResponsibility();
            $Entity->setServiceTblCompany($tblCompany);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

            return $Entity;
        }

        return false;
    }

    /**
     * @param TblResponsibility $tblResponsibility
     *
     * @return bool
     */
    public function removeResponsibility(TblResponsibility $tblResponsibility)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblResponsibility $Entity */
        $Entity = $Manager->getEntityById('TblResponsibility', $tblResponsibility->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);

            return true;
        }

        return false;
    }
}
