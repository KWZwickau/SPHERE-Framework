<?php

namespace SPHERE\Application\App\Authentication\Process\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblToken")
 * @Cache(usage="READ_ONLY")
 */
class TblToken extends Element
{
    public const SERVICE_TBL_ACCOUNT = 'serviceTblAccount';
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAccount;
    /**
     * @Column(type="string")
     */
    protected ?string $authenticationToken;
    /**
     * @Column(type="integer")
     */
    protected ?int $authenticationTimeout;
    /**
     * @Column(type="string")
     */
    protected ?string $accessToken;
    /**
     * @Column(type="integer")
     */
    protected ?int $accessTimeout;

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

    public function getAuthenticationToken(): ?string
    {
        return $this->authenticationToken;
    }

    public function setAuthenticationToken(?string $authenticationToken): void
    {
        $this->authenticationToken = $authenticationToken;
    }

    public function getAuthenticationTimeout(): ?int
    {
        return $this->authenticationTimeout;
    }

    public function setAuthenticationTimeout(?int $authenticationTimeout): void
    {
        $this->authenticationTimeout = $authenticationTimeout;
    }


    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getAccessTimeout(): ?int
    {
        return $this->accessTimeout;
    }

    public function setAccessTimeout(?int $accessTimeout): void
    {
        $this->accessTimeout = $accessTimeout;
    }
}
