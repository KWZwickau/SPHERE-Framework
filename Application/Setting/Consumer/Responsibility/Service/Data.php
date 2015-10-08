<?php
namespace SPHERE\Application\Setting\Consumer\Responsibility\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity\TblResponsibility;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Extension\Extension;

/**
 * Class Data
 *
 * @package SPHERE\Application\Setting\Consumer\Responsibility\Service
 */
class Data extends Extension
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
     * @param integer $Id
     *
     * @return bool|TblResponsibility
     */
    public function getResponsibilityById($Id)
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById('TblResponsibility', $Id);

        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblResponsibility[]
     */
    public function getResponsibilityAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity('TblResponsibility')->findAll();

        return ( empty ( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return TblResponsibility|bool
     */
    public function addResponsibility(TblCompany $tblCompany)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblResponsibility')
            ->findOneBy(array(
                TblResponsibility::SERVICE_TBL_COMPANY => $tblCompany->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblResponsibility();
            $Entity->setServiceTblCompany($tblCompany);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);

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

        $Manager = $this->Connection->getEntityManager();
        /** @var TblResponsibility $Entity */
        $Entity = $Manager->getEntityById('TblResponsibility', $tblResponsibility->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->Connection->getDatabase(), $Entity);
            $Manager->killEntity($Entity);

            return true;
        }

        return false;
    }
}
