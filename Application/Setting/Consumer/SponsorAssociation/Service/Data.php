<?php
namespace SPHERE\Application\Setting\Consumer\SponsorAssociation\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Setting\Consumer\SponsorAssociation\Service\Entity\TblSponsorAssociation;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Extension\Extension;

/**
 * Class Data
 *
 * @package SPHERE\Application\Setting\Consumer\SponsorAssociation\Service
 */
class Data extends Extension
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    function __construct( Binding $Connection )
    {

        $this->Connection = $Connection;
    }

    public function setupDatabaseContent()
    {

    }

    /**
     * @param integer $Id
     * @return bool|TblSponsorAssociation
     */
    public function getSponsorAssociationById( $Id )
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblSponsorAssociation', $Id );

        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblSponsorAssociation[]
     */
    public function getSponsorAssociationAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblSponsorAssociation' )->findAll();

        return ( empty ( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return TblSponsorAssociation|bool
     */
    public function addSponsorAssociation( TblCompany $tblCompany )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblSponsorAssociation' )
            ->findOneBy( array(
                TblSponsorAssociation::SERVICE_TBL_COMPANY => $tblCompany->getId(),
            ) );
        if (null === $Entity) {
            $Entity = new TblSponsorAssociation();
            $Entity->setServiceTblCompany( $tblCompany );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
            return $Entity;
        }
        return false;
    }

    /**
     * @param TblSponsorAssociation $tblSponsorAssociation
     *
     * @return bool
     */
    public function removeSponsorAssociation( TblSponsorAssociation $tblSponsorAssociation )
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblSponsorAssociation $Entity */
        $Entity = $Manager->getEntityById( 'TblSponsorAssociation', $tblSponsorAssociation->getId() );
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(), $Entity );
            $Manager->killEntity( $Entity );

            return true;
        }

        return false;
    }
}
