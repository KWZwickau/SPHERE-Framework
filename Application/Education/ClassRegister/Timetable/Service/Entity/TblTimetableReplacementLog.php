<?php
namespace SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * @Entity
 * @Table(name="tblClassRegisterTimetableReplacementLog")
 * @Cache(usage="READ_ONLY")
 */
class TblTimetableReplacementLog extends Element
{

    const ATTR_SCHOOL_NAME = 'SchoolName';
    const ATTR_DATE = 'Date';
    const ATTR_HOUR = 'Hour';
    const ATTR_ROOM = 'Room';
    const ATTR_IS_CANCELED = 'IsCanceled';
//    const ATTR_SUBJECT_STRING = 'Subject';
//    const ATTR_SUBJECT_SUBSTITUTE = 'SubjectSubstitute';
    const ATTR_COURSE = 'Course';
    const ATTR_SERVICE_TBL_PERSON = 'PersonAcronym';
    const ATTR_ERROR = 'Error';

    /** @Column(type="string") */
    protected string $Date;
    /** @Column(type="string") */
    protected string $Hour;
    /** @Column(type="string") */
    protected string $Room;
    /** @Column(type="boolean") */
    protected bool $IsCanceled;
    /** @Column(type="string") */
    protected string $Subject;
    /** @Column(type="string") */
    protected string $SubjectSubstitute;
    /** @Column(type="string") */
    protected string $Course;
    /** @Column(type="string") */
    protected string $PersonAcronym;
    /** @Column(type="string") */
    protected string $Error;

    /**
     * @return string
     */
    public function getDate(): string
    {

        return $this->Date;
    }

    /**
     * @param string $Date
     * @return void
     */
    public function setDate(string $Date): void
    {
        $this->Date = $Date;
    }

    /**
     * @return string
     */
    public function getHour():string
    {

        return $this->Hour;
    }

    /**
     * @param string $Hour
     * @return void
     */
    public function setHour(string $Hour): void
    {

        $this->Hour = $Hour;
    }

    /**
     * @return string
     */
    public function getRoom(): string
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
     * @return bool
     */
    public function getIsCanceled(): bool
    {

        return $this->IsCanceled;
    }

    /**
     * @param bool $IsCanceled
     * @return void
     */
    public function setIsCanceled(bool $IsCanceled): void
    {

        $this->IsCanceled = $IsCanceled;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {

        return $this->Subject;
    }

    /**
     * @param string $Subject
     * @return void
     */
    public function setSubject(string $Subject): void
    {

        $this->Subject = $Subject;
    }

    /**
     * @return $SubjectSubstitute
     */
    public function getSubjectSubstitute(): string
    {

        return $this->SubjectSubstitute;
    }

    /**
     * @param string $SubjectSubstitute
     * @return void
     */
    public function setSubjectSubstitute(string $SubjectSubstitute): void
    {
        $this->SubjectSubstitute = $SubjectSubstitute;
    }

    /**
     * @return string
     */
    public function getCourse(): string
    {

        return $this->Course;
    }

    /**
     * @param string $Course
     * @return void
     */
    public function setCourse(string $Course): void
    {

        $this->Course = $Course;
    }

    /**
     * @return string
     */
    public function getPersonAcronym(): string
    {

        return $this->PersonAcronym;
    }

    /**
     * @param string $PersonAcronym
     * @return void
     */
    public function setPersonAcronym(string $PersonAcronym): void
    {
        $this->PersonAcronym = $PersonAcronym;
    }

    /**
     * @return string
     */
    public function getError(): string
    {

        return $this->Error;
    }

    /**
     * @param string $Error
     * @return void
     */
    public function setError(string $Error): void
    {
        $this->Error = $Error;
    }
}
