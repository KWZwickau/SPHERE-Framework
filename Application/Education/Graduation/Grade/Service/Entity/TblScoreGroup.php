<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGraduationScoreGroup")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreGroup extends Element
{
    const ATTR_NAME = 'Name';
    const ATTR_IS_ACTIVE = 'IsActive';

    /**
     * @Column(type="string")
     */
    protected string $Name = '';
    /**
     * @Column(type="string")
     */
    protected string $Multiplier = '';
    /**
     * @Column(type="boolean")
     */
    protected bool $IsEveryGradeASingleGroup;
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
    public function getMultiplier(): string
    {
        return $this->Multiplier;
    }

    /**
     * @param string $Multiplier
     */
    public function setMultiplier(string $Multiplier): void
    {
        $this->Multiplier = $Multiplier;
    }

    /**
     * @return bool
     */
    public function getIsEveryGradeASingleGroup(): bool
    {
        return $this->IsEveryGradeASingleGroup;
    }

    /**
     * @param bool $IsEveryGradeASingleGroup
     */
    public function setIsEveryGradeASingleGroup(bool $IsEveryGradeASingleGroup): void
    {
        $this->IsEveryGradeASingleGroup = $IsEveryGradeASingleGroup;
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