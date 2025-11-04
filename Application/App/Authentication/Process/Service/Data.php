<?php

namespace SPHERE\Application\App\Authentication\Process\Service;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Exception;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblFactor;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblProcess;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblStep;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblToken;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 *
 */
class Data extends AbstractData
{
    public function setupDatabaseContent(): void
    {
        // TODO: Implement setupDatabaseContent() method.
        $tblFactor = $this->createFactor('Credentials', 'Username & Password');
        $tblFactor = $this->createFactor('YubiKey', 'YubiKey');
    }

    public function createFactor(string $name, ?string $description = null): ?TblFactor
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        $entity = $manager->getEntity('TblFactor')->findOneBy([TblFactor::ATTR_NAME => $name]);
        if (null === $entity) {
            $entity = new TblFactor();
            $entity->setName($name);
            $entity->setDescription($description);

            $manager->saveEntity($entity);
            Protocol::useService()->createInsertEntry($connection->getDatabase(), $entity);
        }
        return $entity;
    }

    public function createProcess(TblToken $tblToken, TblFactor $tblFactor, bool $isSolved): ?TblProcess
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        $entity = $manager->getEntity('TblProcess')->findOneBy([
            TblProcess::ATTR_TBL_TOKEN => $tblToken->getId(),
            TblProcess::ATTR_TBL_FACTOR => $tblFactor->getId()
        ]);
        if (null === $entity) {
            $entity = new TblProcess();
            $entity->setTblToken($tblToken);
            $entity->setTblFactor($tblFactor);
            $entity->setIsSolved($isSolved);

            $manager->saveEntity($entity);
            Protocol::useService()->createInsertEntry($connection->getDatabase(), $entity);
        }
        return $entity;
    }

    /**
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws ORMException
     */
    public function destroyProcess(TblProcess $tblProcess): ?bool
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblProcess|null $entity */
        $entity = $manager->getEntityById('TblProcess', $tblProcess->getId());
        if (null !== $entity) {
            Protocol::useService()->createDeleteEntry($connection->getDatabase(), $entity);
            $manager->killEntity($entity);
            return true;
        }
        return false;
    }

    public function createStep(TblIdentification $tblIdentification, TblFactor $tblFactor): ?TblStep
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        $entity = $manager->getEntity('TblStep')->findOneBy([
            TblStep::SERVICE_TBL_IDENTIFICATION => $tblIdentification->getId(),
            TblStep::ATTR_TBL_FACTOR => $tblFactor->getId()
        ]);
        if (null === $entity) {
            $entity = new TblStep();
            $entity->setServiceTblIdentification($tblIdentification);
            $entity->setTblFactor($tblFactor);

            $manager->saveEntity($entity);
            Protocol::useService()->createInsertEntry($connection->getDatabase(), $entity);
        }
        return $entity;
    }

    /**
     * @throws Exception
     */
    public function getFactorById(int $id): ?TblFactor
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        /** @var TblFactor $entity */
        $entity = $this->getCachedEntityById(__METHOD__, $connection->getEntityManager(), 'TblFactor', $id);
        if (!$entity) {
            return null;
        }
        return $entity;
    }

    /**
     * @throws Exception
     */
    public function getFactorByName(string $name): ?TblFactor
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        /** @var TblFactor $entity */
        $entity = $this->getCachedEntityBy(__METHOD__, $connection->getEntityManager(), 'TblFactor', [
            TblFactor::ATTR_NAME => $name
        ]);
        if (!$entity) {
            return null;
        }
        return $entity;
    }

    /**
     * @throws Exception
     */
    public function getTokenById(int $id): ?TblToken
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        /** @var TblToken $entity */
        $entity = $this->getForceEntityById(__METHOD__, $connection->getEntityManager(), 'TblToken', $id);
        if (!$entity) {
            return null;
        }
        return $entity;
    }

    /**
     * @return TblStep[]|null
     * @throws Exception
     */
    public function getAllStepsByIdentification(TblIdentification $tblIdentification): ?array
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        /** @var TblStep[] $entities */
        $entities = $this->getCachedEntityListBy(__METHOD__, $connection->getEntityManager(), 'TblStep', [
            TblStep::SERVICE_TBL_IDENTIFICATION => $tblIdentification->getId()
        ], [
            TblStep::ATTR_SORT_ORDER => self::ORDER_ASC
        ]);
        if (!$entities) {
            return null;
        }
        return $entities;
    }

    /**
     * @return TblProcess[]|null
     * @throws Exception
     */
    public function getAllProcessesByToken(TblToken $tblToken, ?bool $isSolved = null): ?array
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $criteria = [
            TblProcess::ATTR_TBL_TOKEN => $tblToken->getId()
        ];
        if (null !== $isSolved) {
            $criteria[TblProcess::ATTR_IS_SOLVED] = $isSolved;
        }
        /** @var TblProcess[] $entities */
        $entities = $this->getForceEntityListBy(__METHOD__, $connection->getEntityManager(), 'TblProcess', $criteria);
        if (!$entities) {
            return null;
        }
        return $entities;
    }

    public function updateDeviceToken(TblToken $entity, string $deviceToken): ?TblToken
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $protocol = clone $entity;
        $entity->setDeviceToken($deviceToken);
        $connection->getEntityManager()->saveEntity($entity);
        Protocol::useService()->createUpdateEntry($connection->getDatabase(), $protocol, $entity);
        return $entity;
    }

    public function updateAuthenticationToken(TblToken $entity, string $authenticationToken): ?TblToken
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $protocol = clone $entity;
        $entity->setAuthenticationToken($authenticationToken);
        $connection->getEntityManager()->saveEntity($entity);
        Protocol::useService()->createUpdateEntry($connection->getDatabase(), $protocol, $entity);
        return $entity;
    }

    public function updateAuthenticationTimeout(TblToken $entity, int $authenticationTimeout): ?TblToken
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $protocol = clone $entity;
        $entity->setAuthenticationTimeout($authenticationTimeout);
        $connection->getEntityManager()->saveEntity($entity);
        Protocol::useService()->createUpdateEntry($connection->getDatabase(), $protocol, $entity);
        return $entity;
    }

    public function updateAccessToken(TblToken $entity, string $accessToken): ?TblToken
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $protocol = clone $entity;
        $entity->setAccessToken($accessToken);
        $connection->getEntityManager()->saveEntity($entity);
        Protocol::useService()->createUpdateEntry($connection->getDatabase(), $protocol, $entity);
        return $entity;
    }

    public function updateAccessTimeout(TblToken $entity, int $accessTimeout): ?TblToken
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $protocol = clone $entity;
        $entity->setAccessTimeout($accessTimeout);
        $connection->getEntityManager()->saveEntity($entity);
        Protocol::useService()->createUpdateEntry($connection->getDatabase(), $protocol, $entity);
        return $entity;
    }

    /**
     * @throws Exception
     */
    public function createToken(TblAccount $tblAccount, string $deviceToken, string $processToken): ?TblToken
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        $entity = $manager->getEntity('TblToken')->findOneBy([
            TblToken::SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
            TblToken::ATTR_DEVICE_TOKEN => $deviceToken,
            TblToken::ATTR_PROCESS_TOKEN => $processToken
        ]);
        if (null === $entity) {
            $entity = new TblToken();
            $entity->setServiceTblAccount($tblAccount);
            $entity->setDeviceToken($deviceToken);
            $entity->setProcessToken($processToken);
            $manager->saveEntity($entity);
            Protocol::useService()->createInsertEntry($connection->getDatabase(), $entity);
        }
        return $entity;
    }

    /**
     * @throws Exception
     */
    public function getToken(TblAccount $tblAccount, string $deviceToken): ?TblToken
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        /** @var TblToken $entity */
        $entity = $this->getForceEntityBy(__METHOD__, $connection->getEntityManager(), 'TblToken', [
            TblToken::SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
            TblToken::ATTR_DEVICE_TOKEN => $deviceToken
        ]);
        if (!$entity) {
            return null;
        }
        return $entity;
    }


    /**
     * @throws Exception
     */
    public function getProcessToken(TblAccount $tblAccount, string $deviceToken): ?string
    {
        return $this->getToken($tblAccount, $deviceToken)?->getProcessToken();
    }

    public function updateProcessToken(TblToken $entity, string $processToken): ?TblToken
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $protocol = clone $entity;
        $entity->setProcessToken($processToken);
        $connection->getEntityManager()->saveEntity($entity);
        Protocol::useService()->createUpdateEntry($connection->getDatabase(), $protocol, $entity);
        return $entity;
    }

    public function updateProcessSolved(TblProcess $entity, bool $isSolved): ?TblProcess
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $protocol = clone $entity;
        $entity->setIsSolved($isSolved);
        $connection->getEntityManager()->saveEntity($entity);
        Protocol::useService()->createUpdateEntry($connection->getDatabase(), $protocol, $entity);
        return $entity;
    }

    /**
     * @throws Exception
     */
    public function getAuthenticationToken(TblAccount $tblAccount, string $deviceToken): ?string
    {
        $tblToken = $this->getToken($tblAccount, $deviceToken);
        if (null === $tblToken) {
            return null;
        }
        if ($tblToken->getAuthenticationTimeout() <= time()) {
            return null;
        }
        return $tblToken->getAuthenticationToken();
    }

    /**
     * @throws Exception
     */
    public function getAccessToken(TblAccount $tblAccount, string $deviceToken): ?string
    {
        $tblToken = $this->getToken($tblAccount, $deviceToken);
        if (null === $tblToken) {
            return null;
        }
        if ($tblToken->getAccessTimeout() <= time()) {
            return null;
        }
        return $tblToken->getAccessToken();
    }
}
