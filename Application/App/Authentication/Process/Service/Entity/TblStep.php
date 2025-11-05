<?php

namespace SPHERE\Application\App\Authentication\Process\Service\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Exception;
use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStep")
 */
class TblStep extends Element
{
    public const SERVICE_TBL_ACCOUNT = 'serviceTblAccount';
    public const ATTR_TBL_DEVICE = 'tblDevice';
    public const ATTR_TBL_PROCESS = 'tblProcess';
    public const ATTR_IS_SOLVED = 'isSolved';
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAccount;
    /**
     * @Column(type="bigint")
     */
    protected $tblDevice;
    /**
     * @Column(type="bigint")
     */
    protected $tblProcess;
    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $isSolved;

    public function getServiceTblAccount(): ?TblAccount
    {
        if (null === $this->serviceTblAccount) {
            return null;
        }
        return Account::useService()->getAccountById($this->serviceTblAccount);
    }

    public function setServiceTblAccount(?TblAccount $tblAccount): void
    {
        $this->serviceTblAccount = $tblAccount?->getId();
    }

    /**
     * @throws Exception
     */
    public function getTblDevice(): TblDevice
    {
        return Authentication::useService()->getDeviceById($this->tblDevice);
    }

    public function setTblDevice(TblDevice $tblDevice): void
    {
        $this->tblDevice = $tblDevice->getId();
    }

    /**
     * @throws Exception
     */
    public function getTblProcess(): TblProcess
    {
        return Authentication::useService()->getProcessById($this->tblProcess);
    }

    public function setTblProcess(TblProcess $tblProcess): void
    {
        $this->tblProcess = $tblProcess->getId();
    }

    public function getIsSolved(): ?bool
    {
        return $this->isSolved;
    }

    public function setIsSolved(?bool $isSolved): void
    {
        $this->isSolved = $isSolved;
    }
}
