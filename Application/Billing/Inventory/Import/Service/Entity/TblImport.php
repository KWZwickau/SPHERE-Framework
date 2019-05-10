<?php

namespace SPHERE\Application\Billing\Inventory\Import\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblImport")
 * @Cache(usage="READ_ONLY")
 */
class TblImport extends Element
{

    const ATTR_ROW = 'Row';
    const ATTR_FIRST_NAME = 'FirstName';
    const ATTR_LAST_NAME = 'LastName';
    const ATTR_BIRTHDAY = 'Birthday';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_VALUE = 'Value';
    const ATTR_PRICE_VARIANT = 'PriceVariant';
    const ATTR_ITEM = 'Item';
    const ATTR_REFERENCE = 'Reference';
    const ATTR_REFERENCE_DATE = 'ReferenceDate';
    const ATTR_PAYMENT_FROM_DATE = 'PaymentFromDate';
    const ATTR_PAYMENT_TILL_DATE = 'PaymentTillDate';
    const ATTR_DEBTOR_FIRST_NAME = 'DebtorFirstName';
    const ATTR_DEBTOR_LAST_NAME = 'DebtorLastName';
    const ATTR_SERVICE_TBL_PERSON_DEBTOR = 'serviceTblPersonDebtor';
    const ATTR_DEBTOR_NUMBER = 'DebtorNumber';
    const ATTR_IBAN = 'IBAN';
    const ATTR_BIC = 'BIC';
    const ATTR_BANK = 'Bank';

    /**
     * @Column(type="string")
     */
    protected $Row;
    /**
     * @column(type="string")
     */
    protected $FirstName;
    /**
     * @column(type="string")
     */
    protected $LastName;
    /**
     * @column(type="string")
     */
    protected $Birthday;
    /**
     * @column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @column(type="string")
     */
    protected $Value;
    /**
     * @column(type="string")
     */
    protected $PriceVariant;
    /**
     * @column(type="string")
     */
    protected $Item;
    /**
     * @column(type="string")
     */
    protected $Reference;
    /**
     * @column(type="string")
     */
    protected $ReferenceDate;
    /**
     * @column(type="string")
     */
    protected $PaymentFromDate;
    /**
     * @column(type="string")
     */
    protected $PaymentTillDate;
    /**
     * @column(type="string")
     */
    protected $DebtorFirstName;
    /**
     * @column(type="string")
     */
    protected $DebtorLastName;
    /**
     * @column(type="bigint")
     */
    protected $serviceTblPersonDebtor;
    /**
     * @column(type="string")
     */
    protected $DebtorNumber;
    /**
     * @column(type="string")
     */
    protected $IBAN;
    /**
     * @column(type="string")
     */
    protected $BIC;
    /**
     * @column(type="string")
     */
    protected $Bank;

    /**
     * @return string
     */
    public function getRow()
    {
        return $this->Row;
    }

    /**
     * @param string $Row
     */
    public function setRow($Row)
    {
        $this->Row = $Row;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->FirstName;
    }

    /**
     * @param string $FirstName
     */
    public function setFirstName($FirstName)
    {
        $this->FirstName = $FirstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->LastName;
    }

    /**
     * @param string $LastName
     */
    public function setLastName($LastName)
    {
        $this->LastName = $LastName;
    }

    /**
     * @return string
     */
    public function getBirthday()
    {
        return $this->Birthday;
    }

    /**
     * @param string $Birthday
     */
    public function setBirthday($Birthday)
    {
        $this->Birthday = $Birthday;
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {

        if(null !== $this->serviceTblPerson){
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
        return false;
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ($tblPerson ? $tblPerson->getId(): null);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->Value;
    }

    /**
     * @param string $Value
     */
    public function setValue($Value)
    {
        $this->Value = $Value;
    }

    /**
     * @return string
     */
    public function getPriceVariant()
    {
        return $this->PriceVariant;
    }

    /**
     * @param string $PriceVariant
     */
    public function setPriceVariant($PriceVariant)
    {
        $this->PriceVariant = $PriceVariant;
    }

    /**
     * @return string
     */
    public function getItem()
    {
        return $this->Item;
    }

    /**
     * @param string $Item
     */
    public function setItem($Item)
    {
        $this->Item = $Item;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->Reference;
    }

    /**
     * @param string $Reference
     */
    public function setReference($Reference)
    {
        $this->Reference = $Reference;
    }

    /**
     * @return string
     */
    public function getReferenceDate()
    {
        return $this->ReferenceDate;
    }

    /**
     * @param string $ReferenceDate
     */
    public function setReferenceDate($ReferenceDate)
    {
        $this->ReferenceDate = $ReferenceDate;
    }

    /**
     * @return string
     */
    public function getPaymentFromDate()
    {
        return $this->PaymentFromDate;
    }

    /**
     * @param string $PaymentFromDate
     */
    public function setPaymentFromDate($PaymentFromDate)
    {
        $this->PaymentFromDate = $PaymentFromDate;
    }

    /**
     * @return string
     */
    public function getPaymentTillDate()
    {
        return $this->PaymentTillDate;
    }

    /**
     * @param string $PaymentTillDate
     */
    public function setPaymentTillDate($PaymentTillDate)
    {
        $this->PaymentTillDate = $PaymentTillDate;
    }

    /**
     * @return string
     */
    public function getDebtorFirstName()
    {
        return $this->DebtorFirstName;
    }

    /**
     * @param string $DebtorFirstName
     */
    public function setDebtorFirstName($DebtorFirstName)
    {
        $this->DebtorFirstName = $DebtorFirstName;
    }

    /**
     * @return string
     */
    public function getDebtorLastName()
    {
        return $this->DebtorLastName;
    }

    /**
     * @param string $DebtorLastName
     */
    public function setDebtorLastName($DebtorLastName)
    {
        $this->DebtorLastName = $DebtorLastName;
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPersonDebtor()
    {

        if(null !== $this->serviceTblPersonDebtor){
            return Person::useService()->getPersonById($this->serviceTblPersonDebtor);
        }
        return false;
    }

    /**
     * @param TblPerson|null $tblPersonDebtor
     */
    public function setServiceTblPersonDebtor(TblPerson $tblPersonDebtor = null)
    {

        $this->serviceTblPersonDebtor = ($tblPersonDebtor ? $tblPersonDebtor->getId(): null);
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
    public function getIBAN()
    {
        return $this->IBAN;
    }

    /**
     * @param string $IBAN
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
     * @param string $BIC
     */
    public function setBIC($BIC)
    {
        $this->BIC = $BIC;
    }

    /**
     * @return mixed
     */
    public function getBank()
    {
        return $this->Bank;
    }

    /**
     * @param mixed $Bank
     */
    public function setBank($Bank)
    {
        $this->Bank = $Bank;
    }
}
