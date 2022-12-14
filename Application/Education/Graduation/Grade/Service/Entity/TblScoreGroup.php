<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Grade\Grade;
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
     * @param string $name
     * @param string $multiplier
     * @param bool $isEveryGradeASingleGroup
     * @param bool $isActive
     * @param int|null $id
     */
    public function __construct(string $name, string $multiplier, bool $isEveryGradeASingleGroup, bool $isActive = true, ?int $id = null)
    {
        $this->Name = $name;
        $this->Multiplier = $multiplier;
        $this->IsEveryGradeASingleGroup = $isEveryGradeASingleGroup;
        $this->IsActive = $isActive;
        if ($id) {
            $this->Id = $id;
        }
    }

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
     * @return string
     */
    public function getDisplayMultiplier(): string
    {
        return str_replace('.', ',', $this->Multiplier);
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

    /**
     * @return bool
     */
    public function getIsUsed(): bool
    {
        return Grade::useService()->getIsScoreGroupUsed($this);
    }
}