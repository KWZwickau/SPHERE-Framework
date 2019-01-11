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
 * @Table(name="tblInvoiceCauser")
 * @Cache(usage="READ_ONLY")
 */
class TblInvoiceCauser extends Element
{

    const ATTR_TBL_INVOICE = 'tblInvoice';
    const ATTR_SERVICE_TBL_PERSON_CAUSER = 'serviceTblPersonCauser';

    /**
     * @Column(type="bigint")
     */
    protected $tblInvoice;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPersonCauser;

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
     * @return bool|TblPerson
     */
    public function getServiceTblPersonCauser()
    {

        if (null === $this->serviceTblPersonCauser) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPersonCauser);
        }
    }

    /**
     * @param TblPerson|null $tblPersonCauser
     */
    public function setServiceTblPersonCauser(TblPerson $tblPersonCauser = null)
    {

        $this->serviceTblPersonCauser = ( null === $tblPersonCauser ? null : $tblPersonCauser->getId() );
    }
}
