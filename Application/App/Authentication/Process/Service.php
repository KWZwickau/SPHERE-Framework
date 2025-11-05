<?php

namespace SPHERE\Application\App\Authentication\Process;

use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Authentication\Process\Service\Data;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblDevice;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblFactor;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblProcess;
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

    public function getProcessById(int $id): ?TblProcess
    {
        return (new Data($this->getBinding()))->getProcessById($id);
    }

    public function getDeviceWithIdentifierAndToken(string $deviceIdentifier, string $processToken): ?TblDevice
    {
        return (new Data($this->getBinding()))->createDevice($deviceIdentifier, $processToken, time() + 3600);
    }

    public function resetAllSteps(TblAccount $tblAccount, TblDevice $tblDevice): void
    {
        $tblSteps = (new Data($this->getBinding()))->getAllStepsByAccountAndDevice($tblAccount, $tblDevice);
        if (null !== $tblSteps) {
            foreach ($tblSteps as $tblStep) {
                (new Data($this->getBinding()))->createStep(
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
}
