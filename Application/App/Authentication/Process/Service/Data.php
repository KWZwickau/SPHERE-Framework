<?php

namespace SPHERE\Application\App\Authentication\Process\Service;

use SPHERE\Application\App\Authentication\Process\Service\Entity\TblDevice;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;
use SPHERE\System\Extension\Repository\Debugger;

/**
 *
 */
class Data extends AbstractData
{
    public function setupDatabaseContent(): void
    {
        // TODO: Implement setupDatabaseContent() method.
    }

    /**
     * @param TblAccount $tblAccount
     * @return TblDevice[]|null
     */
    public function getDeviceAllByAccount(TblAccount $tblAccount): ?array
    {

        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblDevice[]|null $entity */
        $entity = $manager->getEntity('TblDevice')->findBy([
            TblDevice::SERVICE_TBL_ACCOUNT => $tblAccount->getId()
        ]);

        if (!$entity) {
            return null;
        }
        return $entity;
    }

    public function createDevice(TblAccount $tblAccount, string $deviceIdentifier, string $deviceName): ?TblDevice
    {
        $entity = $this->getDeviceByIdentifier($tblAccount, $deviceIdentifier);
        if (null === $entity) {
            $connection = $this->getConnection();
            if (null === $connection) {
                return null;
            }
            $manager = $connection->getEntityManager();
            $entity = new TblDevice();
            $entity->setServiceTblAccount($tblAccount);
            $entity->setDeviceIdentifier($deviceIdentifier);
            $entity->setDeviceName($deviceName);
            $manager->saveEntity($entity);
            Protocol::useService()->createInsertEntry($connection->getDatabase(), $entity);
        }
        return $entity;
    }

    public function updateDevice(TblDevice $tblDevice, string $deviceName, string $isActive = ''): ?TblDevice
    {
        $entity = $this->getDeviceById($tblDevice->getId());
        if (null !== $entity) {
            $connection = $this->getConnection();
            if (null === $connection) {
                return null;
            }
            // delete device by edit
            if($isActive == 3){
                $this->destroyDevice($entity);
                return null;
            }

            $entityTemp = clone $entity;
            $manager = $connection->getEntityManager();
            $entity->setDeviceName($deviceName);
            if($isActive == 2){ // blocked
                $entity->setIsActive(false);
            } elseif($isActive == 1) { // activate
                $entity->setIsActive(true);
            }
            $manager->saveEntity($entity);
            Protocol::useService()->createUpdateEntry($connection->getDatabase(), $entityTemp, $entity);
        }
        return $entity;
    }

    public function updateDeviceStatus(TblDevice $tblDevice, $isActive = '2'): ?TblDevice
    {
        $entity = $this->getDeviceById($tblDevice->getId());
        if (null !== $entity) {
            $connection = $this->getConnection();
            if (null === $connection) {
                return null;
            }
            $entityTemp = clone $entity;
            $manager = $connection->getEntityManager();
            if($isActive == 2){ // blocked
                $entity->setIsActive(false);
            } else {
                $entity->setIsActive(true);
            }
            $manager->saveEntity($entity);
            Protocol::useService()->createUpdateEntry($connection->getDatabase(), $entityTemp, $entity);
        }
        return $entity;
    }

    /**
     * @param TblDevice $tblDevice
     *
     * @return bool
     */
    public function destroyDevice(TblDevice $tblDevice): bool
    {
        $entity = $this->getDeviceById($tblDevice->getId());
        if (null === $entity) {
            return false;
        }
        $connection = $this->getConnection();
        if (null === $connection) {
            return false;
        }
        $entityTemp = clone $entity;
        $manager = $connection->getEntityManager();
        $manager->killEntity($entity);
        Protocol::useService()->createDeleteEntry($connection->getDatabase(), $entityTemp);
        return true;
    }

