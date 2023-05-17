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

/**
 * @Entity
 * @Table(name="tblClassRegisterTimetableReplacement")
 * @Cache(usage="READ_ONLY")
 */
class TblTimetableReplacement extends Element
{

    const ATTR_DATE = 'Date';
    const ATTR_HOUR = 'Hour';
    const ATTR_ROOM = 'Room';
    const ATTR_IS_CANCELED = 'IsCanceled';
    const ATTR_SUBJECT_GROUP = 'SubjectGroup';
    const ATTR_SERVICE_TBL_COURSE = 'serviceTblCourse';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_SERVICE_TBL_SUBSTITUTE_SUBJECT = 'serviceTblSubstituteSubject';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';


    /**
     * @Column(type="datetime")
     */
    protected $Date;
    /**
     * @Column(type="smallint")
     */
    protected int $Hour;
    /**
     * @Column(type="string")
     */
    protected string $Room;
    /**
     * @Column(type="boolean")
     */
    protected bool $IsCanceled;
    /**
     * @Column(type="string")
     */
    protected string $SubjectGroup;
//    /**
//     * @Column(type="string")
//     */
//    protected string $Level;
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
    protected $serviceTblSubstituteSubject;
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblPerson;

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
        } else {
            if($getDateTimeObjekt){
                return new \DateTime($Date);
            } else {
                return (string)$Date;
            }
        }
    }

    /**
     * @param null|\DateTime $Date
     */
    public function setDate(\DateTime $Date = null)
    {
        $this->Date = $Date;
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
    public function getIsCanceled():string
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
     * @param TblSubject|null $tblSubject
     * @return void
     */
    public function setServiceTblSubject(TblSubject $tblSubject = null): void
    {

        $this->serviceTblSubject = ( !$tblSubject ? false : $tblSubject->getId() );
    }

    /**
     * @return TblSubject|null
     */
    public function getServiceTblSubstituteSubject()
    {
        if (null !== $this->serviceTblSubstituteSubject) {
            $tblSubstituteSubject = Subject::useService()->getSubjectById($this->serviceTblSubstituteSubject);
            return $this->changeFalseToNull($tblSubstituteSubject);
        }
        return null;
    }

    /**
     * @param TblSubject|null $tblSubstituteSubject
     * @return void
     */
    public function setServiceTblSubstituteSubject(TblSubject $tblSubstituteSubject = null): void
    {
        $this->serviceTblSubstituteSubject = ( !$tblSubstituteSubject ? false : $tblSubstituteSubject->getId() );
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

}
