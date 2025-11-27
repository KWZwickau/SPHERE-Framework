<?php

namespace SPHERE\Application\App\Authentication\Process\Service;

use Exception;
use RuntimeException;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblDevice;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblFactor;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblProcess;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblStep;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblToken;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use Throwable;

/**
 *
 */
class Data extends AbstractData
{
    public function setupDatabaseContent(): void
    {
        // TODO: Implement setupDatabaseContent() method.
        $tblFactor1 = $this->createFactor('Credentials', 'Username & Password');
        if (null === $tblFactor1) {
            throw new RuntimeException('Failed to create factor');
        }
        $tblIdentification = Account::useService()->getIdentificationByName('Credential');
        $this->modifyProcess($tblIdentification, $tblFactor1, 1);

        $tblFactor2 = $this->createFactor('YubiKey', 'YubiKey');
        if (null === $tblFactor2) {
            throw new RuntimeException('Failed to create factor');
        }
        $tblIdentification = Account::useService()->getIdentificationByName('Token');
        $this->modifyProcess($tblIdentification, $tblFactor1, 1);
        $this->modifyProcess($tblIdentification, $tblFactor2, 2);

    }

    public function createFactor(string $name, ?string $description = null): ?TblFactor
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblFactor|null $entity */
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

    public function modifyProcess(
        TblIdentification $tblIdentification,
        TblFactor $tblFactor,
        ?int $sortOrder = null
    ): ?TblProcess {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblProcess|null $entity */
        $entity = $manager->getEntity('TblProcess')->findOneBy([
            TblProcess::SERVICE_TBL_IDENTIFICATION => $tblIdentification->getId(),
            TblProcess::ATTR_TBL_FACTOR => $tblFactor->getId()
        ]);
        if (null === $entity) {
            $entity = new TblProcess();
            $entity->setServiceTblIdentification($tblIdentification);
            $entity->setTblFactor($tblFactor);
            $entity->setSortOrder($sortOrder);
            $manager->saveEntity($entity);
            Protocol::useService()->createInsertEntry($connection->getDatabase(), $entity);
        } elseif (
            (null !== $sortOrder && $entity->getSortOrder() !== $sortOrder)
        ) {
            /** @var TblProcess $protocol */
            $protocol = clone $entity;
            $entity->setSortOrder($sortOrder);
            $manager->updateEntity($entity);
            Protocol::useService()->createUpdateEntry($connection->getDatabase(), $protocol, $entity);
        }
        return $entity;
    }

    public function modifyDevice(
        string $deviceIdentifier,
        ?string $processToken = null,
        ?int $processTimeout = null,
    ): ?TblDevice {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblDevice|null $entity */
        $entity = $manager->getEntity('TblDevice')->findOneBy([
            TblDevice::ATTR_DEVICE_IDENTIFIER => $deviceIdentifier
        ]);
        if (null === $entity) {
            $entity = new TblDevice();
            $entity->setDeviceIdentifier($deviceIdentifier);
            $entity->setProcessToken($processToken);
            $entity->setProcessTimeout($processTimeout);
            $manager->saveEntity($entity);
            Protocol::useService()->createInsertEntry($connection->getDatabase(), $entity);
        } elseif (
            (null !== $processToken && $entity->getProcessToken() !== $processToken)
            || (null !== $processTimeout && $entity->getProcessTimeout() !== $processTimeout)
        ) {
            /** @var TblDevice $protocol */
            $protocol = clone $entity;
            $entity->setProcessToken($processToken);
            $entity->setProcessTimeout($processTimeout);
            $manager->updateEntity($entity);
            Protocol::useService()->createUpdateEntry($connection->getDatabase(), $protocol, $entity);
        }
        return $entity;
    }

    public function modifyStep(
        TblAccount $tblAccount,
        TblDevice $tblDevice,
        TblProcess $tblProcess,
        ?bool $isSolved = null
    ): ?TblStep {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblStep|null $entity */
        $entity = $manager->getEntity('TblStep')->findOneBy([
            TblStep::SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
            TblStep::ATTR_TBL_DEVICE => $tblDevice->getId(),
            TblStep::ATTR_TBL_PROCESS => $tblProcess->getId()
        ]);
        if (null === $entity) {
            $entity = new TblStep();
            $entity->setServiceTblAccount($tblAccount);
            $entity->setTblDevice($tblDevice);
            $entity->setTblProcess($tblProcess);
            $entity->setIsSolved($isSolved);
            $manager->saveEntity($entity);
            Protocol::useService()->createInsertEntry($connection->getDatabase(), $entity);
        } elseif (
            (null !== $isSolved && $entity->getIsSolved() !== $isSolved)
        ) {
            /** @var TblStep $protocol */
            $protocol = clone $entity;
            $entity->setIsSolved($isSolved);
            $manager->updateEntity($entity);
            Protocol::useService()->createUpdateEntry($connection->getDatabase(), $protocol, $entity);
        }
        return $entity;
    }

