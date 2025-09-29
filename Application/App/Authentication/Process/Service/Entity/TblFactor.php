<?php

namespace SPHERE\Application\App\Authentication\Process\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblFactor")
 * @Cache(usage="READ_ONLY")
 */
class TblFactor extends Element
{
    public const ATTR_NAME = 'name';
    /**
     * @Column(type="string")
     */
    protected string $name;
    /**
     * @Column(type="text", nullable=true)
     */
    protected ?string $description;
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
