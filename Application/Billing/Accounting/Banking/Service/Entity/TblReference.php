<?php
namespace SPHERE\Application\Billing\Accounting\Banking\Service\Entity;

use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblReference")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblReference extends Element
{

    const ATTR_TBL_DEBTOR = "tblDebtor";
    const ATTR_SERVICE_BILLING_COMMODITY = "serviceBilling_Commodity";
    const ATTR_IS_VOID = "IsVoid";
    const ATTR_REFERENCE = "Reference";

    /**
     * @Column(type="string")
     */
    protected $Reference;
    /**
     * @Column(type="boolean")
     */
    protected $IsVoid;
    /**
     * @Column(type="date")
     */
    protected $ReferenceDate;
    /**
     * @Column(type="bigint")
     */
    protected $tblDebtor;
    /**
     * @Column(type="bigint")
     */
    protected $serviceBilling_Commodity;

    /**
     * @return string $Reference
     */
    public function getReference()
    {

        return $this->Reference;
    }

    /**
     * @param string $Reference
     */
    public function setReference( $Reference )
    {

        $this->Reference = $Reference;
    }

    /**
     * @return boolean $IsVoid
     */
    public function getIsVoid()
    {

        return $this->IsVoid;
    }

    /**
     * @param boolean $IsVoid
     */
    public function setIsVoid( $IsVoid )
    {

        $this->IsVoid = $IsVoid;
    }

    /**
     * @return string
     */
    public function getReferenceDate()
    {

        if ( null === $this->ReferenceDate ) {
            return false;
        }
        /** @var \DateTime $ReferenceDate */
        $ReferenceDate = $this->ReferenceDate;
        if ( $ReferenceDate instanceof \DateTime ) {
            return $ReferenceDate->format( 'd.m.Y' );
        } else {
            return (string)$ReferenceDate;
        }
    }

    /**
     * @param \DateTime $ReferenceDate
     */
    public function setReferenceDate( \DateTime $ReferenceDate )
    {

        $this->ReferenceDate = $ReferenceDate;
    }

    /**
     * @return bool|TblDebtor
     */
    public function getServiceBillingBanking()
    {

        if ( null === $this->tblDebtor ) {
            return false;
        } else {
            return Banking::useService()->entityDebtorById( $this->tblDebtor );
        }
    }

    /**
     * @param null|TblDebtor $serviceTblDebtor
     */
    public function setServiceTblDebtor( TblDebtor $serviceTblDebtor )
    {

        $this->tblDebtor = ( null === $serviceTblDebtor ? null : $serviceTblDebtor->getId() );
    }

    /**
     * @return bool|TblCommodity
     */
    public function getServiceBillingCommodity()
    {

        if ( null === $this->serviceBilling_Commodity ) {
            return false;
        } else {
            return Commodity::useService()->entityCommodityById( $this->serviceBilling_Commodity );
        }
    }

    /**
     * @param null|TblCommodity $tblCommodity
     */
    public function setServiceBillingCommodity( TblCommodity $tblCommodity )
    {

        $this->serviceBilling_Commodity = ( null === $tblCommodity ? null : $tblCommodity->getId() );
    }
}