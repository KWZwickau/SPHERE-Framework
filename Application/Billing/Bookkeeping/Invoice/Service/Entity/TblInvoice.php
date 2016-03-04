<?php
namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
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

    const ATTR_IS_PAID = 'IsPaid';
    const ATTR_IS_VOID = 'IsVoid';
    const ATTR_DEBTOR_NUMBER = 'DebtorNumber';
    const ATTR_NUMBER = 'Number';

    /**
     * @Column(type="boolean")
     */
    protected $IsPaid;
    /**
     * @Column(type="string")
     */
    protected $Number;
    /**
     * @Column(type="string")
     */
    protected $BasketName;
    /**
     * @Column(type="boolean")
     */
    protected $IsVoid;
    /**
     * @Column(type="date")
     */
    protected $InvoiceDate;
    /**
     * @Column(type="date")
     */
    protected $PaymentDate;
    /**
     * @Column(type="decimal", precision=14, scale=4)
     */
    protected $Discount;
    /**
     * @Column(type="string")
     */
    protected $DebtorFirstName;
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
    protected $serviceTblPaymentType;
    /**
     * @Column(type="boolean")
     */
    protected $IsPaymentDateModified;

    /**
     * @return boolean
     */
    public function getPaymentDateModified()
    {

        return $this->IsPaymentDateModified;
    }

    /**
     * @param boolean $IsPaymentDateModified
     */
    public function setPaymentDateModified($IsPaymentDateModified)
    {

        $this->IsPaymentDateModified = $IsPaymentDateModified;
    }

    /**
     * @return boolean
     */
    public function isPaid()
    {

        return $this->IsPaid;
    }

    /**
     * @param boolean $IsPaid
     */
    public function setPaid($IsPaid)
    {

        $this->IsPaid = $IsPaid;
    }

    /**
     * @return string
     */
    public function getNumber()
    {

        return $this->Number;
    }

    /**
     * @param string $Number
     */
    public function setNumber($Number)
    {

        $this->Number = $Number;
    }

    /**
     * @return string
     */
    public function getBasketName()
    {

        return $this->BasketName;
    }

    /**
     * @param string $BasketName
     */
    public function setBasketName($BasketName)
    {

        $this->BasketName = $BasketName;
    }

    /**
     * @return boolean
     */
    public function isVoid()
    {

        return $this->IsVoid;
    }

    /**
     * @param boolean $IsVoid
     */
    public function setVoid($IsVoid)
    {

        $this->IsVoid = $IsVoid;
    }

    /**
     * @return string
     */
    public function getInvoiceDate()
    {

        if (null === $this->InvoiceDate) {
            return false;
        }
        /** @var \DateTime $InvoiceDate */
        $InvoiceDate = $this->InvoiceDate;
        if ($InvoiceDate instanceof \DateTime) {
            return $InvoiceDate->format('d.m.Y');
        } else {
            return (string)$InvoiceDate;
        }
    }

    /**
     * @param \DateTime $InvoiceDate
     */
    public function setInvoiceDate(\DateTime $InvoiceDate)
    {

        $this->InvoiceDate = $InvoiceDate;
    }

    /**
     * @return string
     */
    public function getPaymentDate()
    {

        if (null === $this->PaymentDate) {
            return false;
        }
        /** @var \DateTime $PaymentDate */
        $PaymentDate = $this->PaymentDate;
        if ($PaymentDate instanceof \DateTime) {
            return $PaymentDate->format('d.m.Y');
        } else {
            return (string)$PaymentDate;
        }
    }

    /**
     * @param \DateTime $PaymentDate
     */
    public function setPaymentDate(\DateTime $PaymentDate)
    {

        $this->PaymentDate = $PaymentDate;
    }

    /**
     * @return (type="decimal", precision=14, scale=4)
     */
    public function getDiscount()
    {

        return $this->Discount;
    }

    /**
     * @param (type="decimal", precision=14, scale=4) $Price
     */
    public function setDiscount($Discount)
    {

        $this->Discount = $Discount;
    }

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
     * @return bool|TblPaymentType
     */
    public function getServiceTblPaymentType()
    {

        if (null === $this->serviceTblPaymentType) {
            return false;
        } else {
            return Balance::useService()->getPaymentTypeById($this->serviceTblPaymentType);
        }
    }

    /**
     * @param TblPaymentType $tblPaymentType
     */
    public function setServiceTblPaymentType(TblPaymentType $tblPaymentType = null)
    {

        $this->serviceTblPaymentType = ( null === $tblPaymentType ? null : $tblPaymentType->getId() );
    }
}
