<?php
namespace SPHERE\Application\Billing\Accounting\Debtor\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemVariant;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblDebtorSelection")
 * @Cache(usage="READ_ONLY")
 */
class TblDebtorSelection extends Element
{

    const ATTR_SERVICE_TBL_PERSON_CAUSER = 'serviceTblPersonCauser';
    const ATTR_SERVICE_TBL_PERSON_DEBTOR = 'serviceTblPersonDebtor';
    const ATTR_SERVICE_TBL_PAYMENT_TYPE = 'serviceTblPaymentType';
    const ATTR_SERVICE_TBL_ITEM = 'serviceTblItem';
    const ATTR_SERVICE_TBL_ITEM_VARIANT = 'serviceTblItemVariant';
    const ATTR_VALUE = 'Value';
    const ATTR_TBL_BANK_ACCOUNT = 'tblBankAccount';
    const ATTR_TBL_BANK_REFERENCE = 'tblBankReference';

    /**
     * @Column(type="bigint")
     * Beitragsverursacher (Kind etc.)
     */
    protected $serviceTblPersonCauser;
    /**
     * @Column(type="bigint")
     * Beitragszahler (Sorgeberechtigte etc.)
     */
    protected $serviceTblPersonDebtor;
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
    protected $serviceTblItemVariant;
    /**
     * @Column(type="decimal", precision=14, scale=4)
     */
    protected $Value;
    /**
     * @Column(type="bigint")
     */
    protected $tblBankAccount;
    /**
     * @Column(type="bigint")
     */
    protected $tblBankReference;

    /**
     * @return bool|TblPerson
     * Beitragsverursacher (Kind etc.)
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
     * @param TblPerson|null $tblPerson
     * Beitragsverursacher (Kind etc.)
     */
    public function setServiceTblPersonCauser(TblPerson $tblPerson = null)
    {

        $this->serviceTblPersonCauser = (null === $tblPerson ? null : $tblPerson->getId());
    }

    /**
     * @return bool|TblPerson
     * Beitragszahler (Sorgeberechtigte etc.)
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
     * @param TblPerson|null $tblPerson
     * Beitragszahler (Sorgeberechtigte etc.)
     */
    public function setServiceTblPersonDebtor(TblPerson $tblPerson = null)
    {

        $this->serviceTblPersonDebtor = (null === $tblPerson ? null : $tblPerson->getId());
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
     * @param TblPaymentType|null $tblPaymentType
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
     * @param TblItem|null $tblItem
     */
    public function setServiceTblItem(TblItem $tblItem = null)
    {

        $this->serviceTblItem = (null === $tblItem ? null : $tblItem->getId());
    }

    /**
     * @return bool|TblItemVariant
     */
    public function getServiceTblItemVariant()
    {

        if(null === $this->serviceTblItemVariant){
            return false;
        } else {
            return Item::useService()->getItemVariantById($this->serviceTblItemVariant);
        }
    }

    /**
     * @param TblItemVariant|null $tblItemVariant
     */
    public function setServiceTblItemVariant(TblItemVariant $tblItemVariant = null)
    {

        $this->serviceTblItemVariant = (null === $tblItemVariant ? null : $tblItemVariant->getId());
    }

    /**
     * @param bool $IsShort
     *
     * @return (type="decimal", precision=14, scale=4)|string
     */
    public function getValue($IsShort = false)
    {

        if($IsShort){
            return str_replace('.', ',', number_format($this->Value, 2));
        }
        return $this->Value;
    }

    /**
     * @return string
     */
    public function getValuePriceString()
    {

        $result = sprintf("%01.2f", $this->Value);
        return str_replace('.', ',', $result)." €";
    }

    /**
     * @param (type="decimal", precision=14, scale=4) $Value
     */
    public function setValue($Value)
    {

        $this->Value = $Value;
    }

    /**
     * @return bool|TblBankAccount
     */
    public function getTblBankAccount()
    {

        if(null === $this->tblBankAccount){
            return false;
        } else {
            return Debtor::useService()->getBankAccountById($this->tblBankAccount);
        }
    }

    /**
     * @param null|TblBankAccount $tblBankAccount
     */
    public function setTblBankAccount(TblBankAccount $tblBankAccount = null)
    {

        $this->tblBankAccount = (null === $tblBankAccount ? null : $tblBankAccount->getId());
    }

    /**
     * @return bool|TblBankReference
     */
    public function getTblBankReference()
    {

        if(null === $this->tblBankReference){
            return false;
        } else {
            return Debtor::useService()->getBankReferenceById($this->tblBankReference);
        }
    }

    /**
     * @param null|TblBankReference $tblBankReference
     */
    public function setTblBankReference(TblBankReference $tblBankReference = null)
    {

        $this->tblBankReference = (null === $tblBankReference ? null : $tblBankReference->getId());
    }
}
