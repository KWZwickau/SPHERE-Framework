<?php
namespace SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBalance")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblBalance extends Element
{

    const ATTR_SERVICE_BILLING_BANKING = 'serviceBilling_Banking';
    const ATTR_SERVICE_BILLING_INVOICE = 'serviceBilling_Invoice';

    /**
     * @Column(type="bigint")
     */
    protected $serviceBilling_Banking;

    /**
     * @Column(type="bigint")
     */
    protected $serviceBilling_Invoice;

    /**
     * @Column(type="date")
     */
    protected $ExportDate;

    /**
     * @param TblDebtor $serviceBilling_Banking
     */
    public function setServiceBillingBanking(TblDebtor $serviceBilling_Banking = null)
    {

        $this->serviceBilling_Banking = ( null === $serviceBilling_Banking ? null : $serviceBilling_Banking->getId() );
    }

    /**
     * @return bool|TblDebtor
     */
    public function getServiceBillingBilling()
    {

        if (null === $this->serviceBilling_Banking) {
            return false;
        } else {
            return Banking::useService()->entityDebtorById($this->serviceBilling_Banking);
        }
    }

    /**
     * @param null|TblInvoice $serviceBilling_Invoice
     */
    public function setServiceBillingInvoice(TblInvoice $serviceBilling_Invoice = null)
    {

        $this->serviceBilling_Invoice = ( null === $serviceBilling_Invoice ? null : $serviceBilling_Invoice->getId() );
    }

    /**
     * @return bool|TblInvoice
     */
    public function getServiceBillingInvoice()
    {

        if (null === $this->serviceBilling_Invoice) {
            return false;
        } else {
            return Invoice::useService()->entityInvoiceById($this->serviceBilling_Invoice);
        }
    }

    /**
     * @return string $ExportDate
     */
    public function getExportDate()
    {

        if (null === $this->ExportDate) {
            return false;
        }
        /** @var \DateTime $ExportDate */
        $ExportDate = $this->ExportDate;
        if ($ExportDate instanceof \DateTime) {
            return $ExportDate->format('d.m.Y');
        } else {
            return (string)$ExportDate;
        }
    }

    /**
     * @param \DateTime $ExportDate
     */
    public function setExportDate(\DateTime $ExportDate)
    {

        $this->ExportDate = $ExportDate;
    }
}
