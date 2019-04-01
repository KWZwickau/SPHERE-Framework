<?php
namespace SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblBankAccount;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblDebtorSelection;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBasketVerification")
 * @Cache(usage="READ_ONLY")
 */
class TblBasketVerification extends Element
{

    const ATTR_TBL_BASKET = 'tblBasket';
    const ATTR_SERVICE_TBL_PERSON_CAUSER = 'serviceTblPersonCauser';
    const ATTR_SERVICE_TBL_PERSON_DEBTOR = 'serviceTblPersonDebtor';
    const ATTR_SERVICE_TBL_BANK_ACCOUNT = 'serviceTblBankAccount';
    const ATTR_SERVICE_TBL_BANK_REFERENCE = 'serviceTblBankReference';
    const ATTR_SERVICE_TBL_PAYMENT_TYPE = 'serviceTblPaymentType';
    const ATTR_SERVICE_TBL_ITEM = 'serviceTblItem';

    /**
     * @Column(type="decimal", precision=14, scale=4)
     */
    protected $Value;
    /**
     * @Column(type="integer")
     */
    protected $Quantity;
    /**
     * @Column(type="bigint")
     */
    protected $tblBasket;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPersonCauser;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPersonDebtor;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblBankAccount;
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
    protected $serviceTblItem;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDebtorSelection;

    /**
     * @param bool $IsFormInput
     *
     * @return (type="decimal", precision=14, scale=4)
     */
    public function getValue($IsFormInput = false)
    {

        if($IsFormInput){
            return str_replace('.', ',', number_format($this->Value, 2));
        }
        return $this->Value;
    }

    /**
     * @param (type="decimal", precision=14, scale=4) $Value
     */
    public function setValue($Value)
    {

        $this->Value = $Value;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {

        return $this->Quantity;
    }

    /**
     * @param int $Quantity
     */
    public function setQuantity($Quantity)
    {

        $this->Quantity = $Quantity;
    }

    /**
     * @return bool|TblBasket
     */
    public function getTblBasket()
    {

        if(null === $this->tblBasket){
            return false;
        } else {
            return Basket::useService()->getBasketById($this->tblBasket);
        }
    }

    /**
     * @param null|TblBasket $tblBasket
     */
    public function setTblBasket($tblBasket = null)
    {

        $this->tblBasket = (null === $tblBasket ? null : $tblBasket->getId());
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPersonCauser()
    {

        if(null === $this->serviceTblPersonCauser){
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPersonCauser);
        }
    }

    /**
     * @param null|TblPerson $tblPerson
     */
    public function setServiceTblPersonCauser(TblPerson $tblPerson = null)
    {

        $this->serviceTblPersonCauser = (null === $tblPerson ? null : $tblPerson->getId());
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPersonDebtor()
    {

        if(null === $this->serviceTblPersonDebtor){
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPersonDebtor);
        }
    }

    /**
     * @param null|TblPerson $tblPerson
     */
    public function setServiceTblPersonDebtor(TblPerson $tblPerson = null)
    {

        $this->serviceTblPersonDebtor = (null === $tblPerson ? null : $tblPerson->getId());
    }

    /**
     * @return bool|TblBankAccount
     */
    public function getServiceTblBankAccount()
    {

        if(null === $this->serviceTblBankAccount){
            return false;
        } else {
            return Debtor::useService()->getBankAccountById($this->serviceTblBankAccount);
        }
    }

    /**
     * @param null|TblBankAccount $tblBankAccount
     */
    public function setServiceTblBankAccount(TblBankAccount $tblBankAccount = null)
    {

        $this->serviceTblBankAccount = (null === $tblBankAccount ? null : $tblBankAccount->getId());
    }

    /**
     * @return bool|TblBankReference
     */
    public function getServiceTblBankReference()
    {

        if(null === $this->serviceTblBankReference){
            return false;
        } else {
            return Debtor::useService()->getBankReferenceById($this->serviceTblBankReference);
        }
    }

    /**
     * @param null|TblBankReference $tblBankReference
     */
    public function setServiceTblBankReference(TblBankReference $tblBankReference = null)
    {

        $this->serviceTblBankReference = (null === $tblBankReference ? null : $tblBankReference->getId());
    }

    /**
     * @return bool|TblPaymentType
     */
    public function getServiceTblPaymentType()
    {

        if(null === $this->serviceTblPaymentType){
            return false;
        } else {
            return Balance::useService()->getPaymentTypeById($this->serviceTblPaymentType);
        }
    }

    /**
     * @param null|TblPaymentType $tblPaymentType
     */
    public function setServiceTblPaymentType(TblPaymentType $tblPaymentType = null)
    {

        $this->serviceTblPaymentType = (null === $tblPaymentType ? null : $tblPaymentType->getId());
    }

    /**
     * @return bool|TblItem
     */
    public function getServiceTblItem()
    {

        if(null === $this->serviceTblItem){
            return false;
        } else {
            return Item::useService()->getItemById($this->serviceTblItem);
        }
    }

    /**
     * @param null|TblItem $tblItem
     */
    public function setServiceTblItem(TblItem $tblItem = null)
    {

        $this->serviceTblItem = (null === $tblItem ? null : $tblItem->getId());
    }

    /**
     * @return bool|TblDebtorSelection
     */
    public function getServiceTblDebtorSelection()
    {

        if(null === $this->serviceTblDebtorSelection){
            return false;
        } else {
            return Debtor::useService()->getDebtorSelectionById($this->serviceTblDebtorSelection);
        }
    }

    /**
     * @param null|TblDebtorSelection $tblDebtorSelection
     */
    public function setServiceTblDebtorSelection(TblDebtorSelection $tblDebtorSelection = null)
    {

        $this->serviceTblDebtorSelection = (null === $tblDebtorSelection ? null : $tblDebtorSelection->getId());
    }

    /**
     * @return string
     * single ItemPrice
     */
    public function getPrice()
    {

        return str_replace('.', ',', number_format($this->Value, 2).' €');
    }

    /**
     * @return string
     */
    public function getSummaryPrice()
    {
//        if($this->Quantity !== 0){
            $result = $this->Value * $this->Quantity;
//        } else {
//            $result = $this->Value;
//        }
        return str_replace('.', ',', number_format($result, 2).' €');
    }


}
