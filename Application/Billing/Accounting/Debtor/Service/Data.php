<?php

namespace SPHERE\Application\Billing\Accounting\Debtor\Service;

use DateTime;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblBankAccount;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblDebtorNumber;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblDebtorPeriodType;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblDebtorSelection;
use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemVariant;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Accounting\Debtor\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        $this->createDebtorPeriodType(TblDebtorPeriodType::ATTR_MONTH);
        $this->createDebtorPeriodType(TblDebtorPeriodType::ATTR_YEAR);
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtorNumber
     */
    public function getDebtorNumberById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorNumber',
            $Id);
    }

    /**
     * @param $Number
     *
     * @return false|TblDebtorNumber
     */
    public function getDebtorNumberByNumber($Number)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorNumber',
            array(
                TblDebtorNumber::ATTR_DEBTOR_NUMBER => $Number
            ));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblDebtorNumber[]
     */
    public function getDebtorNumberByPerson(TblPerson $tblPerson, $isForced = false)
    {
        if($isForced){
            return $this->getForceEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorNumber',
                array(
                    TblDebtorNumber::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
                ));
        }
        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorNumber',
            array(
                TblDebtorNumber::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
    }

    /**
     * @param $Id
     *
     * @return false|TblBankAccount
     */
    public function getBankAccountById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankAccount',
            $Id);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblBankAccount[]
     */
    public function getBankAccountAllByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankAccount',
            array(
                TblBankAccount::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
    }

    /**
     * @param $Id
     *
     * @return false|TblBankReference
     */
    public function getBankReferenceById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankReference',
            $Id);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblBankReference[]
     */
    public function getBankReferenceByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankReference',
            array(
                TblBankReference::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
    }

    /**
     * @param $ReferenceNumber
     *
     * @return false|TblBankReference
     */
    public function getBankReferenceByReference($ReferenceNumber)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankReference',
            array(
                TblBankReference::ATTR_REFERENCE_NUMBER => $ReferenceNumber
            ));
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtorPeriodType
     */
    public function getDebtorPeriodTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorPeriodType',
            $Id);
    }

    /**
     * @param string $Name
     *
     * @return false|TblDebtorPeriodType
     */
    public function getDebtorPeriodTypeByName($Name = '')
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorPeriodType',
            array(
                TblDebtorPeriodType::ATTR_NAME => $Name
            ));
    }

    /**
     * @return TblDebtorPeriodType[]|false
     */
    public function getDebtorPeriodTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorPeriodType');
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtorSelection
     */
    public function getDebtorSelectionById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorSelection',
            $Id);
    }

    /**
     * @param TblPerson $tblPersonCauser
     *
     * @return false|TblDebtorSelection[]
     */
    public function getDebtorSelectionByPersonCauser(TblPerson $tblPersonCauser)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDebtorSelection',
            array(
                TblDebtorSelection::ATTR_SERVICE_TBL_PERSON_CAUSER => $tblPersonCauser->getId()
            ));
    }

    /**
     * @param TblPerson $tblPersonDebtor
     *
     * @return false|TblDebtorSelection[]
     */
    public function getDebtorSelectionByPersonDebtor(TblPerson $tblPersonDebtor)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDebtorSelection',
            array(
                TblDebtorSelection::ATTR_SERVICE_TBL_PERSON_DEBTOR => $tblPersonDebtor->getId()
            ));
    }

    /**
     * @param TblItem $tblItem
     *
     * @return false|TblDebtorSelection[]
     */
    public function getDebtorSelectionByItem(TblItem $tblItem)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDebtorSelection',
            array(
                TblDebtorSelection::ATTR_SERVICE_TBL_ITEM => $tblItem->getId()
            ));
    }

    /**
     * @param TblItem $tblItem
     *
     * @return false|TblDebtorSelection
     */
    public function getDebtorSelectionFindTestByItem(TblItem $tblItem)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDebtorSelection',
            array(
                TblDebtorSelection::ATTR_SERVICE_TBL_ITEM => $tblItem->getId()
            ));
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblItem   $tblItem
     *
     * @return false|TblDebtorSelection[]
     */
    public function getDebtorSelectionByPersonCauserAndItem(TblPerson $tblPerson, TblItem $tblItem)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDebtorSelection',
            array(
                TblDebtorSelection::ATTR_SERVICE_TBL_PERSON_CAUSER => $tblPerson->getId(),
                TblDebtorSelection::ATTR_SERVICE_TBL_ITEM          => $tblItem->getId()
            ));
    }

    /**
     * @param TblBankReference $tblBankReference
     *
     * @return false|TblDebtorSelection[]
     */
    public function getDebtorSelectionAllByBankReference(TblBankReference $tblBankReference)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDebtorSelection',
            array(
                TblDebtorSelection::ATTR_TBL_BANK_REFERENCE => $tblBankReference->getId()
            ));
    }

    /**
     * @param TblBankAccount $tblBankAccount
     *
     * @return false|TblDebtorSelection[]
     */
    public function getDebtorSelectionAllByBankAccount(TblBankAccount $tblBankAccount)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblDebtorSelection',
            array(
                TblDebtorSelection::ATTR_TBL_BANK_ACCOUNT => $tblBankAccount->getId()
            ));
    }

    /**
     * @return false|TblDebtorNumber[]
     */
    public function getDebtorNumberAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorNumber');
    }

    /**
     * @return false|TblBankAccount
     */
    public function getBankAccountAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankAccount');
    }

    /**
     * @param $Id
     *
     * @return false|TblBankReference[]
     */
    public function getBankReferenceAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankReference');
    }

    /**
     * @return false|TblDebtorSelection
     */
    public function getDebtorSelectionAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorSelection');
    }

    /**
     * @return string
     */
    public function getDebtorSelectionCount()
    {

        return $this->getForceEntityCountBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorSelection'
            , array());
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
     * @param string    $DebtorNumber
     *
     * @return null|TblDebtorNumber
     */
    public function createDebtorNumber(TblPerson $tblPerson, $DebtorNumber)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDebtorNumber')->findOneBy(array(
            TblDebtorNumber::ATTR_DEBTOR_NUMBER => $DebtorNumber
        ));

        if($Entity === null){
            $Entity = new TblDebtorNumber();
            $Entity->setDebtorNumber($DebtorNumber);
            $Entity->setServiceTblPerson($tblPerson);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param string $Name
     *
     * @return null|TblDebtorPeriodType
     */
    public function createDebtorPeriodType($Name)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDebtorPeriodType')->findOneBy(array(
            TblDebtorPeriodType::ATTR_NAME => $Name
        ));

        if($Entity === null){
            $Entity = new TblDebtorPeriodType();
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $BankName
     * @param string    $IBAN
     * @param string    $BIC
     * @param string    $Owner
     *
     * @return null|TblBankAccount
     */
    public function createBankAccount(TblPerson $tblPerson, $BankName = '', $IBAN = '', $BIC = '', $Owner = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblBankAccount')->findOneBy(array(
            TblBankAccount::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblBankAccount::ATTR_IBAN               => $IBAN,
        ));

        if($Entity === null){
            $Entity = new TblBankAccount();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setBankName($BankName);
            $Entity->setIBAN($IBAN);
            $Entity->setBIC($BIC);
            $Entity->setOwner($Owner);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $ReferenceNumber
     * @param string    $Description
     * @param string    $ReferenceDate
     *
     * @return null|TblBankReference
     */
    public function createBankReference(TblPerson $tblPerson, $ReferenceNumber = '', $Description = '', $ReferenceDate = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblBankReference')->findOneBy(array(
            TblBankReference::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId(),
            TblBankReference::ATTR_REFERENCE_NUMBER   => $ReferenceNumber,
        ));

        if($Entity === null){
            $Entity = new TblBankReference();
            $Entity->setReference($ReferenceNumber);
            $Entity->setDescription($Description);
            $Entity->setReferenceDate(($ReferenceDate ? new DateTime($ReferenceDate) : new DateTime()));
            $Entity->setServiceTblPerson($tblPerson);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblPerson             $tblPersonCauser
     * @param TblPerson             $tblPerson
     * @param TblPaymentType        $tblPaymentType
     * @param TblItem               $tblItem
     * @param TblDebtorPeriodType   $tblDebtorPeriodType
     * @param string                $FromDate
     * @param string|null           $ToDate
     * @param TblItemVariant|null   $tblItemVariant
     * @param string                $Value
     * @param TblBankAccount|null   $tblBankAccount
     * @param TblBankReference|null $tblBankReference
     *
     * @return null|object|TblDebtorSelection
     */
    public function createDebtorSelection(
        TblPerson $tblPersonCauser,
        TblPerson $tblPerson,
        TblPaymentType $tblPaymentType,
        TblItem $tblItem,
        TblDebtorPeriodType $tblDebtorPeriodType,
        $FromDate,
        $ToDate = null,
        TblItemVariant $tblItemVariant = null,
        $Value = '0',
        TblBankAccount $tblBankAccount = null,
        TblBankReference $tblBankReference = null
    ){

        if($Value == ''){
            $Value = '0';
        }

        $Manager = $this->getConnection()->getEntityManager();
        // Es dürfen meherere Zahlungszuweisungen vorhanden sein.
//        $Entity = $Manager->getEntity('TblDebtorSelection')->findOneBy(array(
//            TblDebtorSelection::ATTR_SERVICE_TBL_ITEM          => $tblItem->getId(),
//            TblDebtorSelection::ATTR_SERVICE_TBL_PERSON_CAUSER => $tblPersonCauser->getId(),
//            TblDebtorSelection::ATTR_SERVICE_TBL_PERSON_DEBTOR => $tblPerson->getId(),
//        ));
//        if($Entity === null){
        $Entity = new TblDebtorSelection();
        $Entity->setServiceTblPersonCauser($tblPersonCauser);
        $Entity->setServiceTblPersonDebtor($tblPerson);
        $Entity->setServiceTblPaymentType($tblPaymentType);
        $Entity->setServiceTblItem($tblItem);
        $Entity->setTblDebtorPeriodType($tblDebtorPeriodType);
        $Entity->setFromDate(new DateTime($FromDate));
        if($ToDate){
            $Entity->setToDate(new DateTime($ToDate));
        }
        $Entity->setServiceTblItemVariant($tblItemVariant);

        $Entity->setValue($Value);
        $Entity->setTblBankAccount($tblBankAccount);
        $Entity->setTblBankReference($tblBankReference);

        $Manager->saveEntity($Entity);

        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);
//        }

        return $Entity;
    }

    /**
     * @param TblBankAccount $tblBankAccount
     * @param string         $BankName
     * @param string         $IBAN
     * @param string         $BIC
     * @param string         $Owner
     *
     * @return bool
     */
    public function updateBankAccount(
        TblBankAccount $tblBankAccount,
        $BankName = '',
        $IBAN = '',
        $BIC = '',
        $Owner = ''
    ){

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBankAccount $Entity */
        $Entity = $Manager->getEntityById('TblBankAccount', $tblBankAccount->getId());
        $Protocol = clone $Entity;
        if(null !== $Entity){
            $Entity->setBankName($BankName);
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
     * @param TblDebtorNumber $tblDebtorNumber
     * @param string          $Number
     *
     * @return bool
     */
    public function updateDebtorNumber(TblDebtorNumber $tblDebtorNumber, $Number = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblDebtorNumber $Entity */
        $Entity = $Manager->getEntityById('TblDebtorNumber', $tblDebtorNumber->getId());
        $Protocol = clone $Entity;
        if(null !== $Entity){
            $Entity->setDebtorNumber($Number);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBankReference $tblBankReference
     * @param string           $ReferenceNumber
     * @param string           $Description
     * @param string           $ReferenceDate
     *
     * @return bool
     */
    public function updateBankReference(TblBankReference $tblBankReference, $ReferenceNumber = '', $Description = '', $ReferenceDate = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBankReference $Entity */
        $Entity = $Manager->getEntityById('TblBankReference', $tblBankReference->getId());
        $Protocol = clone $Entity;
        if(null !== $Entity){
            $Entity->setReference($ReferenceNumber);
            $Entity->setDescription($Description);
            $Entity->setReferenceDate(($ReferenceDate ? new DateTime($ReferenceDate) : new DateTime()));
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
     * @param TblPerson             $tblPerson
     * @param TblPaymentType        $tblPaymentType
     * @param TblDebtorPeriodType   $tblDebtorPeriodType
     * @param DateTime             $FromDate
     * @param DateTime|null        $ToDate
     * @param TblItemVariant|null   $tblItemVariant
     * @param string                $Value
     * @param TblBankAccount|null   $tblBankAccount
     * @param TblBankReference|null $tblBankReference
     *
     * @return bool
     */
    public function updateDebtorSelection(
        TblDebtorSelection $tblDebtorSelection,
        TblPerson $tblPerson,
        TblPaymentType $tblPaymentType,
        TblDebtorPeriodType $tblDebtorPeriodType,
        DateTime $FromDate,
        DateTime $ToDate = null,
        TblItemVariant $tblItemVariant = null,
        $Value = '0',
        TblBankAccount $tblBankAccount = null,
        TblBankReference $tblBankReference = null
    ){

        if($Value == ''){
            $Value = '0';
        }

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblDebtorSelection $Entity */
        $Entity = $Manager->getEntityById('TblDebtorSelection', $tblDebtorSelection->getId());
        $Protocol = clone $Entity;
        if($Entity !== null){
            $Entity->setServiceTblPersonDebtor($tblPerson);
            $Entity->setServiceTblPaymentType($tblPaymentType);
            $Entity->setServiceTblItemVariant($tblItemVariant);
            $Entity->setTblDebtorPeriodType($tblDebtorPeriodType);
            $Entity->setFromDate($FromDate);
            $Entity->setToDate($ToDate);
            $Entity->setValue($Value);
            $Entity->setTblBankAccount($tblBankAccount);
            $Entity->setTblBankReference($tblBankReference);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }

        return false;
    }

    /**
     * @param TblDebtorNumber $tblDebtorNumber
     *
     * @return bool
     */
    public function removeDebtorNumber(TblDebtorNumber $tblDebtorNumber)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblDebtorNumber', $tblDebtorNumber->getId());
        if(null !== $Entity){
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblBankAccount $tblBankAccount
     *
     * @return bool
     */
    public function removeBankAccount(TblBankAccount $tblBankAccount)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblBankAccount', $tblBankAccount->getId());
        if(null !== $Entity){
            /** @var Element $Entity */
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
    public function removeBankReference(TblBankReference $tblBankReference)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblBankReference', $tblBankReference->getId());
        if(null !== $Entity){
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDebtorSelection $tblDebtorSelection
     *
     * @return bool
     */
    public function removeDebtorSelection(TblDebtorSelection $tblDebtorSelection)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntityById('TblDebtorSelection', $tblDebtorSelection->getId());
        if(null !== $Entity){
            /** @var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}
