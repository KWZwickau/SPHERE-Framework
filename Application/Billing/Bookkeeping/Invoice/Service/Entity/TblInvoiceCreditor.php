<?php
namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Creditor\Creditor;
use SPHERE\Application\Billing\Accounting\Creditor\Service\Entity\TblCreditor;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblInvoiceCreditor")
 * @Cache(usage="READ_ONLY")
 */
class TblInvoiceCreditor extends Element
{

    const ATTR_SERVICE_TBL_CREDITOR = 'serviceTblCreditor';
    const ATTR_TBL_INVOICE = 'tblInvoice';

    /**
     * @Column(type="string")
     */
    protected $CreditorId;
    /**
     * @Column(type="string")
     */
    protected $Owner;
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
     * @Column(type="bigint")
     */
    protected $serviceTblCreditor;
    /**
     * @Column(type="bigint")
     */
    protected $tblInvoice;

    /**
     * @return string
     */
    public function getCreditorId()
    {
        return $this->CreditorId;
    }

    /**
     * @param string $CreditorId
     */
    public function setCreditorId($CreditorId)
    {
        $this->CreditorId = $CreditorId;
    }

    /**
     * @return string
     */
    public function getOwner()
    {

        return $this->Owner;
    }

    /**
     * @param $Owner
     */
    public function setOwner($Owner)
    {

        $this->Owner = $Owner;
    }

    /**
     * @return string
     */
    public function getBankName()
    {

        return $this->BankName;
    }

    /**
     * @param $BankName
     */
    public function setBankName($BankName)
    {

        $this->BankName = $BankName;
    }

    /**
     * @return string
     */
    public function getIBAN()
    {

        return $this->IBAN;
    }

    /**
     * @param $IBAN
     */
    public function setIBAN($IBAN)
    {

        $this->IBAN = $IBAN;
    }

    /**
     * @return string
     */
    public function getBIC()
    {

        return $this->BIC;
    }

    /**
     * @param $BIC
     */
    public function setBIC($BIC)
    {

        $this->BIC = $BIC;
    }

    /**
     * @return bool|TblCreditor
     */
    public function getServiceTblCreditor()
    {

        if (null === $this->serviceTblCreditor) {
            return false;
        } else {
            return Creditor::useService()->getCreditorById($this->serviceTblCreditor);
        }
    }

    /**
     * @param TblCreditor|null $tblCreditor
     */
    public function setServiceTblCreditor(TblCreditor $tblCreditor = null)
    {

        $this->serviceTblCreditor = ( null === $tblCreditor ? null : $tblCreditor->getId() );
    }

    /**
     * @return bool|TblInvoice
     */
    public function getTblInvoice()
    {

        if (null === $this->tblInvoice) {
            return false;
        } else {
            return Invoice::useService()->getInvoiceById($this->tblInvoice);
        }
    }

    /**
     * @param null|TblInvoice $tblInvoice
     */
    public function setTblInvoice(TblInvoice $tblInvoice = null)
    {

        $this->tblInvoice = ( null === $tblInvoice ? null : $tblInvoice->getId() );
    }
}