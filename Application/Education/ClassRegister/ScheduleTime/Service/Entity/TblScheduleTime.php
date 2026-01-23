<?php

namespace SPHERE\Application\Education\ClassRegister\ScheduleTime\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\ClassRegister\ScheduleTime\ScheduleTime;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblClassRegisterScheduleTime")
 * @Cache(usage="READ_ONLY")
 */
class TblScheduleTime extends Element
{
    const SECONDARY_LEVEL_ALL = -1;
    const SECONDARY_LEVEL_ONLY_FIRST = 1;
    const SECONDARY_LEVEL_ONLY_SECOND = 2;

    /**
     * @Column(type="string")
     */
    protected string $Name;
    /**
     * @Column(type="smallint")
     */
    protected int $SecondaryLevel;

    /**
     * @param string $Name
     *
     * @return $this
     */
    public function setName(string $Name): TblScheduleTime
    {
        $this->Name = $Name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->Name;
    }

    /**
     * @param int $SecondaryLevel
     *
     * @return $this
     */
    public function setSecondaryLevel(int $SecondaryLevel): TblScheduleTime
    {
        $this->SecondaryLevel = $SecondaryLevel;

        return $this;
    }

    /**
     * @return int
     */
    public function getSecondaryLevel(): int
    {
        return $this->SecondaryLevel;
    }

    /**
     * @return string
     */
    public function getDisplaySchoolTypes(): string
    {
        return ScheduleTime::useService()->getDisplaySchoolTypesByScheduleTime($this);
    }

    /**
     * @return string
     */
    public function getDisplaySecondaryLevel(): string
    {
        return self::getDisplaySecondaryLevelBySecondaryLevel($this->SecondaryLevel);
    }

    /**
     * @param int $secondaryLevel
     *
     * @return string
     */
    public static function getDisplaySecondaryLevelBySecondaryLevel(int $secondaryLevel): string
    {
        return match ($secondaryLevel) {
            self::SECONDARY_LEVEL_ALL => 'Alle Sekundarstufen',
            self::SECONDARY_LEVEL_ONLY_FIRST => 'Nur Sekundarstufe I',
            self::SECONDARY_LEVEL_ONLY_SECOND => 'Nur Sekundarstufe II',
        };
    }
}