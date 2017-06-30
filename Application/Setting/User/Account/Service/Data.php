<?php
namespace SPHERE\Application\Setting\User\Account\Service;

use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson as TblToPersonAddress;
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
     * @param bool $IsExport
     *
     * @return false|TblUserAccount[]
     */
    public function getUserAccountByIsExport($IsExport = false)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUserAccount',
            array(
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
     * @param TblAccount $tblAccount
     *
     * @return false|TblUserAccount
     */
    public function getUserAccountByAccount(TblAccount $tblAccount)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUserAccount',
            array(
                TblUserAccount::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId()
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
     * @param string $type
     *
     * @return bool|TblUserAccount[]
     */
    public function getUserAccountAllByType($type)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUserAccount',
            array(
                TblUserAccount::ATTR_TYPE => $type
            ));
    }

    /**
     * @param string $type
     *
     * @return bool|TblUserAccount[]
     */
    public function countUserAccountAllByType($type)
    {

        return $this->getCachedCountBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUserAccount',
            array(
                TblUserAccount::ATTR_TYPE => $type
            ));
    }

    /**
     * @param TblAccount              $tblAccount
     * @param TblPerson               $tblPerson
     * @param TblToPersonAddress|null $tblToPersonAddress
     * @param \DateTime               $TimeStamp
     * @param string                  $userPassword
     * @param string                  $type STUDENT|CUSTODY
     *
     * @return TblUserAccount
     */
    public function createUserAccount(
        TblAccount $tblAccount,
        TblPerson $tblPerson,
        TblToPersonAddress $tblToPersonAddress = null,
        \DateTime $TimeStamp,
        $userPassword,
        $type = 'STUDENT'
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
            $Entity->setType($type);
            $Entity->setUserPassword($userPassword);
            $Entity->setAccountPassword(hash('sha256', $userPassword));
            $Entity->setIsExport(false);
            $Entity->setGroupByTime($TimeStamp);
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
