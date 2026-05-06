<?php

namespace SPHERE\Application\App\Authentication\Process\Service\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblDevice")
 */
class TblDevice extends Element
{
    public const SERVICE_TBL_ACCOUNT = 'serviceTblAccount';
    public const ATTR_DEVICE_IDENTIFIER = 'deviceIdentifier';
    public const ATTR_AUTHENTICATION_TOKEN = 'authenticationToken';
    public const ATTR_ACCESS_TOKEN = 'accessToken';

    /**
     * @Column(type="string", nullable=false)
     */
    protected string $deviceIdentifier;
    /**
     * @Column(type="bigint", nullable=true)
     */
    protected ?int $serviceTblAccount;
    /**
     * @Column(type="string", nullable=true)
     */
    protected ?string $deviceName;
    /**
     * @Column(type="text", nullable=true)
     */
    protected ?string $authenticationToken;
    /**
     * @Column(type="integer", nullable=true)
     */
    protected ?int $authenticationTimeout;
    /**
     * @Column(type="text", nullable=true)
     */
    protected ?string $accessToken;
    /**
     * @Column(type="integer", nullable=true)
     */
    protected ?int $accessTimeout;
    /**
     * @Column(type="boolean", nullable=true)
     */
    protected ?bool $isActive;

    public function getServiceTblAccount(): ?TblAccount
    {

        if (null === $this->serviceTblAccount) {
            return null;
        }

        return Account::useService()->getAccountById($this->serviceTblAccount);
    }

    /**
     * @param null|TblAccount $tblAccount
     */
    public function setServiceTblAccount(TblAccount $tblAccount = null): void
    {

        $this->serviceTblAccount = (null === $tblAccount ? null : $tblAccount->getId());
    }

    public function getDeviceIdentifier(): string
    {
        return $this->deviceIdentifier;
    }

    public function setDeviceIdentifier(string $deviceIdentifier): void
    {
        $this->deviceIdentifier = $deviceIdentifier;
    }

    public function getDeviceName(): string
    {
        return $this->deviceName;
    }

    public function setDeviceName(string $deviceName): void
    {
        $this->deviceName = $deviceName;
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

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): void
    {
        $this->isActive = $isActive;
    }
}
