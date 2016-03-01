<?php

namespace SPHERE\Application\Billing\Accounting\Banking\Service;

use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankAccount;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtorSelection;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Accounting\Banking\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Id
     *
     * @return bool|TblDebtor
     */
    public function getDebtorById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblDebtor', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Id
     *
     * @return false|TblBankReference
     */
    public function getBankReferenceById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankReference', $Id);
    }

    /**
     * @return false|TblBankReference[]
     */
    public function getBankReferenceAll()
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankReference',
            array(TblBankReference::ATTR_IS_VOID => false));
    }

    /**
     * @param $Id
     *
     * @return bool|TblBankAccount
     */
    public function getBankAccountById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblBankAccount', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $DebtorNumber
     *
     * @return TblDebtor|bool
     */
    public function getDebtorByDebtorNumber($DebtorNumber)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblDebtor')->findOneBy(array(TblDebtor::ATTR_DEBTOR_NUMBER => $DebtorNumber));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return TblDebtor[]|bool
     */
    public function getDebtorAllByPerson(TblPerson $tblPerson)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblDebtor')
            ->findBy(array(TblDebtor::SERVICE_TBL_PERSON => $tblPerson->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblDebtor
     */
    public function getDebtorByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtor',
            array(TblDebtor::SERVICE_TBL_PERSON => $tblPerson->getId()));
    }

    /**
     * @param $IBAN
     *
     * @return false|TblBankAccount
     */
    public function getIBANIsUsed($IBAN)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankAccount',
            array(TblBankAccount::ATTR_IBAN => $IBAN));
    }

    /**
     * @param $Reference
     *
     * @return false|TblBankReference
     */
    public function getReferenceIsUsed($Reference)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankReference',
            array(TblBankReference::ATTR_REFERENCE_NUMBER => $Reference,
                  TblBankReference::ATTR_IS_VOID          => false));
    }

    /**
     * @return TblBankAccount[]|bool
     */
    public function getBankAccountAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankAccount');
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblDebtorSelection[]
     */
    public function getDebtorSelectionByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorSelection',
            array(TblDebtorSelection::SERVICE_PEOPLE_PERSON => $tblPerson->getId()));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblItem   $tblItem
     * without Debtor
     *
     * @return false|TblDebtorSelection
     */
    public function getDebtorSelectionByPersonAndItem(TblPerson $tblPerson, TblItem $tblItem)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorSelection',
            array(TblDebtorSelection::SERVICE_PEOPLE_PERSON  => $tblPerson->getId(),
                  TblDebtorSelection::SERVICE_INVENTORY_ITEM => $tblItem->getId()));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblItem   $tblItem
     * without Debtor
     *
     * @return false|TblDebtorSelection
     */
    public function getDebtorSelectionByPersonAndItemWithoutDebtor(TblPerson $tblPerson, TblItem $tblItem)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorSelection',
            array(TblDebtorSelection::SERVICE_PEOPLE_PERSON  => $tblPerson->getId(),
                  TblDebtorSelection::SERVICE_INVENTORY_ITEM => $tblItem->getId(),
                  TblDebtorSelection::ATTR_TBL_DEBTOR        => null,));
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtorSelection
     */
    public function getDebtorSelectionById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorSelection', $Id);
    }

    /**
     * @return false|TblDebtorSelection[]
     */
    public function getDebtorSelectionAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorSelection');
    }

    public function checkDebtorSelectionDebtor(TblDebtorSelection $tblDebtorSelection)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorSelection',
            array(TblDebtorSelection::SERVICE_PEOPLE_PERSON  => $tblDebtorSelection->getServicePeoplePerson()->getId(),
                  TblDebtorSelection::SERVICE_INVENTORY_ITEM => $tblDebtorSelection->getServiceInventoryItem()->getId(),
                  TblDebtorSelection::ATTR_TBL_DEBTOR        => null,
            ));
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return bool
     */
    public function removeBanking(
        TblDebtor $tblDebtor
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $EntityItems = $Manager->getEntity('TblDebtor')
            ->findBy(array(TblDebtor::ATTR_DEBTOR_NUMBER => $tblDebtor->getId()));
        if (null !== $EntityItems) {
            foreach ($EntityItems as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
            }
        }

        $Entity = $Manager->getEntity('TblDebtor')->findOneBy(array('Id' => $tblDebtor->getId()));
        if (null !== $Entity) {
            /**@var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBankAccount $tblAccount
     *
     * @return bool
     */
    public function destroyAccount(TblBankAccount $tblAccount)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntityById('TblAccount', $tblAccount->getId());
        if (null !== $Entity) {
            /**@var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBankReference $tblBankReference
     *
     * @return bool
     */
    public function deactivateReference(TblBankReference $tblBankReference)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBankReference $Entity */
        $Entity = $Manager->getEntityById('TblBankReference', $tblBankReference->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setVoid(true);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPerson      $serviceTblPerson
     * @param TblPerson      $serviceTblPersonPayers
     * @param TblPaymentType $serviceBalance_PaymentType
     * @param TblItem        $serviceItem_Item
     *
     * @return null|object|TblDebtorSelection
     */
    public function createDebtorSelection(
        TblPerson $serviceTblPerson,
        TblPerson $serviceTblPersonPayers,
        TblPaymentType $serviceBalance_PaymentType,
        TblItem $serviceItem_Item
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblDebtorSelection')->findOneBy(array(
            TblDebtorSelection::SERVICE_PEOPLE_PERSON  => $serviceTblPerson->getId(),
//            TblDebtorSelection::SERVICE_PEOPLE_PERSON_PAYERS => $serviceTblPersonPayers->getId(),
//            TblDebtorSelection::SERVICE_BALANCE_PAYMENT_TYPE => $serviceBalance_PaymentType->getId(),
            TblDebtorSelection::SERVICE_INVENTORY_ITEM => $serviceItem_Item->getId(),
        ));

        if (null === $Entity) {
            $Entity = new TblDebtorSelection();
            $Entity->setServicePeoplePerson($serviceTblPerson);
            $Entity->setServicePeoplePersonPayers($serviceTblPersonPayers);
            $Entity->setServicePaymentType($serviceBalance_PaymentType);
            $Entity->setServiceInventoryItem($serviceItem_Item);

            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    public function UpdateDebtorSelection(
        TblDebtorSelection $tblDebtorSelection,
        TblDebtor $tblDebtor,
        TblBankAccount $tblBankAccount = null,
        TblBankReference $tblBankReference = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $this->getCachedEntityById(__METHOD__, $Manager, 'TblDebtorSelection', $tblDebtorSelection->getId());

        if (null !== $Entity) {
            /** @var TblDebtorSelection $Entity */
            $Protocol = clone $Entity;
            $Entity->setTblDebtor($tblDebtor);
            $Entity->setTblBankAccount($tblBankAccount);
            $Entity->setTblBankReference($tblBankReference);

            $Manager->saveEntity($Entity);

            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return $Entity;
        }
        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $DebtorNumber
     *
     * @return null|TblDebtor
     */
    public function createDebtor(TblPerson $tblPerson, $DebtorNumber)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblDebtor')->findOneBy(array(
            TblDebtor::ATTR_DEBTOR_NUMBER => $DebtorNumber,
        ));

        if (null === $Entity) {
            $Entity = new TblDebtor();
            $Entity->setDebtorNumber($DebtorNumber);
            $Entity->setServicePeoplePerson($tblPerson);

            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblDebtor $tblDebtor
     * @param           $DebtorNumber
     *
     * @return bool|TblDebtor
     */
    public function updateDebtor(TblDebtor $tblDebtor, $DebtorNumber)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntityById('TblDebtor', $tblDebtor->getId());

        /** @var TblDebtor $Entity */
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDebtorNumber($DebtorNumber);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return $Entity;
        }
        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $BankName
     * @param           $Owner
     * @param           $CashSign
     * @param           $IBAN
     * @param           $BIC
     *
     * @return TblBankAccount
     */
    public function createAccount(
//        $LeadTimeFirst,
//        $LeadTimeFollow,
        TblPerson $tblPerson,
        $BankName,
        $Owner,
        $CashSign,
        $IBAN,
        $BIC
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblBankAccount();
//        $Entity->setLeadTimeFirst($LeadTimeFirst);
//        $Entity->setLeadTimeFollow($LeadTimeFollow);
        $Entity->setBankName($BankName);
        $Entity->setOwner($Owner);
        $Entity->setCashSign($CashSign);
        $Entity->setIBAN($IBAN);
        $Entity->setBIC($BIC);
        $Entity->setServicePeoplePerson($tblPerson);

        $Manager->saveEntity($Entity);

        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @return bool|TblDebtor[]
     */
    public function getDebtorAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtor');
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $Reference
     * @param bool      $ReferenceDate
     *
     * @return TblBankReference
     */
    public function createReference(
        TblPerson $tblPerson,
        $Reference,
        $ReferenceDate = false

    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblBankReference();
        $Entity->setReference($Reference);
        $Entity->setServicePeoplePerson($tblPerson);
        $Entity->setVoid(false);
        if ($ReferenceDate) {
            $Entity->setReferenceDate(new \DateTime($ReferenceDate));
        } else {
            date_default_timezone_set('Europe/Berlin');
            $Entity->setReferenceDate(new \DateTime('now'));
        }
        $Manager->saveEntity($Entity);

        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @param TblBankReference $tblBankReference
     * @param                  $Date
     *
     * @return bool
     */
    public function updateReference(TblBankReference $tblBankReference, $Date)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBankReference $Entity */
        $Entity = $Manager->getEntityById('TblBankReference', $tblBankReference->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setReferenceDate(new \DateTime($Date));
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBankAccount $tblBankAccount
     * @param                $Owner
     * @param                $IBAN
     * @param                $BIC
     * @param                $CashSign
     * @param                $BankName
     *
     * @return bool
     */
    public function updateAccount(
        TblBankAccount $tblBankAccount,
        $Owner,
        $IBAN,
        $BIC,
        $CashSign,
        $BankName
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBankAccount $Entity */
        $Entity = $Manager->getEntityById('TblBankAccount', $tblBankAccount->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setOwner($Owner);
            $Entity->setIBAN($IBAN);
            $Entity->setBIC($BIC);
            $Entity->setCashSign($CashSign);
            $Entity->setBankName($BankName);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblBankAccount[]
     */
    public function getBankAccountByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankAccount',
            array(TblBankAccount::SERVICE_TBL_PERSON => $tblPerson->getId()));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblBankReference[]
     */
    public function getBankReferenceByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankReference',
            array(TblBankReference::SERVICE_TBL_PERSON => $tblPerson->getId(),
                  TblBankReference::ATTR_IS_VOID       => false));
    }
}
