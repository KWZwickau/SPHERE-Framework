<?php
namespace SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
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
     * @Column(type="string")
     */
    protected $BankName;
    /**
     * @Column(type="string")
     */
    protected $IBAN;
    /**
     * @Column(type="string")
     */
    protected $BIC;
    /**
     * @Column(type="string")
     */
    protected $Owner;
    /**
     * @Column(type="string")
     */
    protected $CashSign;


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
            return Banking::useService()->getDebtorById($this->serviceBilling_Banking);
        }
    }

    /**
     * @return bool|TblInvoice
     */
    public function getServiceBillingInvoice()
    {

        if (null === $this->serviceBilling_Invoice) {
            return false;
        } else {
            return Invoice::useService()->getInvoiceById($this->serviceBilling_Invoice);
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

    /**
     * @return string $BankName
     */
    public function getBankName()
    {

        return $this->BankName;
    }

    /**
     * @param string $BankName
     */
    public function setBankName($BankName)
    {

        $this->BankName = $BankName;
    }

    /**
     * @return string $IBAN
     */
    public function getIBAN()
    {

        return $this->IBAN;
    }

    /**
     * @param string $IBAN
     */
    public function setIBAN($IBAN)
    {

        $this->IBAN = strtoupper(substr(str_replace(' ', '', $IBAN), 0, 34));
    }

    /**
     * @return string $BIC
     */
    public function getBIC()
    {

        return $this->BIC;
    }

    /**
     * @param string $BIC
     */
    public function setBIC($BIC)
    {

        $this->BIC = strtoupper(substr(str_replace(' ', '', $BIC), 0, 11));
    }

    /**
     * @return string $Owner
     */
    public function getOwner()
    {

        return $this->Owner;
    }

    /**
     * @param string $Owner
     */
    public function setOwner($Owner)
    {

        $this->Owner = $Owner;
    }

    /**
     * @return string $CashSign
     */
    public function getCashSign()
    {

        return $this->CashSign;
    }

    /**
     * @param string $CashSign
     */
    public function setCashSign($CashSign)
    {

        $this->CashSign = $CashSign;
    }
}
