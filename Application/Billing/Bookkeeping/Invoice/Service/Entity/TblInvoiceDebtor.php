<?php
namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblInvoiceDebtor")
 * @Cache(usage="READ_ONLY")
 */
class TblInvoiceDebtor extends Element
{

    const ATTR_SERVICE_TBL_PERSON_DEBTOR = 'serviceTblPersonDebtor';
    const ATTR_SERVICE_TBL_BANKING_REFERENCE = 'serviceTblBankReference';
    const ATTR_SERVICE_TBL_PAYMENT_TYPE = 'serviceTblPaymentType';
    const ATTR_TBL_INVOICE = 'tblInvoice';

    /**
     * @Column(type="string")
     */
    protected $DebtorNumber;
    /**
     * @Column(type="string")
     */
    protected $DebtorPerson;
    /**
     * @Column(type="string")
     */
    protected $BankReference;
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
    protected $serviceTblPersonDebtor;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblBankReference;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPaymentType;
    /**
     * @Column(type="bigint")
     */
    protected $tblInvoice;

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
    public function getDebtorPerson()
    {

        return $this->DebtorPerson;
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function setDebtorPerson(TblPerson $tblPerson)
    {

        $this->DebtorPerson = ( $tblPerson !== false ? $tblPerson->getFullName() : '' );
    }

    /**
     * @return string
     */
    public function getBankReference()
    {

        return $this->BankReference;
    }

    /**
     * @param $BankReference
     */
    public function setBankReference($BankReference)
    {

        $this->BankReference = $BankReference;
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
     * @return bool|TblPerson
     */
    public function getServiceTblPersonDebtor()
    {

        if (null === $this->serviceTblPersonDebtor) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPersonDebtor);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPersonDebtor(TblPerson $tblPerson = null)
    {

        $this->serviceTblPersonDebtor = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @param TblBankReference|null $tblBankReference
     */
    public function setServiceTblBankReference(TblBankReference $tblBankReference = null)
    {

        $this->serviceTblBankReference = ( null === $tblBankReference ? null : $tblBankReference->getId() );
    }

    /**
     * @return bool|TblBankReference
     */
    public function getServiceTblBankReference()
    {

        if (null === $this->serviceTblBankReference) {
            return false;
        } else {
            return Debtor::useService()->getBankReferenceById($this->serviceTblBankReference);
        }
    }

    /**
     * @param TblPaymentType|null $tblPaymentType
     */
    public function setServiceTblPaymentType(TblPaymentType $tblPaymentType = null)
    {

        $this->serviceTblPaymentType = ( null === $tblPaymentType ? null : $tblPaymentType->getId() );
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