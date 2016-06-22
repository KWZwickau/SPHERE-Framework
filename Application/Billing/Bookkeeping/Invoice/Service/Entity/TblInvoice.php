<?php
namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Mail\Mail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblMail;
use SPHERE\Application\Contact\Phone\Phone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblPhone;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblInvoice")
 * @Cache(usage="READ_ONLY")
 */
class TblInvoice extends Element
{

    const ATTR_DEBTOR_NUMBER = 'DebtorNumber';
    const ATTR_INVOICE_NUMBER = 'InvoiceNumber';

    /**
     * @Column(type="string")
     */
    protected $InvoiceNumber;
//    /**
//     * @Column(type="decimal", precision=14, scale=4)
//     */
//    protected $Discount;
    /**
     * @Column(type="string")
     */
    protected $DebtorFirstName;
    /**
     * @Column(type="string")
     */
    protected $DebtorSecondName;
    /**
     * @Column(type="string")
     */
    protected $DebtorLastName;
    /**
     * @Column(type="string")
     */
    protected $DebtorSalutation;
    /**
     * @Column(type="string")
     */
    protected $DebtorNumber;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAddress;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblMail;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPhone;
//    /**
//     * @Column(type="bigint")
//     */
//    protected $serviceTblPaymentType;
    /**
     * @Column(type="bigint")
     */
    protected $TblDebtor;

    /**
     * @return string
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

//    /**
//     * @return (type="decimal", precision=14, scale=4)
//     */
//    public function getDiscount()
//    {
//
//        return $this->Discount;
//    }
//
//    /**
//     * @param (type="decimal", precision=14, scale=4) $Price
//     */
//    public function setDiscount($Discount)
//    {
//
//        $this->Discount = $Discount;
//    }

    /**
     * @return string
     */
    public function getDebtorFirstName()
    {

        return $this->DebtorFirstName;
    }

    /**
     * @param string $PersonFirstName
     */
    public function setDebtorFirstName($PersonFirstName)
    {

        $this->DebtorFirstName = $PersonFirstName;
    }

    /**
     * @return string
     */
    public function getDebtorSecondName()
    {

        return $this->DebtorSecondName;
    }

    /**
     * @param string $PersonSecondName
     */
    public function setDebtorSecondName($PersonSecondName)
    {

        $this->DebtorSecondName = $PersonSecondName;
    }

    /**
     * @return string
     */
    public function getDebtorLastName()
    {

        return $this->DebtorLastName;
    }

    /**
     * @param string $PersonLastName
     */
    public function setDebtorLastName($PersonLastName)
    {

        $this->DebtorLastName = $PersonLastName;
    }

    /**
     * @return string
     */
    public function getDebtorSalutation()
    {

        return $this->DebtorSalutation;
    }

    /**
     * @param string $PersonSalutation
     */
    public function setDebtorSalutation($PersonSalutation)
    {

        $this->DebtorSalutation = $PersonSalutation;
    }

    /**
     * @return string
     */
    public function getDebtorNumber()
    {

        return $this->DebtorNumber;
    }

    /**
     * @param string $DebtorNumber
     */
    public function setDebtorNumber($DebtorNumber)
    {

        $this->DebtorNumber = $DebtorNumber;
    }

    /**
     * @return string
     */
    public function getDebtorFullName()
    {

        return $this->DebtorSalutation." ".$this->DebtorFirstName." ".$this->DebtorLastName;
    }

    /**
     * @return bool|TblAddress
     */
    public function getServiceTblAddress()
    {

        if (null === $this->serviceTblAddress) {
            return false;
        } else {
            return Address::useService()->getAddressById($this->serviceTblAddress);
        }
    }

    /**
     * @param TblAddress $tblAddress
     */
    public function setServiceTblAddress(TblAddress $tblAddress = null)
    {

        $this->serviceTblAddress = ( null === $tblAddress ? null : $tblAddress->getId() );
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
     * @param TblPerson $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblMail
     */
    public function getServiceTblMail()
    {

        if (null === $this->serviceTblMail) {
            return false;
        } else {
            return Mail::useService()->getMailById($this->serviceTblMail);
        }
    }

    /**
     * @param TblMail $tblMail
     */
    public function setServiceTblMail(TblMail $tblMail = null)
    {

        $this->serviceTblMail = ( null === $tblMail ? null : $tblMail->getId() );
    }

    /**
     * @return bool|TblPhone
     */
    public function getServiceTblPhone()
    {

        if (null === $this->serviceTblPhone) {
            return false;
        } else {
            return Phone::useService()->getPhoneById($this->serviceTblPhone);
        }
    }

    /**
     * @param TblPhone $tblPhone
     */
    public function setServiceTblPhone(TblPhone $tblPhone = null)
    {

        $this->serviceTblPhone = ( null === $tblPhone ? null : $tblPhone->getId() );
    }

//    /**
//     * @return bool|TblPaymentType
//     */
//    public function getServiceTblPaymentType()
//    {
//
//        if (null === $this->serviceTblPaymentType) {
//            return false;
//        } else {
//            return Balance::useService()->getPaymentTypeById($this->serviceTblPaymentType);
//        }
//    }
//
//    /**
//     * @param TblPaymentType $tblPaymentType
//     */
//    public function setServiceTblPaymentType(TblPaymentType $tblPaymentType = null)
//    {
//
//        $this->serviceTblPaymentType = ( null === $tblPaymentType ? null : $tblPaymentType->getId() );
//    }

    /**
     * @return bool|TblDebtor
     */
    public function getTblDebtor()
    {

        if (null === $this->TblDebtor) {
            return false;
        } else {
            return Invoice::useService()->getDebtorById($this->TblDebtor);
        }
    }

    /**
     * @param TblDebtor $tblDebtor
     */
    public function setTblDebtor(TblDebtor $tblDebtor = null)
    {

        $this->TblDebtor = ( null === $tblDebtor ? null : $tblDebtor->getId() );
    }
}
