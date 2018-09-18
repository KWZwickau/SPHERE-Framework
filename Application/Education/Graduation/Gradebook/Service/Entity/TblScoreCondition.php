<?php
namespace SPHERE\Application\Education\Graduation\Gradebook\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblScoreCondition")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreCondition extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_IS_ACTIVE = 'IsActive';
    const PERIOD_FULL_YEAR = -1;
    const PERIOD_FIRST_PERIOD = 1;
    const PERIOD_SECOND_PERIOD = 2;

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="integer")
     */
    protected $Priority;

    /**
     * @Column(type="string")
     */
    protected $Round;

    /**
     * @Column(type="boolean")
     */
    protected $IsActive;

    /**
     * @Column(type="integer")
     */
    protected $Period;

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {

        $this->Name = $Name;
    }

    /**
     * @return integer
     */
    public function getPriority()
    {

        return $this->Priority;
    }

    /**
     * @param integer $Priority
     */
    public function setPriority($Priority)
    {

        $this->Priority = $Priority;
    }

    /**
     * @return string
     */
    public function getRound()
    {

        return $this->Round;
    }

    /**
     * @param string $Round
     */
    public function setRound($Round)
    {

        $this->Round = $Round;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->IsActive;
    }

    /**
     * @param boolean $IsActive
     */
    public function setIsActive($IsActive)
    {
        $this->IsActive = (boolean) $IsActive;
    }

    /**
     * @return bool
     */
    public function isUsed()
    {

        return Gradebook::useService()->isScoreConditionUsed($this);
    }

    /**
     * @return integer
     */
    public function getPeriod()
    {
        return $this->Period;
    }

    /**
     * @param integer $Period
     */
    public function setPeriod($Period)
    {
        $this->Period = $Period;
    }

    /**
     * @return string
     */
    public function getPeriodDisplayName()
    {

        switch ($this->getPeriod())  {
            case self::PERIOD_FIRST_PERIOD: $period = '1. Halbjahr'; break;
            case self::PERIOD_SECOND_PERIOD: $period = '2. Halbjahr'; break;
            default: $period = '';
        }

        return $period;
    }
}
