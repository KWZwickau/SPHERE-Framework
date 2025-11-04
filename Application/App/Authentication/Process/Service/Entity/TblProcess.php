<?php

namespace SPHERE\Application\App\Authentication\Process\Service\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Exception;
use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblProcess")
 */
class TblProcess extends Element
{
    public const ATTR_TBL_TOKEN = 'tblToken';
    public const ATTR_TBL_FACTOR = 'tblFactor';
    public const ATTR_IS_SOLVED = 'isSolved';
    /**
     * @Column(type="bigint")
     */
    protected $tblToken;
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
    public function getTblToken(): ?TblToken
    {
        if (null === $this->tblToken) {
            return null;
        }
        return Authentication::useService()->getTokenById($this->tblToken);
    }

    public function setTblToken(?TblToken $tblToken): void
    {
        $this->tblToken = $tblToken?->getId();
    }

    public function getDeviceId(): ?string
    {
        return $this->deviceId;
    }

    public function setDeviceId(?string $deviceId): void
    {
        $this->deviceId = $deviceId;
    }

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

    public function getIsSolved(): ?bool
    {
        return $this->isSolved;
    }

    public function setIsSolved(?bool $isSolved): void
    {
        $this->isSolved = $isSolved;
    }
}
