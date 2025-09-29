<?php

namespace SPHERE\Application\App\Authentication\Process\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
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
 * @Table(name="tblProcess")
 * @Cache(usage="READ_ONLY")
 */
class TblProcess extends Element
{
    public const SERVICE_TBL_ACCOUNT = 'serviceTblAccount';
    public const ATTR_TBL_FACTOR = 'tblFactor';
    public const ATTR_IS_SOLVED = 'isSolved';
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAccount;
    /**
     * @Column(type="bigint")
     */
    protected $tblFactor;
    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $isSolved;

    /**
     * @throws Exception
     */
    public function getTblFactor(): ?TblFactor
    {
        if (null === $this->tblFactor) {
            return null;
        }
        return Authentication::useService()->getFactorById($this->tblFactor);
    }

    public function setTblFactor(?TblFactor $tblFactor): void
    {
        $this->tblFactor = $tblFactor?->getId();
    }

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

    public function getIsSolved(): ?bool
    {
        return $this->isSolved;
    }

    public function setIsSolved(?bool $isSolved): void
    {
        $this->isSolved = $isSolved;
    }
}
