<?php
namespace SPHERE\Application\Setting\User\Account\Service;

use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson as TblToPersonAddress;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToPerson as TblToPersonMail;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Setting\User\Account\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }

    /**
     * @param integer $Id
     *
     * @return bool|TblUserAccount
     */
    public function getUserAccountById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblUserAccount', $Id);
    }

    /**
     * @param bool $IsSend
     * @param bool $IsExport
     *
     * @return false|TblUserAccount[]
     */
    public function getUserAccountByIsSendAndIsExport($IsSend = false, $IsExport = false)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUserAccount',
            array(
                TblUserAccount::ATTR_IS_SEND   => $IsSend,
                TblUserAccount::ATTR_IS_EXPORT => $IsExport
            ));
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblUserAccount
     */
    public function getUserAccountByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUserAccount',
            array(
                TblUserAccount::ATTR_SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
    }

    /**
     * @return bool|TblUserAccount[]
     */
    public function getUserAccountAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblUserAccount');
    }

    /**
     * @param TblAccount              $tblAccount
     * @param TblPerson               $tblPerson
     * @param TblToPersonAddress|null $tblToPersonAddress
     * @param TblToPersonMail|null    $tblToPersonMail
     * @param string                  $UserPassword
     *
     * @return TblUserAccount
     */
    public function createUserAccount(
        TblAccount $tblAccount,
        TblPerson $tblPerson,
        TblToPersonAddress $tblToPersonAddress = null,
        TblToPersonMail $tblToPersonMail = null,
        $UserPassword
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblUserAccount $Entity */
        $Entity = $Manager->getEntity('TblUserAccount')
            ->findOneBy(array(
                TblUserAccount::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
            ));

        if (null === $Entity) {
            $Entity = new TblUserAccount();
            $Entity->setServiceTblAccount($tblAccount);
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setServiceTblToPersonAddress($tblToPersonAddress);
            $Entity->setServiceTblToPersonMail($tblToPersonMail);
            $Entity->setUserPassword($UserPassword);
            $Entity->setIsSend(false);
            $Entity->setIsExport(false);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
            return $Entity;
        }
        return $Entity;
    }

    /**
     * @param TblUserAccount $tblUserAccount
     * @param TblToPersonAddress $tblToPersonAddress
     *
     * @return bool
     */
    public function updateUserAccountByToPersonAddress(
        TblUserAccount $tblUserAccount,
        TblToPersonAddress $tblToPersonAddress
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblUserAccount $Entity */
        $Entity = $Manager->getEntityById('TblUserAccount', $tblUserAccount->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblToPersonAddress($tblToPersonAddress !== null ? $tblToPersonAddress : null);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblUserAccount  $tblUserAccount
     * @param TblToPersonMail $tblToPersonMail
     *
     * @return bool
     */
    public function updateUserAccountByToPersonMail(TblUserAccount $tblUserAccount, TblToPersonMail $tblToPersonMail)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblUserAccount $Entity */
        $Entity = $Manager->getEntityById('TblUserAccount', $tblUserAccount->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblToPersonMail($tblToPersonMail !== null ? $tblToPersonMail : null);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblUserAccount $tblUserAccount
     * @param bool           $IsExport
     *
     * @return bool
     */
    public function updateUserAccountByIsExport(TblUserAccount $tblUserAccount, $IsExport)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblUserAccount $Entity */
        $Entity = $Manager->getEntityById('TblUserAccount', $tblUserAccount->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsExport($IsExport);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblUserAccount $tblUserAccount
     * @param bool           $IsSend
     *
     * @return bool
     */
    public function updateUserAccountByIsSend(TblUserAccount $tblUserAccount, $IsSend)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblUserAccount $Entity */
        $Entity = $Manager->getEntityById('TblUserAccount', $tblUserAccount->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setIsSend($IsSend);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblUserAccount $tblUserAccount
     *
     * @return bool
     */
    public function removeUserAccount(TblUserAccount $tblUserAccount)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblUserAccount $Entity */
        $Entity = $Manager->getEntityById('TblUserAccount', $tblUserAccount->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}
