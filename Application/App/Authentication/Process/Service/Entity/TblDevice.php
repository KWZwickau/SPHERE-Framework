<?php

namespace SPHERE\Application\App\Authentication\Process\Service\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblDevice")
 */
class TblDevice extends Element
{
    public const ATTR_DEVICE_IDENTIFIER = 'deviceIdentifier';
    public const ATTR_PROCESS_TOKEN = 'processToken';
    public const ATTR_PROCESS_TIMEOUT = 'processTimeout';
    /**
     * @Column(type="string")
     */
    protected string $deviceIdentifier;
    /**
     * @Column(type="text", nullable=true)
     */
    protected ?string $processToken;
    /**
     * @Column(type="integer", nullable=true)
     */
    protected ?int $processTimeout;

    public function getDeviceIdentifier(): string
    {
        return $this->deviceIdentifier;
    }

    public function setDeviceIdentifier(string $deviceIdentifier): void
    {
        $this->deviceIdentifier = $deviceIdentifier;
    }

    public function getProcessToken(): ?string
    {
        return $this->processToken;
    }

    public function setProcessToken(?string $processToken): void
    {
        $this->processToken = $processToken;
    }

    public function getProcessTimeout(): ?int
    {
        return $this->processTimeout;
    }

    public function setProcessTimeout(?int $processTimeout): void
    {
        $this->processTimeout = $processTimeout;
    }
}
