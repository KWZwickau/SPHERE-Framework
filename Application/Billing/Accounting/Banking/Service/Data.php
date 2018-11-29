<?php

namespace SPHERE\Application\Billing\Accounting\Banking\Service;

use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankAccount;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtorNumber;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtorSelection;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblPersonBilling;
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
     * @return false|TblBankAccount
     */
    public function getBankAccountById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankAccount', $Id);
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
     * @return false|TblDebtor
     */
    public function getDebtorById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtor', $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtorNumber
     */
    public function getDebtorNumberById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorNumber', $Id);
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
     * @param $Id
     *
     * @return false|TblPersonBilling
     */
    public function getPersonBillingById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPersonBilling', $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblBankAccount
     */
    public function getBankAccountAll($Id)
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankAccount', $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblBankReference
     */
    public function getBankReferenceAll($Id)
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBankReference', $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtor
     */
    public function getDebtorAll($Id)
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtor', $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtorNumber
     */
    public function getDebtorNumberAll($Id)
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorNumber', $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtorSelection
     */
    public function getDebtorSelectionAll($Id)
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtorSelection', $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblPersonBilling
     */
    public function getPersonBillingAll($Id)
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPersonBilling', $Id);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblDebtor[]
     */
    public function getDebtorAllByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDebtor',
            array(TblDebtor::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()));
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
     * @param TblDebtor $tblDebtor
     *
     * @return bool
     */
    public function removeDebtor(
        TblDebtor $tblDebtor
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblDebtor')->findOneBy(array('Id' => $tblDebtor->getId()));
        if (null !== $Entity) {
            /**@var Element $Entity */
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
            $Manager->removeEntity($Entity);
            return true;
        }
        return false;
    }
}
