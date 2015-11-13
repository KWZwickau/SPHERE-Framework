<?php

namespace SPHERE\Application\Billing\Accounting\Banking\Service;

use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtorCommodity;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblReference;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        /**
         * TblPayment
         */
        $this->createPaymentType('SEPA-Lastschrift');
        $this->createPaymentType('SEPA-Überweisung');
        $this->createPaymentType('Bar');
    }

    /**
     * @param $PaymentType
     *
     * @return TblPaymentType|null|object
     */
    public function createPaymentType($PaymentType)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblPaymentType')->findOneBy(array(TblPaymentType::ATTR_NAME => $PaymentType));
        if (null === $Entity) {
            $Entity = new TblPaymentType();
            $Entity->setName($PaymentType);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
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
     * @param TblDebtor $tblDebtor
     *
     * @return bool|TblAccount
     */
    public function getActiveAccountByDebtor(TblDebtor $tblDebtor)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblAccount')->findOneBy
        (array(TblAccount::ATTR_TBL_DEBTOR => $tblDebtor->getId(),
               TblAccount::ATTR_TBL_ACTIVE => true));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $ServiceManagement_Person
     *
     * @return TblDebtor[]|bool
     */
    public function getDebtorByServiceManagementPerson($ServiceManagement_Person)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblDebtor')->findBy(array(TblDebtor::ATTR_SERVICE_MANAGEMENT_PERSON => $ServiceManagement_Person));
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
            ->findBy(array(TblDebtor::ATTR_SERVICE_MANAGEMENT_PERSON => $tblPerson->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return bool|TblDebtorCommodity[]
     */
    public function getCommodityDebtorAllByDebtor(TblDebtor $tblDebtor)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblDebtorCommodity')
            ->findBy(array(TblDebtorCommodity::ATTR_TBL_DEBTOR => $tblDebtor->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param $Id
     *
     * @return bool|TblDebtorCommodity
     */
    public function getDebtorCommodityById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblDebtorCommodity', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Id
     *
     * @return bool|TblAccount
     */
    public function getAccountById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblAccount', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return TblAccount[]|bool
     */
    public function getAccountAll()
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblAccount')->findAll();
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return TblAccount[]|bool
     */
    public function getAccountByDebtor(TblDebtor $tblDebtor)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblAccount')
            ->findBy(array(TblAccount::ATTR_TBL_DEBTOR => $tblDebtor->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool| TblReference
     */
    public function getReferenceByAccount(TblAccount $tblAccount)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblReference')
            ->findBy(array(TblReference::ATTR_TBL_ACCOUNT => $tblAccount->getId()));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return array|bool
     */
    public function getReferenceActiveByAccount(TblAccount $tblAccount)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblReference')
            ->findBy(array(TblReference::ATTR_TBL_ACCOUNT => $tblAccount->getId(),
                           TblReference::ATTR_IS_VOID     => false));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblDebtorCommodity $tblDebtorCommodity
     *
     * @return bool
     */
    public function removeCommodityToDebtor(
        TblDebtorCommodity $tblDebtorCommodity
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblDebtorCommodity')->findOneBy(
            array(
                'Id' => $tblDebtorCommodity->getId()
            ));
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
     * @param TblDebtor    $tblDebtor
     * @param TblCommodity $tblCommodity
     *
     * @return TblDebtorCommodity
     */
    public function addCommodityToDebtor(
        TblDebtor $tblDebtor,
        TblCommodity $tblCommodity
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblDebtorCommodity')->findOneBy(
            array(
                TblDebtorCommodity::ATTR_TBL_DEBTOR                => $tblDebtor->getId(),
                TblDebtorCommodity::ATTR_SERVICE_BILLING_COMMODITY => $tblCommodity->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblDebtorCommodity();
            $Entity->setTblDebtor($tblDebtor);
            $Entity->setServiceBillingCommodity($tblCommodity);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }
        return $Entity;
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

        $EntityReferenceList = $Manager->getEntity('TblReference')
            ->findBy(array(TblReference::ATTR_TBL_DEBTOR => $tblDebtor->getId()));
        if (null !== $EntityReferenceList) {
            foreach ($EntityReferenceList as $EntityReference) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $EntityReference);
                $Manager->killEntity($EntityReference);
            }
        }

        $EntityItemsDebtorCommodity = $Manager->getEntity('TblDebtorCommodity')
            ->findBy(array(TblDebtorCommodity::ATTR_TBL_DEBTOR => $tblDebtor->getId()));
        if (null !== $EntityItemsDebtorCommodity) {
            foreach ($EntityItemsDebtorCommodity as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
            }
        }

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
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function destroyAccount(TblAccount $tblAccount)
    {

        $Manager = $this->getConnection()->getEntityManager();

        if (Banking::useService()->getReferenceActiveByAccount($tblAccount)) {
            $tblReferenceList = Banking::useService()->getReferenceActiveByAccount($tblAccount);
            foreach ($tblReferenceList as $tblReference) {
                $this->deactivateReference($tblReference);
            }
        }

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
     * @param TblReference $tblReference
     *
     * @return bool
     */
    public function deactivateReference(TblReference $tblReference)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblReference $Entity */
        $Entity = $Manager->getEntityById('TblReference', $tblReference->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsVoid(true);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return bool
     */
    public function removeReference(TblDebtor $tblDebtor)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $EntityList = $Manager->getEntity('TblReference')->findBy(array(TblReference::ATTR_TBL_DEBTOR => $tblDebtor->getId()));

        if (null !== $EntityList) {
            foreach ($EntityList as $Entity) {
                Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                    $Entity);
                $Manager->killEntity($Entity);
            }
            return true;
        }
        return false;
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function deactivateAccount(TblAccount $tblAccount)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblAccount $Entity */
        $Entity = $Manager->getEntityById('TblAccount', $tblAccount->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setActive(false);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function activateAccount(TblAccount $tblAccount)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblAccount $Entity */
        $Entity = $Manager->getEntityById('TblAccount', $tblAccount->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setActive(true);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDebtor      $tblDebtor
     * @param TblPaymentType $paymentType
     *
     * @return bool
     */
    public function changePaymentType(TblDebtor $tblDebtor, TblPaymentType $paymentType)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblDebtor $Entity */
        $Entity = $Manager->getEntityById('TblDebtor', $tblDebtor->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setPaymentType($paymentType);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDebtor    $tblDebtor
     * @param TblCommodity $tblCommodity
     *
     * @return TblDebtorCommodity[]|bool
     */
    public function getDebtorCommodityAllByDebtorAndCommodity(TblDebtor $tblDebtor, TblCommodity $tblCommodity)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblDebtorCommodity')
            ->findBy(array(
                TblDebtorCommodity::ATTR_TBL_DEBTOR                => $tblDebtor->getId(),
                TblDebtorCommodity::ATTR_SERVICE_BILLING_COMMODITY => $tblCommodity->getId()
            ));
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param                $DebtorNumber
     * @param                $Description
     * @param                $ServiceManagement_Person
     * @param TblPaymentType $PaymentType
     *
     * @return TblDebtor
     */
    public function createDebtor(
        $DebtorNumber,
        $Description,
        $ServiceManagement_Person,
        $PaymentType
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblDebtor();
        $Entity->setDebtorNumber($DebtorNumber);
        $Entity->setDescription($Description);
        $Entity->setServiceManagementPerson($ServiceManagement_Person);
        $Entity->setPaymentType(Banking::useService()->getPaymentTypeById($PaymentType));

        $Manager->saveEntity($Entity);

        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @param            $BankName
     * @param            $Owner
     * @param            $CashSign
     * @param            $IBAN
     * @param            $BIC
     * @param bool|false $Active
     * @param TblDebtor  $Debtor
     *
     * @return TblAccount
     */
    public function createAccount(
//        $LeadTimeFirst,
//        $LeadTimeFollow,
        $BankName,
        $Owner,
        $CashSign,
        $IBAN,
        $BIC,
        $Active = false,
        TblDebtor $Debtor
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblAccount();
//        $Entity->setLeadTimeFirst($LeadTimeFirst);
//        $Entity->setLeadTimeFollow($LeadTimeFollow);
        $Entity->setBankName($BankName);
        $Entity->setOwner($Owner);
        $Entity->setCashSign($CashSign);
        $Entity->setIBAN($IBAN);
        $Entity->setBIC($BIC);
        $Entity->setActive($Active);
        $Entity->setTblDebtor($Debtor);

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

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblDebtor')->findAll();
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param              $Reference
     * @param              $DebtorNumber
     * @param              $ReferenceDate
     * @param TblCommodity $tblCommodity
     * @param TblAccount   $tblAccount
     *
     * @return TblReference
     */
    public function createReference(
        $Reference,
        $DebtorNumber,
        $ReferenceDate,
        TblCommodity $tblCommodity,
        TblAccount $tblAccount
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblReference();
        $Entity->setReference($Reference);
        $Entity->setIsVoid(false);
        $Entity->setServiceTblDebtor(Banking::useService()->getDebtorByDebtorNumber($DebtorNumber));
        $Entity->setServiceBillingCommodity($tblCommodity);
        if ($ReferenceDate) {
            $Entity->setReferenceDate(new \DateTime($ReferenceDate));
        } else {
            date_default_timezone_set('Europe/Berlin');
            $Entity->setReferenceDate(new \DateTime('now'));
        }
        $Entity->setServiceTblAccount($tblAccount);
        $Manager->saveEntity($Entity);

        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
            $Entity);

        return $Entity;
    }

    /**
     * @param TblDebtor $tblDebtor
     * @param           $Description
     *
     * @return bool
     */
    public function updateDebtor(
        TblDebtor $tblDebtor,
        $Description
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblDebtor $Entity */
        $Entity = $Manager->getEntityById('TblDebtor', $tblDebtor->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblReference $tblReference
     * @param              $Date
     *
     * @return bool
     */
    public function updateReference(TblReference $tblReference, $Date)
    {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblReference $Entity */
        $Entity = $Manager->getEntityById('TblReference', $tblReference->getId());
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
     * @param TblAccount $tblAccount
     * @param            $Owner
     * @param            $IBAN
     * @param            $BIC
     * @param            $CashSign
     * @param            $BankName
     * @param            $DebtorId
     *
     * @return bool
     */
    public function updateAccount(
        TblAccount $tblAccount,
        $Owner,
        $IBAN,
        $BIC,
        $CashSign,
        $BankName,
        $DebtorId
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblAccount $Entity */
        $Entity = $Manager->getEntityById('TblAccount', $tblAccount->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setOwner($Owner);
            $Entity->setIBAN($IBAN);
            $Entity->setBIC($BIC);
            $Entity->setCashSign($CashSign);
            $Entity->setBankName($BankName);
            $Entity->setTblDebtor(Banking::useService()->getDebtorById($DebtorId));
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                $Protocol,
                $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return bool|TblReference[]
     */
    public function getReferenceByDebtor(TblDebtor $tblDebtor)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblReference')
            ->findBy(array(TblReference::ATTR_TBL_DEBTOR => $tblDebtor->getId(), TblReference::ATTR_IS_VOID => false));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $tblReference
     *
     * @return bool|TblReference
     */
    public function getReferenceById($tblReference)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblReference', $tblReference);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblDebtor    $tblDebtor
     * @param TblCommodity $tblCommodity
     *
     * @return bool|TblReference
     */
    public function getReferenceByDebtorAndCommodity(TblDebtor $tblDebtor, TblCommodity $tblCommodity)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblReference')->findOneBy(array(
            TblReference::ATTR_TBL_DEBTOR                => $tblDebtor->getId(),
            TblReference::ATTR_SERVICE_BILLING_COMMODITY => $tblCommodity->getId(),
            TblReference::ATTR_IS_VOID                   => false
        ));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblAccount   $tblAccount
     * @param TblCommodity $tblCommodity
     *
     * @return bool|TblReference
     */
    public function getReferenceByAccountAndCommodity(TblAccount $tblAccount, TblCommodity $tblCommodity)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblReference')->findOneBy(array(
            TblReference::ATTR_TBL_ACCOUNT               => $tblAccount->getId(),
            TblReference::ATTR_SERVICE_BILLING_COMMODITY => $tblCommodity->getId(),
            TblReference::ATTR_IS_VOID                   => false
        ));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Reference
     *
     * @return bool|TblReference
     */
    public function getReferenceByReference($Reference)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblReference')
            ->findOneBy(array(TblReference::ATTR_REFERENCE => $Reference));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Reference
     *
     * @return bool|TblReference
     */
    public function getReferenceByReferenceActive($Reference)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblReference')
            ->findOneBy(array(
                TblReference::ATTR_REFERENCE => $Reference,
                TblReference::ATTR_IS_VOID   => false
            ));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblPaymentType[]
     */
    public function getPaymentTypeAll()
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblPaymentType')->findAll();

        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $PaymentType
     *
     * @return bool|null|$tblPaymentType
     */
    public function getPaymentTypeByName($PaymentType)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblPaymentType')->findOneBy(array(TblPaymentType::ATTR_NAME => $PaymentType));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Id
     *
     * @return bool|TblPaymentType
     */
    public function getPaymentTypeById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblPaymentType', $Id);
        return ( null === $Entity ? false : $Entity );
    }
}
