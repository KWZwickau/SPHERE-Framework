<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblClassRegisterLessonContent")
 * @Cache(usage="READ_ONLY")
 */
class TblLessonContent extends Element
{
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_SERVICE_TBL_GROUP = 'serviceTblGroup';
    const ATTR_DATE = 'Date';
    const ATTR_LESSON = 'Lesson';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblGroup;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblYear;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;

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
     * @return bool|TblDivision
     */
    public function getServiceTblDivision()
    {
        if (null === $this->serviceTblDivision) {
            return false;
        } else {
            return Division::useService()->getDivisionById($this->serviceTblDivision);
        }
    }

    /**
     * @param TblDivision|null $tblDivision
     */
    public function setServiceTblDivision(TblDivision $tblDivision = null)
    {
        $this->serviceTblDivision = (null === $tblDivision ? null : $tblDivision->getId());
    }

    /**
     * @return bool|TblGroup
     */
    public function getServiceTblGroup()
    {
        if(null === $this->serviceTblGroup){
            return false;
        } else {
            return Group::useService()->getGroupById($this->serviceTblGroup);
        }
    }

    /**
     * @param null|TblGroup $serviceTblGroup
     */
    public function setServiceTblGroup(TblGroup $serviceTblGroup = null)
    {
        $this->serviceTblGroup = (null === $serviceTblGroup ? null : $serviceTblGroup->getId());
    }

    /**
     * @return bool|TblYear
     */
    public function getServiceTblYear()
    {
        if (null === $this->serviceTblYear) {
            return false;
        } else {
            return Term::useService()->getYearById($this->serviceTblYear);
        }
    }

    /**
     * @param TblYear|null $tblYear
     */
    public function setServiceTblYear(TblYear $tblYear = null)
    {
        $this->serviceTblYear = ( null === $tblYear ? null : $tblYear->getId() );
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
     * @return string
     */
    public function getContent(): string
    {
        return $this->Content;
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
}