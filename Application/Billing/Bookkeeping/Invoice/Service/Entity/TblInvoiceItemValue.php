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
    const ATTR_TBL_DEBTOR = 'tblDebtor';

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
    protected $tblDebtor;

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
     * @return bool|TblDebtor
     */
    public function getServiceTblDebtor()
    {

        if (null === $this->tblDebtor) {
            return false;
        } else {
            return Invoice::useService()->getDebtorById($this->tblDebtor);
        }
    }

    /**
     * @param null|TblDebtor $tblDebtor
     */
    public function setServiceTblDebtor(TblDebtor $tblDebtor = null)
    {

        $this->tblDebtor = ( null === $tblDebtor ? null : $tblDebtor->getId() );
    }
}
