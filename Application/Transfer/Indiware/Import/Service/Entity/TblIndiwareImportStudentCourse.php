<?php

namespace SPHERE\Application\Transfer\Indiware\Import\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Transfer\Indiware\Import\Import;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblIndiwareImportStudentCourse")
 * @Cache(usage="READ_ONLY")
 */
class TblIndiwareImportStudentCourse extends Element
{

    const ATTR_SUBJECT_NAME = 'SubjectName';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_SUBJECT_GROUP = 'SubjectGroup';
    const ATTR_COURSE_NUMBER = 'CourseNumber';
    const ATTR_IS_INTENSIVE_COURSE = 'IsIntensiveCourse';
    const ATTR_IS_IGNORE_COURSE = 'IsIgnoreCourse';
    const ATTR_TBL_INDIWARE_IMPORT_STUDENT = 'tblIndiwareImportStudent';

    /**
     * @Column(type="string")
     */
    protected $SubjectName;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;
    /**
     * @Column(type="string")
     */
    protected $SubjectGroup;
    /**
     * @Column(type="integer")
     */
    protected $CourseNumber;
    /**
     * @Column(type="boolean")
     */
    protected $IsIntensiveCourse;
    /**
     * @Column(type="boolean")
     */
    protected $IsIgnoreCourse;
    /**
     * @Column(type="bigint")
     */
    protected $tblIndiwareImportStudent;

    /**
     * @return string
     */
    public function getSubjectName()
    {

        return $this->SubjectName;
    }

    /**
     * @param string $SubjectName
     */
    public function setSubjectName($SubjectName)
    {

        $this->SubjectName = $SubjectName;
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

        $this->serviceTblSubject = (null === $tblSubject ? null : $tblSubject->getId());
    }

    /**
     * @return string
     */
    public function getSubjectGroup()
    {

        return $this->SubjectGroup;
    }

    /**
     * @param string $SubjectGroup
     */
    public function setSubjectGroup($SubjectGroup = '')
    {

        $this->SubjectGroup = $SubjectGroup;
    }

    /**
     * @return integer
     */
    public function getCourseNumber()
    {

        return $this->CourseNumber;
    }

    /**
     * @param integer $CourseNumber
     */
    public function setCourseNumber($CourseNumber)
    {

        $this->CourseNumber = $CourseNumber;
    }

    /**
     * @return bool
     */
    public function getIsIntensiveCourse()
    {

        return $this->IsIntensiveCourse;
    }

    /**
     * @param bool $IsIntensiveCourse
     */
    public function setIsIntensiveCourse($IsIntensiveCourse)
    {

        $this->IsIntensiveCourse = $IsIntensiveCourse;
    }

    /**
     * @return bool
     */
    public function getisIgnoreCourse()
    {
        return $this->IsIgnoreCourse;
    }

    /**
     * @param bool $IsIgnoreCourse
     */
    public function setIsIgnoreCourse($IsIgnoreCourse)
    {
        $this->IsIgnoreCourse = $IsIgnoreCourse;
    }

    /**
     * @return bool|TblIndiwareImportStudent
     */
    public function getTblIndiwareImportStudent()
    {
        if (null === $this->tblIndiwareImportStudent) {
            return false;
        } else {
            return Import::useService()->getIndiwareImportStudentById($this->tblIndiwareImportStudent);
        }
    }

    /**
     * @param TblIndiwareImportStudent $tblIndiwareImportStudent
     */
    public function settblIndiwareImportStudent($tblIndiwareImportStudent)
    {

        $this->tblIndiwareImportStudent = $tblIndiwareImportStudent->getId();
    }
}