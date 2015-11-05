<?php
namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblTempInvoiceCommodity")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblTempInvoiceCommodity extends Element
{

    const ATTR_TBL_TEMP_INVOICE = 'tblTempInvoice';
    const ATTR_SERVICE_BILLING_COMMODITY = 'serviceBilling_Commodity';

    /**
     * @Column(type="bigint")
     */
    protected $tblTempInvoice;
    /**
     * @Column(type="bigint")
     */
    protected $serviceBilling_Commodity;

    /**
     * @return bool|TblTempInvoice
     */
    public function getTblTempInvoice()
    {

        if (null === $this->tblTempInvoice) {
            return false;
        } else {
            return Invoice::useService()->getTempInvoiceById($this->tblTempInvoice);
        }
    }

    /**
     * @param TblTempInvoice $tblTempInvoice
     */
    public function setTblTempInvoice(TblTempInvoice $tblTempInvoice = null)
    {

        $this->tblTempInvoice = ( null === $tblTempInvoice ? null : $tblTempInvoice->getId() );
    }

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
     * @param TblCommodity $tblCommodity
     */
    public function setServiceBillingCommodity(TblCommodity $tblCommodity = null)
    {

        $this->serviceBilling_Commodity = ( null === $tblCommodity ? null : $tblCommodity->getId() );
    }
}