    public function modifyToken(
        TblAccount $tblAccount,
        TblDevice $tblDevice,
        ?string $authenticationToken = null,
        ?int $authenticationTimeout = null,
        ?string $accessToken = null,
        ?int $accessTimeout = null,
    ): ?TblToken {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblToken|null $entity */
        $entity = $manager->getEntity('TblToken')->findOneBy([
            TblToken::SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
            TblToken::ATTR_TBL_DEVICE => $tblDevice->getId(),
        ]);
        if (null === $entity) {
            $entity = new TblToken();
            $entity->setServiceTblAccount($tblAccount);
            $entity->setTblDevice($tblDevice);
            $entity->setAuthenticationToken($authenticationToken);
            $entity->setAuthenticationTimeout($authenticationTimeout);
            $entity->setAccessToken($accessToken);
            $entity->setAccessTimeout($accessTimeout);
            $manager->saveEntity($entity);
            Protocol::useService()->createInsertEntry($connection->getDatabase(), $entity);
        } elseif (
            (null !== $authenticationToken && $entity->getAuthenticationToken() !== $authenticationToken)
            || (null !== $authenticationTimeout && $entity->getAuthenticationTimeout() !== $authenticationTimeout)
            || (null !== $accessToken && $entity->getAccessToken() !== $accessToken)
            || (null !== $accessTimeout && $entity->getAccessTimeout() !== $accessTimeout)
        ) {
            /** @var TblToken $protocol */
            $protocol = clone $entity;
            $entity->setAuthenticationToken($authenticationToken);
            $entity->setAuthenticationTimeout($authenticationTimeout);
            $entity->setAccessToken($accessToken);
            $entity->setAccessTimeout($accessTimeout);
            $manager->updateEntity($entity);
            Protocol::useService()->createUpdateEntry($connection->getDatabase(), $protocol, $entity);
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
        $manager = $connection->getEntityManager();
        /** @var TblFactor|null $entity */
        $entity = $manager->getEntityById('TblFactor', $id);
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
        $manager = $connection->getEntityManager();
        /** @var TblFactor|null $entity */
        $entity = $manager->getEntity('TblFactor')->findOneBy([TblFactor::ATTR_NAME => $name]);
        if (!$entity) {
            return null;
        }
        return $entity;
    }

    /**
     * @throws Exception
     */
    public function getDeviceById(int $id): ?TblDevice
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblDevice|null $entity */
        $entity = $manager->getEntityById('TblDevice', $id);
        if (!$entity) {
            return null;
        }
        return $entity;
    }

    /**
     * @throws Exception
     */
    public function getDeviceByIdentifier(string $deviceIdentifier): ?TblDevice
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblDevice|null $entity */
        $entity = $manager->getEntity('TblDevice')->findOneBy([TblDevice::ATTR_DEVICE_IDENTIFIER => $deviceIdentifier]);
        if (!$entity) {
            return null;
        }
        return $entity;
    }

    /**
     * @throws Exception
     */
    public function getTokenByAccess(string $accessToken): ?TblToken
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblToken|null $entity */
        $entity = $manager->getEntity('TblToken')->findOneBy([TblToken::ATTR_ACCESS_TOKEN => $accessToken]);
        if (!$entity) {
            return null;
        }
        // Token timed out
        if($entity->getAccessTimeout() < time()){
            return null;
        }
        return $entity;
    }

    /**
     * @throws Exception
     */
    public function getTokenByAuthentication(string $authenticationToken): ?TblToken
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblToken|null $entity */
        $entity = $manager->getEntity('TblToken')->findOneBy([TblToken::ATTR_AUTHENTICATION_TOKEN => $authenticationToken]);
        if (!$entity) {
            return null;
        }
        // Token timed out
        if($entity->getAuthenticationTimeout() < time()){
            return null;
        }
        return $entity;
    }

    /**
     * @throws Exception
     */
    public function getProcessById(int $id): ?TblProcess
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblProcess|null $entity */
        $entity = $manager->getEntityById('TblProcess', $id);
        if (!$entity) {
            return null;
        }
        return $entity;
    }

    /**
     * @param TblAccount $tblAccount
     * @param TblDevice  $tblDevice
     *
     * @return TblStep[]|null
     */
    public function getAllStepsByAccountAndDevice(TblAccount $tblAccount, TblDevice $tblDevice): ?array
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblStep[]|null $entity */
        $entities = $manager->getEntity('TblStep')->findBy([
            TblStep::SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
            TblStep::ATTR_TBL_DEVICE => $tblDevice->getId(),
        ]);
        if (empty($entities)) {
            return null;
        }

        // Sort TblStep by TblProcess->sortOrder
        usort($entities, static function (TblStep $a, TblStep $b) {
            try {
                return $a->getTblProcess()->getSortOrder() <=> $b->getTblProcess()->getSortOrder();
            } catch (Throwable) {
                return 0;
            }
        });

        return $entities;
    }

    /**
     * @param TblIdentification $tblIdentification
     *
     * @return TblProcess[]|null
     */
    public function getAllProcessesByIdentification(TblIdentification $tblIdentification): ?array
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblProcess[]|null $entity */
        $entities = $manager->getEntity('TblProcess')->findBy([
            TblProcess::SERVICE_TBL_IDENTIFICATION => $tblIdentification->getId(),
        ]);
        if (empty($entities)) {
            return null;
        }
        return $entities;
    }

    /**
     * @param TblStep $tblStep
     *
     * @return null|bool
     */
    public function destroyStep(TblStep $tblStep): ?bool
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblStep|null $entity */
        try {
            $entity = $manager->getEntityById('TblStep', $tblStep->getId());
        } catch (Throwable) {
            return null;
        }
        if (null === $entity) {
            return false;
        }
        Protocol::useService()->createDeleteEntry($connection->getDatabase(), $entity);
        $manager->killEntity($entity);
        return true;
    }

}
