<?php
namespace SPHERE\Application\Platform\System\BasicData\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblMaintenance")
 * @Cache(usage="READ_ONLY")
 */
class TblMaintenance extends Element
{

    const ATTR_MAINTENANCE_DATE = 'MaintenanceDate';

    /**
     * @Column(type="datetime")
     */
    protected \DateTime $StartDate;
    /**
     * @Column(type="datetime")
     */
    protected \DateTime $MaintenanceDate;
    /**
     * @Column(type="string")
     */
    protected string $PreWarningTime;
    /**
     * @Column(type="string")
     */
    protected string $ActiveWarningTime;
    /**
     * @Column(type="string")
     */
    protected string $EndWarningTime;

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->StartDate;
    }

    /**
     * @param string $StartDate
     */
    public function setStartDate(string $StartDate): void
    {
        $this->StartDate = new \DateTime($StartDate);
    }

    /**
     * @return \DateTime
     */
    public function getMaintenanceDate()
    {
        return $this->MaintenanceDate;
    }

    /**
     * @param string $MaintenanceDate
     */
    public function setMaintenanceDate(string $MaintenanceDate): void
    {
        $this->MaintenanceDate = new \DateTime($MaintenanceDate);
    }

    /**
     * @return string
     */
    public function getPreWarningTime()
    {
        return $this->PreWarningTime;
    }

    /**
     * @param string $PreWarningTime
     */
    public function setPreWarningTime(string$PreWarningTime): void
    {
        $this->PreWarningTime = $PreWarningTime;
    }

    /**
     * @return string
     */
    public function getActiveWarningTime()
    {
        return $this->ActiveWarningTime;
    }

    /**
     * @param string $ActiveWarningTime
     */
    public function setActiveWarningTime(string$ActiveWarningTime): void
    {
        $this->ActiveWarningTime = $ActiveWarningTime;
    }

    /**
     * @return string
     */
    public function getEndWarningTime()
    {
        return $this->EndWarningTime;
    }

    /**
     * @param string $EndWarningTime
     */
    public function setEndWarningTime(string$EndWarningTime): void
    {
        $this->EndWarningTime = $EndWarningTime;
    }
}