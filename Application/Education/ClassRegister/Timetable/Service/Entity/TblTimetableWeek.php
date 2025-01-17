<?php
namespace SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblClassRegisterTimetableWeek")
 * @Cache(usage="READ_ONLY")
 */
class TblTimetableWeek extends Element
{

    const ATTR_NUMBER = 'Number';
    const ATTR_WEEK = 'Week';
    const ATTR_DATE = 'Date';
    const ATTR_TBL_CLASS_REGISTER_TIMETABLE = 'tblClassRegisterTimetable';


    /**
     * @Column(type="string")
     */
    protected string $Number;
    /**
     * @Column(type="string")
     */
    protected string $Week;
    /**
     * @Column(type="datetime")
     */
    protected \DateTime $Date;
    /**
     * @Column(type="bigint")
     */
    protected int $tblClassRegisterTimetable;

    /**
     * @return string
     */
    public function getNumber():string
    {

        return $this->Number;
    }

    /**
     * @param string $Number
     * @return void
     */
    public function setNumber(string $Number): void
    {

        $this->Number = $Number;
    }

    /**
     * @return string
     */
    public function getWeek():string
    {

        return $this->Week;
    }

    /**
     * @param string $Week
     * @return void
     */
    public function setWeek(string $Week): void
    {

        $this->Week = $Week;
    }

    /**
     * @param bool $getDateTimeObjekt
     * false = string; true = DateTimeObject
     *
     * @return string
     */
    public function getDate($getDateTimeObjekt = false)
    {
        if(null === $this->Date){
            return false;
        }
        /** @var \DateTime $Date */
        $Date = $this->Date;
        if($Date instanceof \DateTime){
            if($getDateTimeObjekt){
                return $Date;
            } else {
                return $Date->format('d.m.Y');
            }
        }
        return $Date;
    }

    /**
     * @param \DateTime $Date
     */
    public function setDate(\DateTime $Date)
    {
        $this->Date = $Date;
    }

    /**
     * @return tblTimetable|null
     * @throws \Exception
     */
    public function getTblTimetable(): ?tblTimetable
    {

        if (null !== $this->tblClassRegisterTimetable) {
            $tblTimetable = Timetable::useService()->getTimetableById($this->tblClassRegisterTimetable);
            return $this->changeFalseToNull($tblTimetable);
        }
        return null;
    }

    /**
     * @param TblTimetable $tblTimetable
     * @return void
     */
    public function setTblTimetable(TblTimetable $tblTimetable): void
    {

        $this->tblClassRegisterTimetable = $tblTimetable->getId();
    }

}
