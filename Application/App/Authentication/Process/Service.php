<?php

namespace SPHERE\Application\App\Authentication\Process;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Exception;
use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Authentication\Process\Service\Data;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblFactor;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblProcess;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblStep;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblToken;
use SPHERE\Application\App\Authentication\Process\Service\Setup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
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

    /**
     * @throws Exception
     */
    public function getFactorById(int $id): ?TblFactor
    {
        return (new Data($this->getBinding()))->getFactorById($id);
    }

    /**
     * @throws Exception
     */
    public function getTokenById(int $id): ?TblToken
    {
        return (new Data($this->getBinding()))->getTokenById($id);
    }

    /**
     * @throws Exception
     */
    public function getFactorByName(string $name): ?TblFactor
    {
        return (new Data($this->getBinding()))->getFactorByName($name);
    }

    /**
     * @return TblStep[]|null
     * @throws Exception
     */
    public function getStepsByIdentification(TblIdentification $tblIdentification): ?array
    {
        return (new Data($this->getBinding()))->getAllStepsByIdentification($tblIdentification);
    }
    /**
     * @return TblProcess[]|null
     * @throws Exception
     */
    public function getProcessesByToken(TblToken $tblToken, ?bool $isSolved = null): ?array
    {
        return (new Data($this->getBinding()))->getAllProcessesByToken($tblToken, $isSolved);
    }

    /**
     * @throws Exception
     */
    public function createToken(TblAccount $tblAccount, string $deviceToken, string $processToken): ?TblToken
    {
        return (new Data($this->getBinding()))->createToken($tblAccount, $deviceToken, $processToken);
    }

    /**
     * @throws Exception
     */
    public function getToken(TblAccount $tblAccount, string $deviceToken): ?TblToken
    {
        return (new Data($this->getBinding()))->getToken($tblAccount, $deviceToken);
    }

    /**
     * @throws Exception
     */
    public function createProcess(TblToken $tblToken, TblFactor $tblFactor): ?TblProcess
    {
        return (new Data($this->getBinding()))->createProcess($tblToken, $tblFactor, false);
    }

    /**
     * @throws Exception
     */
    public function destroyProcess(TblProcess $tblProcess): ?bool
    {
        return (new Data($this->getBinding()))->destroyProcess($tblProcess);
    }

    /**
     * @throws Exception
     */
    public function getProcessToken(TblToken $tblToken): ?string
    {
        return (new Data($this->getBinding()))->getProcessToken(
            $tblToken->getServiceTblAccount(), $tblToken->getDeviceToken()
        );
    }

    public function updateProcessToken(TblToken $tblToken, string $processToken): ?TblToken
    {
        return (new Data($this->getBinding()))->updateProcessToken($tblToken, $processToken);
    }

    public function updateProcessSolved(TblProcess $tblProcess, bool $isSolved): ?TblProcess
    {
        return (new Data($this->getBinding()))->updateProcessSolved($tblProcess, $isSolved);
    }

    /**
     * @throws Exception
     */
    public function getAuthenticationToken(TblToken $tblToken): ?string
    {
        return (new Data($this->getBinding()))->getAuthenticationToken(
            $tblToken->getServiceTblAccount(), $tblToken->getDeviceToken()
        );
    }
    public function updateAuthenticationToken(TblToken $tblToken, string $authenticationToken): ?TblToken
    {
        return (new Data($this->getBinding()))->updateAuthenticationToken($tblToken, $authenticationToken);
    }

    public function updateAuthenticationTimeout(TblToken $tblToken, int $authenticationTimeout): ?TblToken
    {
        return (new Data($this->getBinding()))->updateAuthenticationTimeout($tblToken, $authenticationTimeout);
    }
    /**
     * @throws Exception
     */
    public function getAccessToken(TblToken $tblToken): ?string
    {
        return (new Data($this->getBinding()))->getAccessToken(
            $tblToken->getServiceTblAccount(), $tblToken->getDeviceToken()
        );
    }
    public function updateAccessToken(TblToken $tblToken, string $accessToken): ?TblToken
    {
        return (new Data($this->getBinding()))->updateAccessToken($tblToken, $accessToken);
    }

    public function updateAccessTimeout(TblToken $tblToken, int $accessTimeout): ?TblToken
    {
        return (new Data($this->getBinding()))->updateAccessTimeout($tblToken, $accessTimeout);
    }

}
