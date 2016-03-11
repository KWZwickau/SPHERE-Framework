<?php
namespace SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblInvoicePayment")
 * @Cache(usage="READ_ONLY")
 */
class TblInvoicePayment extends Element
{

    const ATTR_TBL_PAYMENT_TYPE = 'tblPaymentType';
    const ATTR_TBL_INVOICE = 'tblInvoice';

    /**
     * @Column(type="bigint")
     */
    protected $tblPaymentType;
    /**
     * @Column(type="bigint")
     */
    protected $tblInvoice;


    /**
     * @return bool|TblPaymentType
     */
    public function getTblPaymentType()
    {

        if (null === $this->tblPaymentType) {
            return false;
        } else {
            return Balance::useService()->getPaymentTypeById($this->tblPaymentType);
        }
    }

    /**
     * @param TblPaymentType|null $tblPaymentType
     */
    public function setTblPaymentType(TblPaymentType $tblPaymentType = null)
    {

        $this->tblPaymentType = ( null === $tblPaymentType ? null : $tblPaymentType->getId() );
    }

    /**
     * @return bool|TblInvoice
     */
    public function getTblInvoice()
    {

        if (null === $this->tblInvoice) {
            return false;
        } else {
            return Balance::useService()->getInvoiceById($this->tblInvoice);
        }
    }

    /**
     * @param TblInvoice|null $tblInvoice
     */
    public function setTblInvoice(TblInvoice $tblInvoice = null)
    {

        $this->tblInvoice = ( null === $tblInvoice ? null : $tblInvoice->getId() );
    }

}
