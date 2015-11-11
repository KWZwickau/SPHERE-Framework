<?php
namespace SPHERE\Application\Billing\Accounting\Basket\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Accounting\Basket\Basket;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBasketCommodityDebtor")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblBasketCommodityDebtor extends Element
{

    const ATTR_TBL_BASKET_COMMODITY = 'tblBasketCommodity';
    const ATTR_SERVICE_BILLING_DEBTOR = 'serviceBilling_Debtor';

    /**
     * @Column(type="bigint")
     */
    protected $tblBasketCommodity;
    /**
     * @Column(type="bigint")
     */
    protected $serviceBilling_Debtor;

    /**
     * @return bool|TblBasketCommodity
     */
    public function getTblBasketCommodity()
    {

        if (null === $this->tblBasketCommodity) {
            return false;
        } else {
            return Basket::useService()->getBasketCommodityById($this->tblBasketCommodity);
        }
    }

    /**
     * @param TblBasketCommodity $tblBasketCommodity
     */
    public function setTblBasketCommodity(TblBasketCommodity $tblBasketCommodity = null)
    {

        $this->tblBasketCommodity = ( null === $tblBasketCommodity ? null : $tblBasketCommodity->getId() );
    }

    /**
     * @return bool|TblDebtor
     */
    public function getServiceBillingDebtor()
    {

        if (null === $this->serviceBilling_Debtor) {
            return false;
        } else {
            return Banking::useService()->getDebtorById($this->serviceBilling_Debtor);
        }
    }

    /**
     * @param null|TblDebtor $tblDebtor
     */
    public function setServiceBillingDebtor(TblDebtor $tblDebtor = null)
    {

        $this->serviceBilling_Debtor = ( null === $tblDebtor ? null : $tblDebtor->getId() );
    }
}
