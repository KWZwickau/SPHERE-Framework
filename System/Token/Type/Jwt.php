<?php

namespace SPHERE\System\Token\Type;


use SPHERE\System\Token\ITypeInterface;

/**
 *
 */
class Jwt implements ITypeInterface
{
    private ?string $JwtSecret = null;

    /**
     * @param array $Configuration
     */
    public function setConfiguration($Configuration): void
    {
        $this->JwtSecret = $Configuration['JwtSecret'];
    }

    public function getConfiguration(): string
    {
        return 'Jwt';
    }

    public function getJwtSecret(): ?string
    {
        return $this->JwtSecret;
    }
}
