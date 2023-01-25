<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblLessonTeacherLectureship")
 * @Cache(usage="READ_ONLY")
 */
class TblTeacherLectureship extends Element
{
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_YEAR = 'serviceTblYear';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_TBL_DIVISION_COURSE = 'tblLessonDivisionCourse';
    const ATTR_GROUP_NAME = 'GroupName';

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblPerson;

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblYear;

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblSubject;

    /**
     * @Column(type="bigint")
     */
    protected int $tblLessonDivisionCourse;

    /**
     * @Column(type="string")
     */
    protected string $GroupName = '';

//    /**
//     * @Column(type="bigint")
//     */
//    protected ?int $tblCoreGroup = null;
//
//    /**
//     * @Column(type="bigint")
//     */
//    protected ?int $tblTeachingGroup = null;

//    /**
//     * @Column(type="datetime")
//     */
//    protected ?DateTime $FromDate = null;

//    /**
//     * @Column(type="datetime")
//     */
//    protected ?DateTime $ToDate = null;

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     * @param string $groupName
     *
     * @return TblTeacherLectureship
     */
    public static function withParameter(TblPerson $tblPerson, TblYear $tblYear, TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject,
        string $groupName = ''): TblTeacherLectureship
    {
        $instance = new self();

        $instance->serviceTblPerson = $tblPerson->getId();
        $instance->serviceTblYear = $tblYear->getId();
        $instance->tblLessonDivisionCourse = $tblDivisionCourse->getId();
        $instance->serviceTblSubject = $tblSubject->getId();
        $instance->setGroupName($groupName);

        return  $instance;
    }

    /**
     * @param bool $IsForce
     *
     * @return false|TblPerson
     */
    public function getServiceTblPerson(bool $IsForce = false)
    {
        return Person::useService()->getPersonById($this->serviceTblPerson, $IsForce);
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson)
    {
        $this->serviceTblPerson = $tblPerson->getId();
    }

    /**
     * @return false|TblYear
     */
    public function getServiceTblYear()
    {
        return Term::useService()->getYearById($this->serviceTblYear);
    }

    /**
     * @param TblYear $tblYear
     */
    public function setServiceTblYear(TblYear $tblYear)
    {
        $this->serviceTblYear = $tblYear->getId();
    }

    /**
     * @return false|TblSubject
     */
    public function getServiceTblSubject()
    {
        return Subject::useService()->getSubjectById($this->serviceTblSubject);
    }

    /**
     * @param TblSubject $tblSubject
     */
    public function setServiceTblSubject(TblSubject $tblSubject)
    {
        $this->serviceTblSubject = $tblSubject->getId();
    }

    /**
     * @return false|TblDivisionCourse
     */
    public function getTblDivisionCourse()
    {
        return $this->tblLessonDivisionCourse ? DivisionCourse::useService()->getDivisionCourseById($this->tblLessonDivisionCourse) : false;
    }

    /**
     * @param ?TblDivisionCourse $tblDivisionCourse
     */
    public function setTblDivisionCourse(?TblDivisionCourse $tblDivisionCourse): void
    {
        $this->tblLessonDivisionCourse = $tblDivisionCourse ? $tblDivisionCourse->getId() : null;
    }

    /**
     * @return string
     */
    public function getGroupName(): string
    {
        return $this->GroupName;
    }

