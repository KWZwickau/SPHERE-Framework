<?php

namespace SPHERE\Application\Transfer\Education\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Transfer\Education\Education;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblImportStudentCourse")
 * @Cache(usage="READ_ONLY")
 */
class TblImportStudentCourse extends Element
{
    const ATTR_TBL_IMPORT_STUDENT = 'tblImportStudent';

    /**
     * @Column(type="bigint")
     */
    protected int $tblImportStudent;
    /**
     * @Column(type="string")
     */
    protected string $SubjectAcronym;
    /**
     * @Column(type="string")
     */
    protected string $CourseNumber;
    /**
     * @Column(type="string")
     */
    protected string $CourseName;

    public function __construct(TblImportStudent $tblImportStudent, string $SubjectAcronym, string $CourseNumber, string $CourseName)
    {
        $this->tblImportStudent = $tblImportStudent->getId();
        $this->SubjectAcronym = $SubjectAcronym;
        $this->CourseNumber = $CourseNumber;
        $this->CourseName = $CourseName;
    }

    /**
     * @return TblImportStudent|false
     */
    public function getTblImportStudent()
    {
        return Education::useService()->getImportStudentById($this->tblImportStudent);
    }

    /**
     * @param TblImportStudent $tblImportStudent
     */
    public function setTblImportStudent(TblImportStudent $tblImportStudent): void
    {
        $this->tblImportStudent = $tblImportStudent->getId();
    }

    /**
     * @return string
     */
    public function getSubjectAcronym(): string
    {
        return $this->SubjectAcronym;
    }

    /**
     * @param string $SubjectAcronym
     */
    public function setSubjectAcronym(string $SubjectAcronym): void
    {
        $this->SubjectAcronym = $SubjectAcronym;
    }

    /**
     * @return string
     */
    public function getCourseNumber(): string
    {
        return $this->CourseNumber;
    }

    /**
     * @param string $CourseNumber
     */
    public function setCourseNumber(string $CourseNumber): void
    {
        $this->CourseNumber = $CourseNumber;
    }

    /**
     * @return string
     */
    public function getCourseName(): string
    {
        return $this->CourseName;
    }

    /**
     * @param string $CourseName
     */
    public function setCourseName(string $CourseName): void
    {
        $this->CourseName = $CourseName;
    }
}