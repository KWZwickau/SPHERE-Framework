<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGraduationScoreRule")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreRule extends Element
{
    /**
     * @Column(type="string")
     */
    protected string $Name = '';
    /**
     * @Column(type="string")
     */
    protected string $Description = '';
    /**
     * @Column(type="string")
     */
    protected string $DescriptionForExtern = '';
    /**
     * @Column(type="boolean")
     */
    protected bool $IsActive;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName(string $Name): void
    {
        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription(string $Description): void
    {
        $this->Description = $Description;
    }

    /**
     * @return string
     */
    public function getDescriptionForExtern(): string
    {
        return $this->DescriptionForExtern;
    }

    /**
     * @param string $DescriptionForExtern
     */
    public function setDescriptionForExtern(string $DescriptionForExtern): void
    {
        $this->DescriptionForExtern = $DescriptionForExtern;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->IsActive;
    }

    /**
     * @param bool $IsActive
     */
    public function setIsActive(bool $IsActive): void
    {
        $this->IsActive = $IsActive;
    }
}