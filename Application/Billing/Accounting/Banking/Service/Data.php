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
     * @return false|TblDebtor
     */
    public function getDebtorById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtor', $Id);
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
     * @param $Id
     *
     * @return bool|TblBankAccount
     */
    public function getBankAccountById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankAccount', $Id);
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
     * @return bool|TblDebtor[]
     */
    public function getDebtorAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtor');
    }

    /**
     * @return false|TblBankReference[]
     */
    public function getBankReferenceAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankReference');
    }

    /**
     * @param TblBankAccount $tblBankAccount
     *
     * @return false|TblBankReference[]
     */
    public function getBankReferenceByBankAccount(TblBankAccount $tblBankAccount)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankReference',
            array(TblBankReference::ATTR_TBL_BANK_ACCOUNT => $tblBankAccount->getId()));
    }

    /**
     * @return TblBankAccount[]|bool
     */
    public function getBankAccountAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankAccount');
    }

    /**
     * @return false|TblDebtorSelection[]
     */
    public function getDebtorSelectionAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorSelection');
    }

    /**
     * @param $DebtorNumber
     *
     * @return TblDebtor|bool
     */
    public function getDebtorByDebtorNumber($DebtorNumber)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtor',
            array(TblDebtor::ATTR_DEBTOR_NUMBER => $DebtorNumber));
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
            array(TblBankReference::ATTR_REFERENCE_NUMBER => $Reference));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblDebtor[]
     */
    public function getDebtorByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtor',
            array(TblDebtor::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblBankReference[]
     */
    public function getBankReferenceByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankReference',
            array(TblBankReference::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()));
    }

    /**
     * @param $Reference
     *
     * @return false|TblBankReference
     */
    public function getBankReferenceByNumber($Reference)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankReference',
            array(TblBankReference::ATTR_REFERENCE_NUMBER => $Reference));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblDebtorSelection[]
     */
    public function getDebtorSelectionByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorSelection',
            array(TblDebtorSelection::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()));
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
            array(TblDebtorSelection::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                  TblDebtorSelection::ATTR_SERVICE_TBL_ITEM   => $tblItem->getId()));
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
            array(TblDebtorSelection::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
                  TblDebtorSelection::ATTR_SERVICE_TBL_ITEM   => $tblItem->getId(),
                  TblDebtorSelection::ATTR_TBL_DEBTOR         => null,));
    }

    /**
     * @param TblDebtorSelection $tblDebtorSelection
     *
     * @return false|TblDebtorSelection
     */
    public function checkDebtorSelectionDebtor(TblDebtorSelection $tblDebtorSelection)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorSelection',
            array(TblDebtorSelection::ATTR_SERVICE_TBL_PERSON => $tblDebtorSelection->getServiceTblPerson()->getId(),
                  TblDebtorSelection::ATTR_SERVICE_TBL_ITEM   => $tblDebtorSelection->getServiceTblInventoryItem()->getId(),
                  TblDebtorSelection::ATTR_TBL_DEBTOR         => null,
            ));
    }

    /**
     * @param           $BankName
     * @param           $Owner
     * @param           $CashSign
     * @param           $IBAN
     * @param           $BIC
     *
     * @return TblBankAccount
     */
    public function createBankAccount(
        $BankName,
        $Owner,
        $CashSign,
        $IBAN,
        $BIC
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblBankAccount')->findOneBy(array(
            TblBankAccount::ATTR_IBAN => $IBAN
        ));

        if ($Entity === null) {
            $Entity = new TblBankAccount();
            $Entity->setBankName($BankName);
            $Entity->setOwner($Owner);
            $Entity->setCashSign($CashSign);
            $Entity->setIBAN($IBAN);
            $Entity->setBIC($BIC);

            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
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
            $Entity->setServiceTblPerson($tblPerson);

            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $Reference
     * @param null      $CreditorId
     * @param bool      $ReferenceDate
     * @param null      $BankName
     * @param null      $Owner
     * @param null      $CashSign
     * @param null      $IBAN
     * @param null      $BIC
     *
     * @return TblBankReference
     */
    public function createReference(
        TblPerson $tblPerson,
        $Reference,
        $CreditorId = null,
        $ReferenceDate = false,
        $BankName = null,
        $Owner = null,
        $CashSign = null,
        $IBAN = null,
        $BIC = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblBankReference();
        $Entity->setReference($Reference);
        $Entity->setCreditorId($CreditorId);
        $Entity->setServiceTblPerson($tblPerson);
        if ($ReferenceDate) {
            $Entity->setReferenceDate(new \DateTime($ReferenceDate));
        } else {
            date_default_timezone_set('Europe/Berlin');
            $Entity->setReferenceDate(new \DateTime('now'));
        }
        $Entity->setBankName($BankName);
        $Entity->setOwner($Owner);
        $Entity->setCashSign($CashSign);
        $Entity->setIBAN($IBAN);
        $Entity->setBIC($BIC);

        $Manager->saveEntity($Entity);

        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
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
            TblDebtorSelection::ATTR_SERVICE_TBL_PERSON => $serviceTblPerson->getId(),
//            TblDebtorSelection::SERVICE_PEOPLE_PERSON_PAYERS => $serviceTblPersonPayers->getId(),
//            TblDebtorSelection::SERVICE_BALANCE_PAYMENT_TYPE => $serviceBalance_PaymentType->getId(),
            TblDebtorSelection::ATTR_SERVICE_TBL_ITEM   => $serviceItem_Item->getId(),
        ));

        if (null === $Entity) {
            $Entity = new TblDebtorSelection();
            $Entity->setServiceTblPerson($serviceTblPerson);
            $Entity->setServiceTblPersonPayers($serviceTblPersonPayers);
            $Entity->setServiceTblPaymentType($serviceBalance_PaymentType);
            $Entity->setServiceTblInventoryItem($serviceItem_Item);

            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
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
    public function updateBankAccount(
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
     * @param TblBankReference $tblBankReference
     * @param                  $Reference
     * @param                  $CreditorId
     * @param                  $Date
     * @param null             $Owner
     * @param null             $BankName
     * @param null             $CashSign
     * @param null             $IBAN
     * @param null             $BIC
     *
     * @return bool
     */
    public function updateReference(
        TblBankReference $tblBankReference,
        $Reference,
        $CreditorId,
        $Date,
        $Owner = null,
        $BankName = null,
        $CashSign = null,
        $IBAN = null,
        $BIC = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBankReference $Entity */
        $Entity = $Manager->getEntityById('TblBankReference', $tblBankReference->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setReference($Reference);
            $Entity->setCreditorId($CreditorId);
            $Entity->setReferenceDate(new \DateTime($Date));
            $Entity->setOwner($Owner);
            $Entity->setBankName($BankName);
            $Entity->setCashSign($CashSign);
            $Entity->setIBAN($IBAN);
            $Entity->setBIC($BIC);
            $Entity->setOwner($Owner);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDebtorSelection    $tblDebtorSelection
     * @param TblDebtor             $tblDebtor
     * @param TblBankReference|null $tblBankReference
     *
     * @return bool|false|TblDebtorSelection
     */
    public function updateDebtorSelection(
        TblDebtorSelection $tblDebtorSelection,
        TblDebtor $tblDebtor,
        TblBankReference $tblBankReference = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $this->getCachedEntityById(__METHOD__, $Manager, 'TblDebtorSelection', $tblDebtorSelection->getId());

        if (null !== $Entity) {
            /** @var TblDebtorSelection $Entity */
            $Protocol = clone $Entity;
            $Entity->setTblDebtor($tblDebtor);
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
     * @param TblDebtorSelection    $tblDebtorSelection
     * @param TblPerson             $tblPersonPayers
     * @param TblPaymentType        $tblPaymentType
     * @param TblDebtor|null        $tblDebtor
     * @param TblBankReference|null $tblBankReference
     *
     * @return bool|false|TblDebtorSelection
     */
    public function changeDebtorSelection(
        TblDebtorSelection $tblDebtorSelection,
        TblPerson $tblPersonPayers,
        TblPaymentType $tblPaymentType,
        TblDebtor $tblDebtor = null,
        TblBankReference $tblBankReference = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $this->getCachedEntityById(__METHOD__, $Manager, 'TblDebtorSelection', $tblDebtorSelection->getId());

        if (null !== $Entity) {
            /** @var TblDebtorSelection $Entity */
            $Protocol = clone $Entity;
            $Entity->setServiceTblPersonPayers($tblPersonPayers);
            $Entity->setServiceTblPaymentType($tblPaymentType);
            if ($tblPaymentType->getName() !== 'SEPA-Lastschrift') {
                $Entity->setTblDebtor($tblDebtor);
                $Entity->setTblBankReference($tblBankReference);
            }
            $Manager->saveEntity($Entity);

            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return $Entity;
        }
        return false;
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
     * @param TblBankReference $tblBankReference
     *
     * @return bool
     */
    public function removeReference(TblBankReference $tblBankReference)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBankReference $Entity */
        $Entity = $Manager->getEntityById('TblBankReference', $tblBankReference->getId());
        if (null !== $Entity) {
            $Manager->removeEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDebtorSelection $tblDebtorSelection
     *
     * @return bool
     */
    public function destroyDebtorSelection(TblDebtorSelection $tblDebtorSelection)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblDebtorSelection $Entity */
        $Entity = $Manager->getEntityById('TblDebtorSelection', $tblDebtorSelection->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBankAccount $tblBankAccount
     *
     * @return bool
     */
    public function destroyBankAccount(TblBankAccount $tblBankAccount)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntityById('TblBankAccount', $tblBankAccount->getId());
        if (null !== $Entity) {
            /**@var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}
