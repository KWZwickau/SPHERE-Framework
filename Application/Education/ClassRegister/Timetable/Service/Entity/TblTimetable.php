<?php
namespace SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity;

use DateTime as DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblClassRegisterTimetable")
 * @Cache(usage="READ_ONLY")
 */
class TblTimetable extends Element
{


    const ATTR_NAME = 'Name';
    const ATTR_DESCRIPTION = 'Description';
    const ATTR_DATE_FROM = 'DateFrom';
    const ATTR_DATE_TO = 'DateTo';

    /**
     * @Column(type="string")
     */
    protected string $Name;
    /**
     * @Column(type="string")
     */
    protected string $Description;
    /**
     * @Column(type="datetime")
     */
    protected DateTime $DateFrom;
    /**
     * @Column(type="datetime")
     */
    protected DateTime $DateTo;

    /**
     * @return string
     */
    public function getName():string
    {

        return $this->Name;
    }

    /**
     * @param string $Name
     * @return void
     */
    public function setName(string $Name): void
    {

        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getDescription():string
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     * @return void
     */
    public function setDescription(string $Description = ''): void
    {

        $this->Description = $Description;
    }

    /**
     * @param bool $getDateTimeObjekt
     * false = string; true = DateTimeObject
     *
     * @return string|DateTime
     */
    public function getDateFrom($getDateTimeObjekt = false)
    {

        if(null === $this->DateFrom){
            return false;
        }
        /** @var DateTime $Date */
        $Date = $this->DateFrom;
        if($Date instanceof DateTime){
            if($getDateTimeObjekt){
                return $Date;
            } else {
                return $Date->format('d.m.Y');
            }
        }
        return $Date;
    }

    /**
     * @param DateTime $DateFrom
     */
    public function setDateFrom(DateTime $DateFrom)
    {
        $this->DateFrom = $DateFrom;
    }

    /**
     * @param bool $getDateTimeObjekt
     * false = string; true = DateTimeObject
     *
     * @return string
     */
    public function getDateTo($getDateTimeObjekt = false)
    {
        if(null === $this->DateTo){
            return false;
        }
        /** @var DateTime $Date */
        $Date = $this->DateTo;
        if($Date instanceof DateTime){
            if($getDateTimeObjekt){
                return $Date;
            } else {
                return $Date->format('d.m.Y');
            }
        }
        return $Date;
    }

    /**
     * @param DateTime $DateTo
     */
    public function setDateTo(DateTime $DateTo)
    {
        $this->DateTo = $DateTo;
    }

}
