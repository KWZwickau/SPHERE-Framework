<?php

namespace SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBasket")
 * @Cache(usage="READ_ONLY")
 */
class TblBasket extends Element
{

    const ATTR_NAME = 'Name';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="text")
     */
    protected $Description;
    /**
     * @Column(type="string")
     */
    protected $Year;
    /**
     * @Column(type="string")
     */
    protected $Month;
    /**
     * @Column(type="datetime")
     */
    protected $TargetTime;

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
     * @return string
     */
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }

    /**
     * @return string
     */
    public function getYear()
    {
        return $this->Year;
    }

    /**
     * @param string $Year
     */
    public function setYear($Year)
    {
        $this->Year = $Year;
    }

    /**
     * @param bool $IsFrontend
     *
     * @return string
     */
    public function getMonth($IsFrontend = false)
    {
        if($IsFrontend){
            if(strlen($this->Month) == 1) {
                $Month = '0'.$this->Month;
            } else {
                $Month = $this->Month;
            }
            return $Month;
        }
        return $this->Month;
    }

    /**
     * @param string $Month
     */
    public function setMonth($Month)
    {
        $this->Month = $Month;
    }

    /**
     * @return string
     */
    public function getTargetTime()
    {

        if(null === $this->TargetTime) {
            return false;
        }
        /** @var \DateTime $TargetTime */
        $TargetTime = $this->TargetTime;
        if($TargetTime instanceof \DateTime) {
            return $TargetTime->format('d.m.Y');
        } else {
            return (string)$TargetTime;
        }
    }

    /**
     * @param null|\DateTime $TargetTime
     */
    public function setTargetTime(\DateTime $TargetTime = null)
    {
        $this->TargetTime = $TargetTime;
    }
}
