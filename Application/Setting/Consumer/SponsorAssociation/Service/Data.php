<?php
namespace SPHERE\Application\Setting\Consumer\SponsorAssociation\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Setting\Consumer\SponsorAssociation\Service\Entity\TblSponsorAssociation;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Setting\Consumer\SponsorAssociation\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param integer $Id
     *
     * @return bool|TblSponsorAssociation
     */
    public function getSponsorAssociationById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblSponsorAssociation', $Id);

        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblSponsorAssociation[]
     */
    public function getSponsorAssociationAll()
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblSponsorAssociation')->findAll();

        return ( empty ( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return TblSponsorAssociation|bool
     */
    public function addSponsorAssociation(TblCompany $tblCompany)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblSponsorAssociation')
            ->findOneBy(array(
                TblSponsorAssociation::SERVICE_TBL_COMPANY => $tblCompany->getId(),
            ));
        if (null === $Entity) {
            $Entity = new TblSponsorAssociation();
            $Entity->setServiceTblCompany($tblCompany);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
            return $Entity;
        }
        return false;
    }

    /**
     * @param TblSponsorAssociation $tblSponsorAssociation
     *
     * @return bool
     */
    public function removeSponsorAssociation(TblSponsorAssociation $tblSponsorAssociation)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSponsorAssociation $Entity */
        $Entity = $Manager->getEntityById('TblSponsorAssociation', $tblSponsorAssociation->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);

            return true;
        }

        return false;
    }
}
