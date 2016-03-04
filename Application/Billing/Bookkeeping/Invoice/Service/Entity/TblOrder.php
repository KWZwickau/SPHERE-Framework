<?php
//namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity;
//
//use Doctrine\ORM\Mapping\Cache;
//use Doctrine\ORM\Mapping\Column;
//use Doctrine\ORM\Mapping\Entity;
//use Doctrine\ORM\Mapping\Table;
//use SPHERE\Application\Billing\Accounting\Banking\Banking;
//use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblPaymentType;
//use SPHERE\Application\Contact\Address\Address;
//use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
//use SPHERE\Application\People\Person\Person;
//use SPHERE\Application\People\Person\Service\Entity\TblPerson;
//use SPHERE\System\Database\Fitting\Element;
//
///**
// * @Entity
// * @Table(name="tblOrder")
// * @Cache(usage="READ_ONLY")
// */
//class TblOrder extends Element
//{
//
//    const ATTR_DEBTOR_NUMBER = 'DebtorNumber';
//    const ATTR_SERVICE_TBL_PAYMENT_TYPE = 'serviceTblPaymentType';
//
//    /**
//     * @Column(type="string")
//     */
//    protected $BasketName;
//    /**
//     * @Column(type="date")
//     */
//    protected $InvoiceDate;
//    /**
//     * @Column(type="date")
//     */
//    protected $PaymentDate;
//    /**
//     * @Column(type="decimal", precision=14, scale=4)
//     */
//    protected $Discount;
//    /**
//     * @Column(type="string")
//     */
//    protected $DebtorFirstName;
//    /**
//     * @Column(type="string")
//     */
//    protected $DebtorLastName;
//    /**
//     * @Column(type="string")
//     */
//    protected $DebtorSalutation;
//    /**
//     * @Column(type="string")
//     */
//    protected $DebtorNumber;
//    /**
//     * @Column(type="bigint")
//     */
//    protected $serviceManagement_Address;
//    /**
//     * @Column(type="bigint")
//     */
//    protected $serviceManagement_Person;
//    /**
//     * @Column(type="bigint")
//     */
//    protected serviceTblPaymentType;
//    /**
//     * @Column(type="boolean")
//     */
//    protected $IsPaymentDateModified;
//
//    /**
//     * @return boolean
//     */
//    public function isPaymentDateModified()
//    {
//
//        return $this->IsPaymentDateModified;
//    }
//
//    /**
//     * @param boolean $IsPaymentDateModified
//     */
//    public function setPaymentDateModified($IsPaymentDateModified)
//    {
//
//        $this->IsPaymentDateModified = $IsPaymentDateModified;
//    }
//
//    /**
//     * @return string
//     */
//    public function getBasketName()
//    {
//
//        return $this->BasketName;
//    }
//
//    /**
//     * @param string $BasketName
//     */
//    public function setBasketName($BasketName)
//    {
//
//        $this->BasketName = $BasketName;
//    }
//
//    /**
//     * @return string
//     */
//    public function getInvoiceDate()
//    {
//
//        if (null === $this->InvoiceDate) {
//            return false;
//        }
//        /** @var \DateTime $InvoiceDate */
//        $InvoiceDate = $this->InvoiceDate;
//        if ($InvoiceDate instanceof \DateTime) {
//            return $InvoiceDate->format('d.m.Y');
//        } else {
//            return (string)$InvoiceDate;
//        }
//    }
//
//    /**
//     * @param \DateTime $InvoiceDate
//     */
//    public function setInvoiceDate(\DateTime $InvoiceDate)
//    {
//
//        $this->InvoiceDate = $InvoiceDate;
//    }
//
//    /**
//     * @return string
//     */
//    public function getPaymentDate()
//    {
//
//        if (null === $this->PaymentDate) {
//            return false;
//        }
//        /** @var \DateTime $PaymentDate */
//        $PaymentDate = $this->PaymentDate;
//        if ($PaymentDate instanceof \DateTime) {
//            return $PaymentDate->format('d.m.Y');
//        } else {
//            return (string)$PaymentDate;
//        }
//    }
//
//    /**
//     * @param \DateTime $PaymentDate
//     */
//    public function setPaymentDate(\DateTime $PaymentDate)
//    {
//
//        $this->PaymentDate = $PaymentDate;
//    }
//
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
//
//    /**
//     * @return string
//     */
//    public function getDebtorFirstName()
//    {
//
//        return $this->DebtorFirstName;
//    }
//
//    /**
//     * @param string $PersonFirstName
//     */
//    public function setDebtorFirstName($PersonFirstName)
//    {
//
//        $this->DebtorFirstName = $PersonFirstName;
//    }
//
//    /**
//     * @return string
//     */
//    public function getDebtorLastName()
//    {
//
//        return $this->DebtorLastName;
//    }
//
//    /**
//     * @param string $PersonLastName
//     */
//    public function setDebtorLastName($PersonLastName)
//    {
//
//        $this->DebtorLastName = $PersonLastName;
//    }
//
//    /**
//     * @return string
//     */
//    public function getDebtorSalutation()
//    {
//
//        return $this->DebtorSalutation;
//    }
//
//    /**
//     * @param string $PersonSalutation
//     */
//    public function setDebtorSalutation($PersonSalutation)
//    {
//
//        $this->DebtorSalutation = $PersonSalutation;
//    }
//
//    /**
//     * @return string
//     */
//    public function getDebtorNumber()
//    {
//
//        return $this->DebtorNumber;
//    }
//
//    /**
//     * @param string $DebtorNumber
//     */
//    public function setDebtorNumber($DebtorNumber)
//    {
//
//        $this->DebtorNumber = $DebtorNumber;
//    }
//
//    /**
//     * @return string
//     */
//    public function getDebtorFullName()
//    {
//
//        return $this->DebtorSalutation." ".$this->DebtorFirstName." ".$this->DebtorLastName;
//    }
//
//    /**
//     * @return bool|TblAddress
//     */
//    public function getServiceManagementAddress()
//    {
//
//        if (null === $this->serviceManagement_Address) {
//            return false;
//        } else {
//            return Address::useService()->getAddressById($this->serviceManagement_Address);
//        }
//    }
//
//    /**
//     * @param TblAddress $tblAddress
//     */
//    public function setServiceManagementAddress(TblAddress $tblAddress = null)
//    {
//
//        $this->serviceManagement_Address = ( null === $tblAddress ? null : $tblAddress->getId() );
//    }
//
//    /**
//     * @return bool|TblPerson
//     */
//    public function getServiceManagementPerson()
//    {
//
//        if (null === $this->serviceManagement_Person) {
//            return false;
//        } else {
//            return Person::useService()->getPersonById($this->serviceManagement_Person);
//        }
//    }
//
//    /**
//     * @param TblPerson $tblPerson
//     */
//    public function setServiceManagementPerson(TblPerson $tblPerson = null)
//    {
//
//        $this->serviceManagement_Person = ( null === $tblPerson ? null : $tblPerson->getId() );
//    }
//
//    /**
//     * @return bool|TblPaymentType
//     */
//    public function getServiceBillingBankingPaymentType()
//    {
//
//        if (null === $this->serviceBilling_Banking_Payment_Type) {
//            return false;
//        } else {
//            return Banking::useService()->getPaymentTypeById($this->serviceBilling_Banking_Payment_Type);
//        }
//    }
//
//    /**
//     * @param TblPaymentType $tblPaymentType
//     */
//    public function setServiceBillingBankingPaymentType(TblPaymentType $tblPaymentType = null)
//    {
//
//        $this->serviceBilling_Banking_Payment_Type = ( null === $tblPaymentType ? null : $tblPaymentType->getId() );
//    }
//}
