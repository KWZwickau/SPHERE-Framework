<?php
namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblInvoiceItemValue")
 * @Cache(usage="READ_ONLY")
 */
class TblInvoiceItemValue extends Element
{

    const ATTR_TBL_INVOICE = 'tblInvoice';
    const ATTR_TBL_ITEM_VALUE = 'tblItemValue';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_TBL_INVOICE_DEBTOR = 'tblInvoiceDebtor';

    /**
     * @Column(type="bigint")
     */
    protected $tblInvoice;
    /**
     * @Column(type="bigint")
     */
    protected $tblItemValue;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $tblInvoiceDebtor;

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

    /**
     * @return bool|TblItemValue
     */
    public function getTblItem()
    {

        if (null === $this->tblItemValue) {
            return false;
        } else {
            return Invoice::useService()->getItemById($this->tblItemValue);
        }
    }

    /**
     * @param null|TblItemValue $tblItemValue
     */
    public function setTblItem(TblItemValue $tblItemValue = null)
    {

        $this->tblItemValue = ( null === $tblItemValue ? null : $tblItemValue->getId() );
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param null|TblPerson $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblInvoiceDebtor
     */
    public function getInvoiceDebtor()
    {

        if (null === $this->tblInvoiceDebtor) {
            return false;
        } else {
            return Invoice::useService()->getInvoiceDebtorById($this->tblInvoiceDebtor);
        }
    }

    /**
     * @param null|TblInvoiceDebtor $tblInvoiceDebtor
     */
    public function setInvoiceDebtor(TblInvoiceDebtor $tblInvoiceDebtor = null)
    {

        $this->tblInvoiceDebtor = ( null === $tblInvoiceDebtor ? null : $tblInvoiceDebtor->getId() );
    }
}