    /**
     * @param string $GroupName
     */
    public function setGroupName(string $GroupName): void
    {
        $this->GroupName = $GroupName;
    }

//    /**
//     * @return false|TblDivisionCourse
//     */
//    public function getTblCoreGroup()
//    {
//        return $this->tblCoreGroup ? DivisionCourse::useService()->getDivisionCourseById($this->tblCoreGroup) : false;
//    }
//
//    /**
//     * @param ?TblDivisionCourse $tblCoreGroup
//     */
//    public function setTblCoreGroup(?TblDivisionCourse $tblCoreGroup): void
//    {
//        $this->tblCoreGroup = $tblCoreGroup ? $tblCoreGroup->getId() : null;
//    }
//
//    /**
//     * @return false|TblDivisionCourse
//     */
//    public function getTblTeachingGroup()
//    {
//        return $this->tblTeachingGroup ? DivisionCourse::useService()->getDivisionCourseById($this->tblTeachingGroup) : false;
//    }
//
//    /**
//     * @param ?TblDivisionCourse $tblTeachingGroup
//     */
//    public function setTblTeachingGroup(?TblDivisionCourse $tblTeachingGroup): void
//    {
//        $this->tblTeachingGroup = $tblTeachingGroup ? $tblTeachingGroup->getId() : null;
//    }

//    /**
//     * @return string
//     */
//    public function getFromDate(): string
//    {
//        return $this->FromDate instanceof DateTime ? $this->FromDate->format('d.m.Y') : '';
//    }
//
//    /**
//     * @param null|DateTime $Date
//     */
//    public function setFromDate(DateTime $Date = null)
//    {
//        $this->FromDate = $Date;
//    }
//
//    /**
//     * @return ?DateTime
//     */
//    public function getFromDateTime(): ?DateTime
//    {
//        return $this->FromDate;
//    }

//    /**
//     * @return string
//     */
//    public function getToDate(): string
//    {
//        return $this->ToDate instanceof DateTime ? $this->ToDate->format('d.m.Y') : '';
//    }
//
//    /**
//     * @param null|DateTime $Date
//     */
//    public function setToDate(DateTime $Date = null)
//    {
//        $this->ToDate = $Date;
//    }
//
//    /**
//     * @return ?DateTime
//     */
//    public function getToDateTime(): ?DateTime
//    {
//        return $this->ToDate;
//    }

    /**
     * @return string
     */
    public function getYearName(): string
    {
        if (($tblYear = $this->getServiceTblYear())) {
            return $tblYear->getDisplayName();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getTeacherName(): string
    {
        if (($tblPerson = $this->getServiceTblPerson())) {
            $acronym = (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson)) ? ' (' . $tblTeacher->getAcronym() . ')' : '');

            return $acronym ? $tblPerson->getLastName() . $acronym : $tblPerson->getLastName();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getCourseName(): string
    {
        if (($tblDivisionCourse = $this->getTblDivisionCourse())) {
            return $tblDivisionCourse->getDisplayName();
        }

        return "";
    }

    /**
     * @return string
     */
    public function getSubjectName(): string
    {
        if (($tblSubject = $this->getServiceTblSubject())) {
            return $tblSubject->getDisplayName();
        }

        return "";
    }

    /**
     * @param bool $isString
     * @return false|string|TblPerson[]
     */
    public function getSubjectTeachers(bool $isString = true)
    {
        $tblPersonList = array();
        if (($tblDivisionCourse = $this->getTblDivisionCourse())
            && ($tblSubject = $this->getServiceTblSubject())
        ) {
            if (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy(null, null, $tblDivisionCourse, $tblSubject))) {
                foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                    if (($tblPerson = $tblTeacherLectureship->getServiceTblPerson())) {
                        $tblPersonList[$tblPerson->getId()] = $isString ? $tblTeacherLectureship->getTeacherName() : $tblPerson;
                    }
                }
            }
        }

        return empty($tblPersonList)
            ? false
            : ($isString ? implode(", ", $tblPersonList) : $tblPersonList);
    }


    /**
     * für Sortierung
     *
     * @return string
     */
    public function getSort(): string
    {
        if (($tblPerson = $this->getServiceTblPerson())
            && ($tblSubject = $this->getServiceTblSubject())
            && ($tblDivisionCourse = $this->getTblDivisionCourse())
        ) {
            return $tblPerson->getLastFirstName() . '_' . $tblSubject->getName() . '_' . $tblDivisionCourse->getName();
        }

        return '';
    }

    /**
     * für Sortierung
     *
     * @return string
     */
    public function getSortCourseName(): string
    {
        if (($tblDivisionCourse = $this->getTblDivisionCourse())) {
            return $tblDivisionCourse->getName();
        }

        return '';
    }
}