<?php
namespace SPHERE\Application\Billing\Accounting\Banking\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblDebtorCommodity")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblDebtorCommodity extends Element
{

    const ATTR_SERVICE_BILLING_COMMODITY = 'serviceBilling_Commodity';
    const ATTR_TBL_DEBTOR = 'tblDebtor';

    /**
     * @Column(type="bigint")
     */
    protected $serviceBilling_Commodity;
    /**
     * @Column(type="bigint")
     */
    protected $tblDebtor;

    /**
     * @return bool|TblCommodity
     */
    public function getServiceBillingCommodity()
    {

        if (null === $this->serviceBilling_Commodity) {
            return false;
        } else {
            return Commodity::useService()->getCommodityById($this->serviceBilling_Commodity);
        }
    }

    /**
     * @param null|TblCommodity $tblCommodity
     */
    public function setServiceBillingCommodity(TblCommodity $tblCommodity)
    {

        $this->serviceBilling_Commodity = ( null === $tblCommodity ? null : $tblCommodity->getId() );
    }

    /**
     * @return bool|TblDebtor
     */
    public function getTblDebtor()
    {

        if (null === $this->tblDebtor) {
            return false;
        } else {
            return Banking::useService()->getDebtorById($this->tblDebtor);
        }
    }

    /**
     * @param null|TblDebtor $tblDebtor
     */
    public function setTblDebtor(TblDebtor $tblDebtor)
    {

        $this->tblDebtor = ( null === $tblDebtor ? null : $tblDebtor->getId() );
    }

}
