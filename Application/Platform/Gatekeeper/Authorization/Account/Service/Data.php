<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service;

use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthentication;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSession;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSetting;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblUser;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity\TblToken;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Cache\Handler\MemoryHandler;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Database\Fitting\ColumnHydrator;

/**
 * Class Data
 *
 * @package SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

        // Identification
        $this->createIdentification('Credential', 'Benutzername / Passwort');
        $this->createIdentification('Token', 'Benutzername / Passwort & Hardware-Schlüssel');
        $this->createIdentification('System', 'Benutzername / Passwort & Hardware-Schlüssel');

        $tblConsumer = Consumer::useService()->getConsumerById(1);
        $tblIdentification = $this->getIdentificationByName('System');
        $tblRole = Access::useService()->getRoleByName('Administrator');

        // System (Gerd)
        $tblToken = Token::useService()->getTokenByIdentifier('ccccccdilkui');
        $tblAccount = $this->createAccount('System', 'System', $tblToken, $tblConsumer);
        $this->addAccountAuthentication($tblAccount, $tblIdentification);
        $this->addAccountAuthorization($tblAccount, $tblRole);
        if (!$this->getSettingByAccount($tblAccount, 'Surface')) {
            $this->setSettingByAccount($tblAccount, 'Surface', 1);
        }

        // System (Jens)
        $tblToken = Token::useService()->getTokenByIdentifier('ccccccectjge');
        $tblAccount = $this->createAccount('Kmiezik', 'System', $tblToken, $tblConsumer);
        $this->addAccountAuthentication($tblAccount, $tblIdentification);
        $this->addAccountAuthorization($tblAccount, $tblRole);
        if (!$this->getSettingByAccount($tblAccount, 'Surface')) {
            $this->setSettingByAccount($tblAccount, 'Surface', 1);
        }

        // System (Sidney)
        $tblToken = Token::useService()->getTokenByIdentifier('ccccccectjgt');
        $tblAccount = $this->createAccount('Rackel', 'System', $tblToken, $tblConsumer);
        $this->addAccountAuthentication($tblAccount, $tblIdentification);
        $this->addAccountAuthorization($tblAccount, $tblRole);
        if (!$this->getSettingByAccount($tblAccount, 'Surface')) {
            $this->setSettingByAccount($tblAccount, 'Surface', 1);
        }

        // System (Johannes)
        $tblToken = Token::useService()->getTokenByIdentifier('ccccccectjgr');
        $tblAccount = $this->createAccount('Kauschke', 'System', $tblToken, $tblConsumer);
        $this->addAccountAuthentication($tblAccount, $tblIdentification);
        $this->addAccountAuthorization($tblAccount, $tblRole);
        if (!$this->getSettingByAccount($tblAccount, 'Surface')) {
            $this->setSettingByAccount($tblAccount, 'Surface', 1);
        }
    }

    /**
     * @param string $Name
     * @param string $Description
     *
     * @return TblIdentification
     */
    public function createIdentification($Name, $Description = '')
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblIdentification')->findOneBy(array(TblIdentification::ATTR_NAME => $Name));
        if (null === $Entity) {
            $Entity = new TblIdentification($Name);
            $Entity->setDescription($Description);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string $Name
     *
     * @return bool|TblIdentification
     */
    public function getIdentificationByName($Name)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblIdentification')
            ->findOneBy(array(TblIdentification::ATTR_NAME => $Name));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param string $Username
     * @param string $Password
     * @param null|TblToken $tblToken
     * @param null|TblConsumer $tblConsumer
     *
     * @return TblAccount
     */
    public function createAccount($Username, $Password, TblToken $tblToken = null, TblConsumer $tblConsumer = null)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblAccount')->findOneBy(array(TblAccount::ATTR_USERNAME => $Username));
        if (null === $Entity) {
            $Entity = new TblAccount($Username);
            $Entity->setPassword(hash('sha256', $Password));
            $Entity->setServiceTblToken($tblToken);
            $Entity->setServiceTblConsumer($tblConsumer);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblAccount $tblAccount
     * @param TblIdentification $tblIdentification
     *
     * @return TblAuthentication
     */
    public function addAccountAuthentication(TblAccount $tblAccount, TblIdentification $tblIdentification)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblAuthentication')
            ->findOneBy(array(
                TblAuthentication::ATTR_TBL_ACCOUNT => $tblAccount->getId(),
                TblAuthentication::ATTR_TBL_IDENTIFICATION => $tblIdentification->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblAuthentication();
            $Entity->setTblAccount($tblAccount);
            $Entity->setTblIdentification($tblIdentification);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblAccount $tblAccount
     * @param TblRole $tblRole
     *
     * @return TblAuthorization
     */
    public function addAccountAuthorization(TblAccount $tblAccount, TblRole $tblRole)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblAuthorization')
            ->findOneBy(array(
                TblAuthorization::ATTR_TBL_ACCOUNT => $tblAccount->getId(),
                TblAuthorization::SERVICE_TBL_ROLE => $tblRole->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblAuthorization();
            $Entity->setTblAccount($tblAccount);
            $Entity->setServiceTblRole($tblRole);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblAccount $tblAccount
     * @param string $Identifier
     *
     * @return bool|TblSetting
     */
    public function getSettingByAccount(TblAccount $tblAccount, $Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSetting', array(
            TblSetting::ATTR_TBL_ACCOUNT => $tblAccount->getId(),
            TblSetting::ATTR_IDENTIFIER => $Identifier
        ));
    }

    /**
     * @param TblAccount $tblAccount
     * @param string $Identifier
     * @param string $Value
     *
     * @return bool|TblSetting
     */
    public function setSettingByAccount(TblAccount $tblAccount, $Identifier, $Value)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblSetting')->findOneBy(array(
            TblSetting::ATTR_TBL_ACCOUNT => $tblAccount->getId(),
            TblSetting::ATTR_IDENTIFIER => $Identifier
        ));
        if (null === $Entity) {
            $Entity = new TblSetting();
            $Entity->setTblAccount($tblAccount);
            $Entity->setIdentifier($Identifier);
            $Entity->setValue($Value);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        } else {
            /**
             * @var TblSetting $Protocol
             * @var TblSetting $Entity
             */
            $Protocol = clone $Entity;
            $Entity->setValue($Value);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @return TblIdentification[]|bool
     */
    public function getIdentificationAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblIdentification');
    }

    /**
     * @param TblAccount $tblAccount
     * @param TblRole $tblRole
     *
     * @return bool
     */
    public function removeAccountAuthorization(TblAccount $tblAccount, TblRole $tblRole)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblAuthorization $Entity */
        $Entity = $Manager->getEntity('TblAuthorization')
            ->findOneBy(array(
                TblAuthorization::ATTR_TBL_ACCOUNT => $tblAccount->getId(),
                TblAuthorization::SERVICE_TBL_ROLE => $tblRole->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblAccount $tblAccount
     * @param TblIdentification $tblIdentification
     *
     * @return bool
     */
    public function removeAccountAuthentication(TblAccount $tblAccount, TblIdentification $tblIdentification)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblAuthentication $Entity */
        $Entity = $Manager->getEntity('TblAuthentication')
            ->findOneBy(array(
                TblAuthentication::ATTR_TBL_ACCOUNT => $tblAccount->getId(),
                TblAuthentication::ATTR_TBL_IDENTIFICATION => $tblIdentification->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param string $Username
     *
     * @return bool|TblAccount
     */
    public function getAccountByUsername($Username)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblAccount')
            ->findOneBy(array(TblAccount::ATTR_USERNAME => $Username));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblAccount
     */
    public function getAccountById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAccount', $Id);
    }

    /**
     * @return TblAccount[]|bool
     */
    public function getAccountAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAccount');
    }

    /**
     * @return TblSession[]|bool
     */
    public function getSessionAll()
    {
        // MUST NOT USE Cache
        return $this->getConnection()->getEntityManager()->getEntity('TblSession')->findAll();
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblIdentification
     */
    public function getIdentificationById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblIdentification',
            $Id);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblSession
     */
    public function getSessionById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSession', $Id);
    }

    /**
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity\TblToken $tblToken
     *
     * @return bool|TblAccount[]
     */
    public function getAccountAllByToken(TblToken $tblToken)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAccount', array(
            TblAccount::SERVICE_TBL_TOKEN => $tblToken->getId()
        ));
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblAuthorization[]
     */
    public function getAuthorizationAllByAccount(TblAccount $tblAccount)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblAuthorization',
            array(
                TblAuthorization::ATTR_TBL_ACCOUNT => $tblAccount->getId()
            ));
    }

    /**
     * @param TblConsumer $tblConsumer
     *
     * @return bool|TblAccount[]
     */
    public function getAccountAllByConsumer(TblConsumer $tblConsumer)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblAccount')->findBy(array(
            TblAccount::SERVICE_TBL_CONSUMER => $tblConsumer->getId()
        ));
        return (empty($EntityList) ? false : $EntityList);
    }

    /**
     * @param string $Username
     * @param string $Password
     * @param TblIdentification $tblIdentification
     *
     * @return bool|TblAccount
     */
    public function getAccountByCredential($Username, $Password, TblIdentification $tblIdentification = null)
    {

        $tblAccount = $this->getConnection()->getEntityManager()->getEntity('TblAccount')
            ->findOneBy(array(
                TblAccount::ATTR_USERNAME => $Username,
                TblAccount::ATTR_PASSWORD => hash('sha256', $Password)
            ));
        // Account not available
        if (null === $tblAccount) {
            return false;
        }

        $tblAuthentication = $this->getConnection()->getEntityManager()->getEntity('TblAuthentication')
            ->findOneBy(array(
                TblAuthentication::ATTR_TBL_ACCOUNT => $tblAccount->getId(),
                TblAuthentication::ATTR_TBL_IDENTIFICATION => $tblIdentification->getId()
            ));
        // Identification not valid
        if (null === $tblAuthentication) {
            return false;
        }

        return $tblAccount;
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblSession[]
     */
    public function getSessionAllByAccount(TblAccount $tblAccount)
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblSession')->findBy(array(
            TblSession::ATTR_TBL_ACCOUNT => $tblAccount->getId()
        ));
        return (empty($EntityList) ? false : $EntityList);
    }

    /**
     * @param TblAccount $tblAccount
     * @param null|string $Session
     * @param integer $Timeout
     *
     * @return TblSession
     */
    public function createSession(TblAccount $tblAccount, $Session = null, $Timeout = 1800)
    {

        if (null === $Session) {
            $Session = session_id();
        }
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSession $Entity */
        $Entity = $Manager->getEntity('TblSession')->findOneBy(array(TblSession::ATTR_SESSION => $Session));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
        }
        $Entity = new TblSession($Session);
        $Entity->setTblAccount($tblAccount);
        $Entity->setTimeout(time() + $Timeout);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param null|string $Session
     *
     * @return bool
     */
    public function destroySession($Session = null)
    {

        if (null === $Session) {
            $Session = session_id();
        }

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSession $Entity */
        $Entity = $Manager->getEntity('TblSession')->findOneBy(array(TblSession::ATTR_SESSION => $Session));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
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
        /** @var TblAccount $Entity */
        $Entity = $Manager->getEntityById('TblAccount', $tblAccount->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblAccount $tblAccount
     * @param string $Password
     *
     * @return bool
     */
    public function changePassword($Password, TblAccount $tblAccount = null)
    {

        if (null === $tblAccount) {
            $tblAccount = $this->getAccountBySession();
        }
        $Manager = $this->getConnection()->getEntityManager();
        /**
         * @var TblAccount $Protocol
         * @var TblAccount $Entity
         */
        $Entity = $Manager->getEntityById('TblAccount', $tblAccount->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setPassword(hash('sha256', $Password));
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param null|string $Session
     *
     * @return bool|TblAccount
     */
    public function getAccountBySession($Session = null)
    {

        if (null === $Session) {
            $Session = session_id();
        }

        // 1. Level Cache
        $Memory = $this->getCache(new MemoryHandler());
        if (null === ($Entity = $Memory->getValue($Session, __METHOD__))) {
            // Kill stalled Sessions
            $this->removeSessionByTimeout();

            /** @var false|TblSession $Entity */
            $Entity = $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblSession',
                array(
                    TblSession::ATTR_SESSION => $Session
                ));

            if ($Entity) {
                $Account = $Entity->getTblAccount();
                // Reset Timeout on Current Session (within time)
                $Type = $this->getAuthenticationByAccount($Account)->getTblIdentification()->getName();
                switch (strtoupper($Type)) {
                    case 'SYSTEM':
                        $Timeout = (60 * 60);
                        break;
                    case 'TOKEN':
                        $Timeout = (60 * 30);
                        break;
                    case 'CREDENTIAL':
                        $Timeout = (60 * 15);
                        break;
                    default:
                        $Timeout = (60 * 10);
                }
                $this->changeTimeout($Entity, $Timeout);
                $Entity = $Account;
            }
            $Memory->setValue($Session, $Entity, 0, __METHOD__);
        }
        return ($Entity ? $Entity : false);
    }

    private function removeSessionByTimeout()
    {
        $Manager = $this->getConnection()->getEntityManager();

        $Builder = $Manager->getQueryBuilder();
        $Query = $Builder->select('S.Id')
            ->from(__NAMESPACE__ . '\Entity\TblSession', 'S')
            ->where($Builder->expr()->lte('S.Timeout', '?1'))
            ->setParameter(1, time())
            ->getQuery();

        $IdList = $Query->getResult(ColumnHydrator::HYDRATION_MODE);

        if (!empty($IdList)) {
            $Builder = $Manager->getQueryBuilder();
            $Query = $Builder->delete()
                ->from(__NAMESPACE__ . '\Entity\TblSession', 'S')
                ->where($Builder->expr()->in('S.Id', '?1'))
                ->setParameter(1, $IdList)
                ->getQuery();

            $Query->getResult();
        }
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblAuthentication
     */
    public function getAuthenticationByAccount(TblAccount $tblAccount)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntity('TblAuthentication')->findOneBy(array(
            TblAuthentication::ATTR_TBL_ACCOUNT => $tblAccount->getId()
        ));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblSession $tblSession
     * @param int $Timeout
     * @return bool
     */
    private function changeTimeout(TblSession $tblSession, $Timeout)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /**
         * @var TblSession $Entity
         */
        $Entity = $Manager->getEntityById('TblSession', $tblSession->getId());

        if (null !== $Entity) {
            $Entity->setTimeout(time() + $Timeout);
            $Manager->saveEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblToken $tblToken
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function changeToken(TblToken $tblToken, TblAccount $tblAccount = null)
    {

        if (null === $tblAccount) {
            $tblAccount = $this->getAccountBySession();
        }
        $Manager = $this->getConnection()->getEntityManager();
        /**
         * @var TblAccount $Protocol
         * @var TblAccount $Entity
         */
        $Entity = $Manager->getEntityById('TblAccount', $tblAccount->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblToken($tblToken);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblConsumer $tblConsumer
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function changeConsumer(TblConsumer $tblConsumer, TblAccount $tblAccount = null)
    {

        if (null === $tblAccount) {
            $tblAccount = $this->getAccountBySession();
        }
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblAccount $Entity */
        $Entity = $Manager->getEntityById('TblAccount', $tblAccount->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setServiceTblConsumer($tblConsumer);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @return bool|TblPerson[]
     */
    public function getPersonAllHavingNoAccount()
    {

        $Exclude = $this->getConnection()->getEntityManager()->getQueryBuilder()
            ->select('U.serviceTblPerson')
            ->from('\SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblUser', 'U')
            ->distinct()
            ->getQuery()
            ->getResult("COLUMN_HYDRATOR");

        $tblPersonAll = Person::useService()->getPersonAll();
        if ($tblPersonAll) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblPersonAll, function (TblPerson &$tblPerson) use ($Exclude) {

                if (in_array($tblPerson->getId(), $Exclude)) {
                    $tblPerson = false;
                }
            });
            $EntityList = array_filter($tblPersonAll);
        } else {
            $EntityList = null;
        }
        return (null === $EntityList ? false : $EntityList);
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblPerson[]
     */
    public function getPersonAllByAccount(TblAccount $tblAccount)
    {

        $tblUserAll = $this->getUserAllByAccount($tblAccount);
        if ($tblUserAll) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblUserAll, function (TblUser &$tblUser) {

                $tblUser = $tblUser->getServiceTblPerson();
            });
            $EntityList = $tblUserAll;
        } else {
            $EntityList = null;
        }
        return (null === $EntityList ? false : $EntityList);
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblUser[]
     */
    public function getUserAllByAccount(TblAccount $tblAccount)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblUser', array(
            TblUser::ATTR_TBL_ACCOUNT => $tblAccount->getId()
        ));
    }

    /**
     * @param TblAccount $tblAccount
     * @param TblPerson $tblPerson
     *
     * @return TblUser
     */
    public function addAccountPerson(TblAccount $tblAccount, TblPerson $tblPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblUser')
            ->findOneBy(array(
                TblUser::ATTR_TBL_ACCOUNT => $tblAccount->getId(),
                TblUser::SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblUser();
            $Entity->setTblAccount($tblAccount);
            $Entity->setServiceTblPerson($tblPerson);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblAccount $tblAccount
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function removeAccountPerson(TblAccount $tblAccount, TblPerson $tblPerson)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblUser $Entity */
        $Entity = $Manager->getEntity('TblUser')
            ->findOneBy(array(
                TblUser::ATTR_TBL_ACCOUNT => $tblAccount->getId(),
                TblUser::SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }
}
