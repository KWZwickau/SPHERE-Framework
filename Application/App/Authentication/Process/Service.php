<?php

namespace SPHERE\Application\App\Authentication\Process;

use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Authentication\Process\Service\Data;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblDevice;
use SPHERE\Application\App\Authentication\Process\Service\Setup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\System\Database\Binding\AbstractService;

/**
 *
 */
class Service extends AbstractService
{
    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     * @throws AppException
     */
    public function setupService($doSimulation, $withData, $UTF8): string
    {
        $Protocol = '';
        if (!$withData) {
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    public function getDeviceById(int $id): ?TblDevice
    {
        return (new Data($this->getBinding()))->getDeviceById($id);
    }

    public function getDeviceByIdentifier(TblAccount $tblAccount, string $deviceIdentifier): ?TblDevice
    {
        return (new Data($this->getBinding()))->getDeviceByIdentifier($tblAccount, $deviceIdentifier);
    }

    public function createDevice(TblAccount $tblAccount, string $deviceIdentifier): ?TblDevice
    {
        return (new Data($this->getBinding()))->createDevice($tblAccount, $deviceIdentifier);
    }

    public function getDeviceByAuthentication(string $authenticationToken): ?TblDevice
    {
        return (new Data($this->getBinding()))->getDeviceByAuthenticationToken($authenticationToken);
    }

    public function getDeviceByAccess(string $accessToken): ?TblDevice
    {
        return (new Data($this->getBinding()))->getDeviceByAccessToken($accessToken);
    }

    public function modifyAccessToken(
        TblDevice $tblDevice,
        string $accessToken,
        int $tokenTimeout = 300
    ): ?bool {
        return (new Data($this->getBinding()))->modifyAccessToken(
            $tblDevice, $accessToken, $tokenTimeout
        );
    }

    public function modifyAuthenticationToken(
        TblDevice $tblDevice,
        string $authenticationToken,
        int $tokenTimeout = 3600
    ): ?bool {
        return (new Data($this->getBinding()))->modifyAuthenticationToken(
            $tblDevice, $authenticationToken, $tokenTimeout
        );
    }
    public function modifyOtpToken(
        TblDevice $tblDevice,
        string $otpToken,
        int $tokenTimeout = 120
    ): ?bool {
        return (new Data($this->getBinding()))->modifyOtpToken(
            $tblDevice, $otpToken, $tokenTimeout
        );
    }
}
