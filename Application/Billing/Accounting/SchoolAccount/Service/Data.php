<?php

namespace SPHERE\Application\Billing\Accounting\SchoolAccount\Service;

use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankAccount;
use SPHERE\Application\Billing\Accounting\SchoolAccount\Service\Entity\TblSchoolAccount;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Accounting\SchoolAccount\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Id
     *
     * @return false|TblSchoolAccount
     */
    public function getSchoolAccountById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSchoolAccount', $Id);
    }

    /**
     * @return false|TblSchoolAccount[]
     */
    public function getSchoolAccountAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSchoolAccount');
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return false|TblSchoolAccount
     */
    public function getSchoolAccountByCompany(TblCompany $tblCompany)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSchoolAccount',
            array(TblSchoolAccount::ATTR_SERVICE_TBL_COMPANY => $tblCompany->getId()));
    }

    /**
     * @param TblCompany $tblCompany
     * @param            $BankName
     * @param            $Owner
     * @param            $CashSign
     * @param            $IBAN
     * @param            $BIC
     *
     * @return null|object|TblSchoolAccount
     */
    public function createSchoolAccount(
        TblCompany $tblCompany,
        $BankName,
        $Owner,
        $CashSign,
        $IBAN,
        $BIC
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblSchoolAccount')->findOneBy(array(
            TblSchoolAccount::ATTR_SERVICE_TBL_COMPANY => $tblCompany->getId()
        ));

        if ($Entity === null) {
            $Entity = new TblSchoolAccount();
            $Entity->setBankName($BankName);
            $Entity->setOwner($Owner);
            $Entity->setCashSign($CashSign);
            $Entity->setIBAN($IBAN);
            $Entity->setBIC($BIC);
            $Entity->setServiceTblCompany($tblCompany);

            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSchoolAccount $tblSchoolAccount
     * @param                  $BankName
     * @param                  $Owner
     * @param                  $CashSign
     * @param                  $IBAN
     * @param                  $BIC
     *
     * @return bool
     */
    public function updateSchoolAccount(
        TblSchoolAccount $tblSchoolAccount,
        $BankName,
        $Owner,
        $CashSign,
        $IBAN,
        $BIC
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var TblBankAccount $Entity */
        $Entity = $Manager->getEntityById('TblSchoolAccount', $tblSchoolAccount->getId());
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
     * @param TblSchoolAccount $tblSchoolAccount
     *
     * @return bool
     */
    public function destroySchoolAccount(
        TblSchoolAccount $tblSchoolAccount
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblSchoolAccount')->findOneBy(array('Id' => $tblSchoolAccount->getId()));
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
