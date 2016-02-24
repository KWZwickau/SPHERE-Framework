<?php
namespace SPHERE\Application\Billing\Accounting\Banking\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
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

    const SERVICE_PEOPLE_PERSON = 'ServicePeople_Person';
    const SERVICE_PEOPLE_PERSON_PAYERS = 'ServicePeople_PersonPayers';
    const SERVICE_BALANCE_PAYMENT_TYPE = 'ServicePaymentType';
    const SERVICE_INVENTORY_ITEM = 'ServiceInventory_Item';
    const ATTR_TBL_DEBTOR = 'tblDebtor';
    const ATTR_TBL_BANK_ACCOUNT = 'tblBankAccount';
    const ATTR_TBL_BANK_REFERENCE = '$tblBankReference';

    /**
     * @Column(type="bigint")
     */
    protected $ServicePeople_Person;
    /**
     * @Column(type="bigint")
     */
    protected $ServicePeople_PersonPayers;
    /**
     * @Column(type="bigint")
     */
    protected $ServicePaymentType;
    /**
     * @Column(type="bigint")
     */
    protected $ServiceInventory_Item;
    /**
     * @Column(type="bigint")
     */
    protected $tblDebtor;
    /**
     * @Column(type="bigint")
     */
    protected $tblBankReference;
    /**
     * @Column(type="bigint")
     */
    protected $tblBankAccount;

    /**
     * @return bool|TblPerson
     */
    public function getServicePeoplePerson()
    {

        if (null === $this->ServicePeople_Person) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->ServicePeople_Person);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServicePeoplePerson(TblPerson $tblPerson = null)
    {

        $this->ServicePeople_Person = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblPerson
     */
    public function getServicePeoplePersonPayers()
    {

        if (null === $this->ServicePeople_PersonPayers) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->ServicePeople_PersonPayers);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServicePeoplePersonPayers(TblPerson $tblPerson = null)
    {

        $this->ServicePeople_PersonPayers = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblPaymentType
     */
    public function getServicePaymentType()
    {

        if (null === $this->ServicePaymentType) {
            return false;
        } else {
            return Balance::useService()->getPaymentTypeById($this->ServicePaymentType);
        }
    }

    /**
     * @param TblPaymentType|null $tblPaymentType
     */
    public function setServicePaymentType(TblPaymentType $tblPaymentType = null)
    {

        $this->ServicePaymentType = ( null === $tblPaymentType ? null : $tblPaymentType->getId() );
    }

    /**
     * @return bool|TblItem
     */
    public function getServiceInventoryItem()
    {

        if (null === $this->ServiceInventory_Item) {
            return false;
        } else {
            return Item::useService()->getItemById($this->ServiceInventory_Item);
        }
    }

    /**
     * @param TblItem|null $tblItem
     */
    public function setServiceInventoryItem(TblItem $tblItem = null)
    {

        $this->ServiceInventory_Item = ( null === $tblItem ? null : $tblItem->getId() );
    }

    /**
     * @return bool|TblDebtor
     */
    public function getTblDebtor()
    {

        if (null === $this->tblDebtor) {
            return false;
        } else {
            return Banking::useService()->getDebtorById($this->tblDebtor);
        }
    }

    /**
     * @param null|TblDebtor $tblDebtor
     */
    public function setTblDebtor(TblDebtor $tblDebtor)
    {

        $this->tblDebtor = ( null === $tblDebtor ? null : $tblDebtor->getId() );
    }

    /**
     * @return bool|TblBankReference
     */
    public function getTblBankReference()
    {

        if (null === $this->tblBankReference) {
            return false;
        } else {
            return Banking::useService()->getBankReferenceById($this->tblBankReference);
        }
    }

    /**
     * @param null|TblBankReference $tblBankReference
     */
    public function setTblBankReference(TblBankReference $tblBankReference = null)
    {

        $this->tblBankReference = ( null === $tblBankReference ? null : $tblBankReference->getId() );
    }

    /**
     * @return bool|TblBankAccount
     */
    public function getTblBankAccount()
    {

        if (null === $this->tblBankAccount) {
            return false;
        } else {
            return Banking::useService()->getBankAccountById($this->tblBankAccount);
        }
    }

    /**
     * @param null|TblBankAccount $tblBankAccount
     */
    public function setTblBankAccount(TblBankAccount $tblBankAccount = null)
    {

        $this->tblBankAccount = ( null === $tblBankAccount ? null : $tblBankAccount->getId() );
    }
}
