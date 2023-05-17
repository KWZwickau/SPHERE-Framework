<?php
namespace SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblClassRegisterTimetableNode")
 * @Cache(usage="READ_ONLY")
 */
class TblTimetableNode extends Element
{
    const WEEK_DAY_MONDAY = '1';
    const WEEK_DAY_TUESDAY = '2';
    const WEEK_DAY_WEDNESDAY = '3';
    const WEEK_DAY_THURSDAY = '4';
    const WEEK_DAY_FRIDAY = '5';
    const WEEK_DAY_SATURDAY = '6';
    const WEEK_DAY_SUNDAY = '7';

    const ATTR_HOUR = 'Hour';
    const ATTR_DAY = 'Day';
    const ATTR_WEEK = 'Week';
    const ATTR_ROOM = 'Room';
    const ATTR_SUBJECT_GROUP = 'SubjectGroup';
    const ATTR_LEVEL = 'Level';
    const ATTR_SERVICE_TBL_COURSE = 'serviceTblCourse';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_TBL_CLASS_REGISTER_TIMETABLE = 'tblClassRegisterTimetable';


    /**
     * @Column(type="smallint")
     */
    protected int $Hour;
    /**
     * @Column(type="smallint")
     */
    protected int $Day;
    /**
     * @Column(type="string")
     */
    protected string $Week;
    /**
     * @Column(type="string")
     */
    protected string $Room;
    /**
     * @Column(type="string")
     */
    protected string $SubjectGroup;
    /**
     * @Column(type="string")
     */
    protected string $Level;
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblCourse;
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblSubject;
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected int $tblClassRegisterTimetable;

    /**
     * @return int
     */
    public function getDay():int
    {

        return $this->Day;
    }

    public function getDisplayDay($lettercount = 10)
    {
        if($lettercount > 0){
            switch ($this->Day){
                case self::WEEK_DAY_MONDAY:
                    return substr('Montag', 0, $lettercount);
                case self::WEEK_DAY_TUESDAY:
                    return substr('Dienstag', 0, $lettercount);
                case self::WEEK_DAY_WEDNESDAY:
                    return substr('Mittwoch', 0, $lettercount);
                case self::WEEK_DAY_THURSDAY:
                    return substr('Donnerstag', 0, $lettercount);
                case self::WEEK_DAY_FRIDAY:
                    return substr('Freitag', 0, $lettercount);
                case self::WEEK_DAY_SUNDAY:
                    return substr('Samstag', 0, $lettercount);
                case self::WEEK_DAY_SATURDAY:
                    return substr('Sonntag', 0, $lettercount);
            }
        }
        return $this->Day;
    }

    /**
     * @param int $Day
     * @return void
     */
    public function setDay(int $Day): void
    {

        $this->Day = $Day;
    }

    /**
     * @return integer
     */
    public function getHour():int
    {

        return $this->Hour;
    }

    /**
     * @param int $Hour
     * @return void
     */
    public function setHour(int $Hour): void
    {

        $this->Hour = $Hour;
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
     * @return string
     */
    public function getRoom():string
    {

        return $this->Room;
    }

    /**
     * @param string $Room
     * @return void
     */
    public function setRoom(string $Room): void
    {

        $this->Room = $Room;
    }

    /**
     * @return string
     */
    public function getSubjectGroup():string
    {

        return $this->SubjectGroup;
    }

    /**
     * @param string $SubjectGroup
     * @return void
     */
    public function setSubjectGroup(string $SubjectGroup): void
    {

        $this->SubjectGroup = $SubjectGroup;
    }

    /**
     * @return string
     */
    public function getLevel():string
    {

        return $this->Level;
    }

    /**
     * @param string $Level
     * @return void
     */
    public function setLevel(string $Level): void
    {

        $this->Level = $Level;
    }

    /**
     * @return TblDivisionCourse|null
     */
    public function getServiceTblCourse(): ?TblDivisionCourse
    {
        if (null !== $this->serviceTblCourse) {
            $tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($this->serviceTblCourse);
            return $this->changeFalseToNull($tblDivisionCourse);
        }

        return null;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @return void
     */
    public function setServiceTblCourse(TblDivisionCourse $tblDivisionCourse): void
    {
        $this->serviceTblCourse = $tblDivisionCourse->getId();
    }

    /**
     * @return TblSubject|null
     */
    public function getServiceTblSubject(): ?TblSubject
    {

        if (null !== $this->serviceTblSubject) {
            $tblSubject = Subject::useService()->getSubjectById($this->serviceTblSubject);
            return $this->changeFalseToNull($tblSubject);
        }
        return null;
    }

    /**
     * @param TblSubject $tblSubject
     * @return void
     */
    public function setServiceTblSubject(TblSubject $tblSubject): void
    {

        $this->serviceTblSubject = $tblSubject->getId();
    }

    /**
     * @return TblPerson|null
     */
    public function getServiceTblPerson(): ?TblPerson
    {

        if (null !== $this->serviceTblPerson) {
            $tblPerson = Person::useService()->getPersonById($this->serviceTblPerson);
            return $this->changeFalseToNull($tblPerson);
        }
        return null;
    }

    /**
     * @param TblPerson $tblPerson
     * @return void
     */
    public function setServiceTblPerson(TblPerson $tblPerson): void
    {

        $this->serviceTblPerson = $tblPerson->getId();
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