    public function getDeviceByIdentifier(TblAccount $tblAccount, string $deviceIdentifier): ?TblDevice
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblDevice|null $entity */
        $entity = $manager->getEntity('TblDevice')->findOneBy([
            TblDevice::SERVICE_TBL_ACCOUNT => $tblAccount->getId(),
            TblDevice::ATTR_DEVICE_IDENTIFIER => $deviceIdentifier
        ]);
        if (!$entity) {
            return null;
        }
        return $entity;
    }

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

    public function getDeviceByAccessToken(string $accessToken): ?TblDevice
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblDevice|null $entity */
        $entity = $manager->getEntity('TblDevice')->findOneBy([
            TblDevice::ATTR_ACCESS_TOKEN => $accessToken
        ]);
        if (!$entity) {
            return null;
        }
        // Token timed out
        if ($entity->getAccessTimeout() < time()) {
            return null;
        }
        return $entity;
    }

    public function getDeviceByAuthenticationToken(string $authenticationToken): ?TblDevice
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblDevice|null $entity */
        $entity = $manager->getEntity('TblDevice')->findOneBy([
            TblDevice::ATTR_AUTHENTICATION_TOKEN => $authenticationToken
        ]);
        if (!$entity) {
            return null;
        }
        // Token timed out
        if ($entity->getAuthenticationTimeout() < time()) {
            return null;
        }
        return $entity;
    }

    public function modifyDeviceName(TblDevice $tblDevice, string $deviceName): ?bool
    {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblDevice|null $entity */
        $entity = $manager->getEntity('TblDevice')->find($tblDevice->getId());
        if (null === $entity) {
            return false;
        }
        // Persist
        /** @var TblDevice $protocol */
        $protocol = clone $entity;
        $entity->setDeviceName($deviceName);
        $manager->updateEntity($entity);
        Protocol::useService()->createUpdateEntry($connection->getDatabase(), $protocol, $entity);
        // Writeback
        $tblDevice->setDeviceName($deviceName);
        return true;
    }

    public function modifyAccessToken(
        TblDevice $tblDevice,
        string $accessToken,
        int $tokenTimeout = 300
    ): ?bool {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblDevice|null $entity */
        $entity = $manager->getEntity('TblDevice')->find($tblDevice->getId());
        if (null === $entity) {
            return false;
        }
        // Persist
        /** @var TblDevice $protocol */
        $protocol = clone $entity;
        $entity->setAccessToken($accessToken);
        $entity->setAccessTimeout(time() + $tokenTimeout);
        $manager->updateEntity($entity);
        Protocol::useService()->createUpdateEntry($connection->getDatabase(), $protocol, $entity);
        // Writeback
        $tblDevice->setAccessToken($accessToken);
        $tblDevice->setAccessTimeout($entity->getAccessTimeout());
        return true;
    }

    public function modifyAuthenticationToken(
        TblDevice $tblDevice,
        string $authenticationToken,
        int $tokenTimeout = 3600
    ): ?bool {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblDevice|null $entity */
        $entity = $manager->getEntity('TblDevice')->find($tblDevice->getId());
        if (null === $entity) {
            return false;
        }
        // Persist
        /** @var TblDevice $protocol */
        $protocol = clone $entity;
        $entity->setAuthenticationToken($authenticationToken);
        $entity->setAuthenticationTimeout(time() + $tokenTimeout);
        $manager->updateEntity($entity);
        Protocol::useService()->createUpdateEntry($connection->getDatabase(), $protocol, $entity);
        // Writeback
        $tblDevice->setAuthenticationToken($authenticationToken);
        $tblDevice->setAuthenticationTimeout($entity->getAuthenticationTimeout());
        return true;
    }

    public function modifyIsActive(
        TblDevice $tblDevice,
        ?bool $isActive
    ): ?bool {
        $connection = $this->getConnection();
        if (null === $connection) {
            return null;
        }
        $manager = $connection->getEntityManager();
        /** @var TblDevice|null $entity */
        $entity = $manager->getEntity('TblDevice')->find($tblDevice->getId());
        if (null === $entity) {
            return false;
        }
        // Persist
        /** @var TblDevice $protocol */
        $protocol = clone $entity;
        $entity->setIsActive($isActive);
        $manager->updateEntity($entity);
        Protocol::useService()->createUpdateEntry($connection->getDatabase(), $protocol, $entity);
        // Writeback
        $tblDevice->setIsActive($isActive);
        return true;
    }
}
