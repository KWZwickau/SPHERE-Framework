<?php
namespace SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice as TblInvoiceInv;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblPayment")
 * @Cache(usage="READ_ONLY")
 */
class TblInvoice extends Element
{

    const ATTR_SERVICE_TBL_INVOICE = 'serviceTblInvoice';
    const ATTR_TBL_PAYMENT = 'tblPayment';
    const ATTR_INVOICE_NUMBER = 'InvoiceNumber';

    /**
     * @Column(type="bigint")
     */
    protected $tblPayment;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblInvoice;
    /**
     * @Column(boolean)
     */
    protected $IsPaid;
    /**
     * @Column(type="string")
     */
    protected $InvoiceNumber;

    /**
     * @return bool|TblPayment
     */
    public function getTblPayment()
    {

        if (null === $this->tblPayment) {
            return false;
        } else {
            return Balance::useService()->getPaymentById($this->tblPayment);
        }
    }

    /**
     * @param TblPayment|null $tblPayment
     */
    public function setTblPayment(TblPayment $tblPayment = null)
    {

        $this->tblPayment = ( null === $tblPayment ? null : $tblPayment->getId() );
    }

    /**
     * @return bool|\SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice
     */
    public function getServiceTblInvoice()
    {

        if (null === $this->serviceTblInvoice) {
            return false;
        } else {
            return Invoice::useService()->getInvoiceById($this->serviceTblInvoice);
        }
    }

    /**
     * @param TblInvoiceInv|null $tblInvoice
     */
    public function setServiceTblInvoice(TblInvoiceInv $tblInvoice = null)
    {

        $this->serviceTblInvoice = ( null === $tblInvoice ? null : $tblInvoice->getId() );
    }

    /**
     * @return string $InvoiceNumber
     */
    public function getInvoiceNumber()
    {

        return $this->InvoiceNumber;
    }

    /**
     * @param string $InvoiceNumber
     */
    public function setInvoiceNumber($InvoiceNumber)
    {

        $this->InvoiceNumber = $InvoiceNumber;
    }

    /**
     * @return string $IsPaid
     */
    public function getIsPaid()
    {

        return $this->IsPaid;
    }

    /**
     * @param $IsPaid
     */
    public function setIsPaid($IsPaid)
    {

        $this->IsPaid = $IsPaid;
    }
}
