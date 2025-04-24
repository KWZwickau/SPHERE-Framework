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
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblClassRegisterForgotten")
 * @Cache(usage="READ_ONLY")
 */
class TblForgotten extends Element
{
    const ATTR_SERVICE_TBL_DIVISION_COURSE = 'serviceTblDivisionCourse';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_DATE = 'Date';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivisionCourse;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;

    /**
     * @Column(type="datetime")
     */
    protected $Date;

    // Type
    /**
     * @Column(type="boolean")
     */
    protected $IsHomework;

    /**
     * @Column(type="bigint")
     */
    protected $tblClassRegisterLessonContent;

    /**
     * @Column(type="bigint")
     */
    protected $tblClassRegisterCourseContent;

    /**
     * @Column(type="string")
     */
    protected string $Remark;

    /**
     * @return bool|TblDivisionCourse
     */
    public function getServiceTblDivisionCourse(): TblDivisionCourse|bool
    {
        if (null === $this->serviceTblDivisionCourse) {
            return false;
        } else {
            return DivisionCourse::useService()->getDivisionCourseById($this->serviceTblDivisionCourse);
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     */
    public function setServiceTblDivisionCourse(TblDivisionCourse $tblDivisionCourse): void
    {
        $this->serviceTblDivisionCourse = $tblDivisionCourse->getId();
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubject(): TblSubject|bool
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
    public function setServiceTblSubject(TblSubject $tblSubject = null): void
    {
        $this->serviceTblSubject = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return string
     */
    public function getDate(): string
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
    public function setDate(DateTime $Date = null): void
    {
        $this->Date = $Date;
    }

    /**
     * @return bool
     */
    public function getIsHomework(): bool
    {
        return $this->IsHomework;
    }

    /**
     * @param bool $IsHomework
     */
    public function setIsHomework(bool $IsHomework): void
    {
        $this->IsHomework = $IsHomework;
    }

    /**
     * @return bool|TblLessonContent
     */
    public function getTblLessonContent(): TblLessonContent|bool
    {
        if (null === $this->tblClassRegisterLessonContent) {
            return false;
        } else {
            return Digital::useService()->getLessonContentById($this->tblClassRegisterLessonContent);
        }
    }

    /**
     * @param TblLessonContent|null $tblLessonContent
     */
    public function setTblLessonContent(?TblLessonContent $tblLessonContent): void
    {
        $this->tblClassRegisterLessonContent = $tblLessonContent?->getId();
    }

    /**
     * @return bool|TblCourseContent
     */
    public function getTblCourseContent(): TblCourseContent|bool
    {
        if (null === $this->tblClassRegisterCourseContent) {
            return false;
        } else {
            return Digital::useService()->getCourseContentById($this->tblClassRegisterCourseContent);
        }
    }

    /**
     * @param TblCourseContent|null $tblCourseContent
     */
    public function setTblCourseContent(?TblCourseContent $tblCourseContent): void
    {
        $this->tblClassRegisterCourseContent = $tblCourseContent?->getId();
    }

    /**
     * @return string
     */
    public function getRemark(): string
    {
        return $this->Remark;
    }

    /**
     * @param string $Remark
     */
    public function setRemark(string $Remark): void
    {
        $this->Remark = $Remark;
    }
}