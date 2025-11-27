<?php

namespace SPHERE\Application\App\Authentication\Process;

use RuntimeException;
use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\App\Authentication\Factor\Credentials;
use SPHERE\Application\App\Authentication\Process\Service\Data;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblDevice;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblFactor;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblProcess;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblStep;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblToken;
use SPHERE\Application\App\Authentication\Process\Service\Setup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
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

    public function getFactorById(int $id): ?TblFactor
    {
        return (new Data($this->getBinding()))->getFactorById($id);
    }

    public function getFactorByName(string $name): ?TblFactor
    {
        return (new Data($this->getBinding()))->getFactorByName($name);
    }

    public function getDeviceById(int $id): ?TblDevice
    {
        return (new Data($this->getBinding()))->getDeviceById($id);
    }

    public function getDeviceByIdentifier(string $deviceIdentifier): ?TblDevice
    {
        return (new Data($this->getBinding()))->getDeviceByIdentifier($deviceIdentifier);
    }

    public function getTokenByAuthentication(string $authenticationToken): ?TblToken
    {
        return (new Data($this->getBinding()))->getTokenByAuthentication($authenticationToken);
    }

    public function getTokenByAccess(string $accessToken): ?TblToken
    {
        return (new Data($this->getBinding()))->getTokenByAccess($accessToken);
    }

    public function createAccessToken(TblToken $tblToken): ?TblToken
    {
        return (new Data($this->getBinding()))->modifyToken(
            $tblToken->getServiceTblAccount(), $tblToken->getTblDevice(),
            $tblToken->getAuthenticationToken(), $tblToken->getAuthenticationTimeout(),
            Authentication::produceAccessToken(),
            time() + Authentication::ACCESS_TOKEN_TIMEOUT
        );
    }

    public function createAuthenticationToken(TblAccount $tblAccount, TblDevice $tblDevice): ?TblToken
    {
        return (new Data($this->getBinding()))->modifyToken(
            $tblAccount, $tblDevice,
            Authentication::produceAuthenticationToken(), time() + Authentication::AUTHENTICATION_TOKEN_TIMEOUT,
            null, null
        );
    }

    public function getProcessById(int $id): ?TblProcess
    {
        return (new Data($this->getBinding()))->getProcessById($id);
    }

    public function getDeviceWithIdentifierAndToken(string $deviceIdentifier, string $processToken): ?TblDevice
    {
        return (new Data($this->getBinding()))->modifyDevice(
            $deviceIdentifier, $processToken, time() + Authentication::PROCESS_TOKEN_TIMEOUT
        );
    }

    public function resetAllSteps(TblAccount $tblAccount, TblDevice $tblDevice): void
    {
        /** @var TblStep[] $tblSteps */
        $tblSteps = $this->getAllStepsByAccountAndDevice($tblAccount, $tblDevice);
        if (null !== $tblSteps) {
            foreach ($tblSteps as $tblStep) {

                // Except "Credentials"
                if ($tblStep->getTblProcess()->getTblFactor()?->getName() === Credentials::FACTOR_NAME) {
                    continue;
                }

                (new Data($this->getBinding()))->modifyStep(
                    $tblStep->getServiceTblAccount(),
                    $tblStep->getTblDevice(),
                    $tblStep->getTblProcess(),
                    false
                );
            }
        }
    }

    public function getAllStepsByAccountAndDevice(TblAccount $tblAccount, TblDevice $tblDevice): ?array
    {
        return (new Data($this->getBinding()))->getAllStepsByAccountAndDevice($tblAccount, $tblDevice);
    }

    public function createAllSteps(TblAccount $tblAccount, TblDevice $tblDevice): void
    {
        // Fetch current existing steps
        $tblSteps = $this->getAllStepsByAccountAndDevice($tblAccount, $tblDevice);
        $existingTblSteps = [];
        /** @var TblStep $tblStep */
        foreach ($tblSteps as $tblStep) {
            $existingTblSteps[$tblStep->getTblProcess()->getId()] = $tblStep;
        }

        // Collect processes for current identification and create necessary steps (or reset them)
        $tblAuthentications = Account::useService()->getAuthenticationListByAccount($tblAccount);
        if (!$tblAuthentications) {
            throw new RuntimeException('No authentication method available');
        }
        $tblIdentifications = [];
        foreach ($tblAuthentications as $tblAuthentication) {
            $tblIdentifications[] = $tblAuthentication->getTblIdentification();
        }
        if (empty($tblIdentifications)) {
            throw new RuntimeException('No authentication method available');
        }
        foreach ($tblIdentifications as $tblIdentification) {
            $tblProcesses = (new Data($this->getBinding()))->getAllProcessesByIdentification($tblIdentification);
            foreach ($tblProcesses as $tblProcess) {
                // If not existing or to be reset
                if (!isset($existingTblSteps[$tblProcess->getId()])) {
                    // Create if not exist, update if exist
                    (new Data($this->getBinding()))->modifyStep($tblAccount, $tblDevice, $tblProcess, false);
                }
                // Remove from "existing" list because process is needed in identification
                if (isset($existingTblSteps[$tblProcess->getId()])) {
                    unset($existingTblSteps[$tblProcess->getId()]);
                }
            }
        }
        // Kill leftover processes from existing list because no longer needed in any identification
        foreach ($existingTblSteps as $tblStep) {
            (new Data($this->getBinding()))->destroyStep($tblStep);
        }
    }
}
