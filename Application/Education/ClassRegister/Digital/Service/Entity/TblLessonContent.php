<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Text\Repository\Strikethrough;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblClassRegisterLessonContent")
 * @Cache(usage="READ_ONLY")
 */
class TblLessonContent extends Element
{
    const ATTR_SERVICE_TBL_DIVISION_COURSE = 'serviceTblDivision';
    const ATTR_DATE = 'Date';
    const ATTR_LESSON = 'Lesson';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblGroup = null;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblYear = null;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubstituteSubject;

    /**
     * @Column(type="boolean")
     */
    protected $IsCanceled;

    /**
     * @Column(type="datetime")
     */
    protected $Date;

    /**
     * @Column(type="integer")
     */
    protected int $Lesson;

    /**
     * @Column(type="string")
     */
    protected string $Content;

    /**
     * @Column(type="string")
     */
    protected string $Homework;

    /**
     * @Column(type="string")
     */
    protected string $Room;

    /**
     * @return bool|TblDivisionCourse
     */
    public function getServiceTblDivisionCourse()
    {
        if (null === $this->serviceTblDivision) {
            return false;
        } else {
            return DivisionCourse::useService()->getDivisionCourseById($this->serviceTblDivision);
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     */
    public function setServiceTblDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        $this->serviceTblDivision = $tblDivisionCourse->getId();
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {
        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {
        $this->serviceTblPerson = (null === $tblPerson ? null : $tblPerson->getId());
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubject()
    {
        if (null === $this->serviceTblSubject) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubject);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubject(TblSubject $tblSubject = null)
    {
        $this->serviceTblSubject = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubstituteSubject()
    {
        if (null === $this->serviceTblSubstituteSubject) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubstituteSubject);
        }
    }

    /**
     * @param TblSubject|null $tblSubstituteSubject
     */
    public function setServiceTblSubstituteSubject(TblSubject $tblSubstituteSubject = null)
    {
        $this->serviceTblSubstituteSubject = ( null === $tblSubstituteSubject ? null : $tblSubstituteSubject->getId() );
    }

    /**
     * @return string
     */
    public function getDate()
    {
        if (null === $this->Date) {
            return false;
        }
        /** @var DateTime $Date */
        $Date = $this->Date;
        if ($Date instanceof DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @param null|DateTime $Date
     */
    public function setDate(DateTime $Date = null)
    {
        $this->Date = $Date;
    }

    /**
     * @return integer
     */
    public function getLesson(): int
    {
        return $this->Lesson;
    }

    /**
     * @return string
     */
    public function getLessonDisplay(): string
    {
        return $this->Lesson === null ? '' : $this->Lesson . '. Unterrichtseinheit';
    }

    /**
     * @param integer $Lesson
     */
    public function setLesson(int $Lesson)
    {
        $this->Lesson = $Lesson;
    }

    /**
     * @param bool $ShowCanceled
     *
     * @return string
     */
    public function getContent(bool $ShowCanceled = true): string
    {
        if ($this->Content) {
            return $this->Content;
        }

        if ($ShowCanceled && $this->getIsCanceled()) {
            return 'Ausgefallen';
        }

        return '';
    }

    /**
     * @param string $Content
     */
    public function setContent(string $Content)
    {
        $this->Content = $Content;
    }

    /**
     * @return string
     */
    public function getHomework(): string
    {
        return $this->Homework;
    }

    /**
     * @param string $Homework
     */
    public function setHomework(string $Homework)
    {
        $this->Homework = $Homework;
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
     */
    public function setRoom(string $Room): void
    {
        $this->Room = $Room;
    }

    /**
     * @param bool $IsToolTip
     *
     * @return string
     */
    public function getTeacherString(bool $IsToolTip = true): string
    {
        return $this->getServiceTblPerson()
            ? Digital::useService()->getTeacherString($this->getServiceTblPerson(), $IsToolTip)
            : '';
    }

    /**
     * @return bool
     */
    public function getIsCanceled()
    {
        return $this->IsCanceled || ($this->getServiceTblSubject() && $this->getServiceTblSubstituteSubject());
    }

    /**
     * @param bool $IsCanceled
     */
    public function setIsCanceled($IsCanceled): void
    {
        $this->IsCanceled = (bool) $IsCanceled;
    }

    /**
     * @param bool $isAcronymOnly
     *
     * @return string
     */
    public function getDisplaySubject(bool $isAcronymOnly): string
    {
        $tblSubject = $this->getServiceTblSubject();
        $tblSubstituteSubject = $this->getServiceTblSubstituteSubject();
        $subject = '';
        if ($this->getIsCanceled()) {
            $subject = ($tblSubject ? new Strikethrough($isAcronymOnly ? $tblSubject->getAcronym() : $tblSubject->getDisplayName()) . ' ' : '')
                . ($tblSubstituteSubject ? ($isAcronymOnly ? $tblSubstituteSubject->getAcronym() : $tblSubstituteSubject->getDisplayName()) : '');
        } elseif ($tblSubject) {
            $subject = $isAcronymOnly ? $tblSubject->getAcronym() : $tblSubject->getDisplayName();
        } elseif ($tblSubstituteSubject) {
            $subject = $isAcronymOnly ? $tblSubstituteSubject->getAcronym() : $tblSubstituteSubject->getDisplayName();
        }

        return $subject;
    }

    /**
     * @return false|TblLessonContent[]
     */
    public function getLinkedLessonContentAll()
    {
        return Digital::useService()->getLessonContentLinkAllByLessonContent($this);
    }
}