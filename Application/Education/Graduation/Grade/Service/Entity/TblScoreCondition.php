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
 * @Table(name="tblGraduationScoreCondition")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreCondition extends Element
{
    const ATTR_NAME = 'Name';
    const ATTR_IS_ACTIVE = 'IsActive';
    const ATTR_PRIORITY = 'Priority';
    const PERIOD_FULL_YEAR = -1;
    const PERIOD_FIRST_PERIOD = 1;
    const PERIOD_SECOND_PERIOD = 2;

    /**
     * @Column(type="string")
     */
    protected string $Name = '';
    /**
     * @Column(type="integer")
     */
    protected int $Priority;
    /**
     * @Column(type="integer")
     */
    protected ?int $Period = null;
    /**
     * @Column(type="boolean")
     */
    protected bool $IsActive;

    /**
     * @param string $name
     * @param string $priority
     * @param int|null $period
     * @param bool $isActive
     * @param int|null $id
     */
    public function __construct(string $name, string $priority, ?int $period = null, bool $isActive = true, ?int $id = null)
    {
        $this->Name = $name;
        $this->Priority = $priority;
        $this->Period = $period;
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
     * @return int
     */
    public function getPriority(): int
    {
        return $this->Priority;
    }

    /**
     * @param int $Priority
     */
    public function setPriority(int $Priority): void
    {
        $this->Priority = $Priority;
    }

    /**
     * @return int|null
     */
    public function getPeriod(): ?int
    {
        return $this->Period;
    }

    /**
     * @param int|null $Period
     */
    public function setPeriod(?int $Period): void
    {
        $this->Period = $Period;
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
     * @return string
     */
    public function getPeriodDisplayName(): string
    {

        switch ($this->getPeriod())  {
            case self::PERIOD_FIRST_PERIOD: $period = '1. Halbjahr'; break;
            case self::PERIOD_SECOND_PERIOD: $period = '2. Halbjahr'; break;
            default: $period = '';
        }

        return $period;
    }

    /**
     * @return bool
     */
    public function getIsUsed(): bool
    {
        return Grade::useService()->getIsScoreConditionUsed($this);
    }
}