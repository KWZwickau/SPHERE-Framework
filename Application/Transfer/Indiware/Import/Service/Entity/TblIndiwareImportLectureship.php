<?php

namespace SPHERE\Application\Transfer\Indiware\Import\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Teacher\Service\Entity\TblTeacher;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblIndiwareImportLectureship")
 * @Cache(usage="READ_ONLY")
 */
class TblIndiwareImportLectureship extends Element
{

    const ATTR_SERVICE_TBL_YEAR = 'serviceTblYear';

    const ATTR_SCHOOL_CLASS = 'SchoolClass';
    const ATTR_TEACHER_ACRONYM = 'TeacherAcronym';
    const ATTR_SUBJECT_NAME = 'SubjectName';
    const ATTR_SUBJECT_GROUP_NAME = 'SubjectGroupName';

    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_SERVICE_TBL_TEACHER = 'serviceTblTeacher';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_SUBJECT_GROUP = 'SubjectGroup';
    const ATTR_SERVICE_TBL_ACCOUNT = 'serviceTblAccount';

    const ATTR_IS_IGNORE = 'IsIgnore';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblYear;
    /**
     * @Column(type="string")
     */
    protected $SchoolClass;
    /**
     * @Column(type="string")
     */
    protected $TeacherAcronym;
    /**
     * @Column(type="string")
     */
    protected $SubjectName;
    /**
     * @Column(type="string")
     */
    protected $SubjectGroupName;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblTeacher;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;
    /**
     * @Column(type="string")
     */
    protected $SubjectGroup;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAccount;
    /**
     * @Column(type="bigint")
     */
    protected $IsIgnore;

    /**
     * @return bool|TblYear
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

        $this->serviceTblYear = (null === $tblYear ? null : $tblYear->getId());
    }

    /**
     * @return string
     */
    public function getSchoolClass()
    {

        return $this->SchoolClass;
    }

    /**
     * @param string $SchoolClass
     */
    public function setSchoolClass($SchoolClass)
    {

        $this->SchoolClass = $SchoolClass;
    }

    /**
     * @return string
     */
    public function getTeacherAcronym()
    {

        return $this->TeacherAcronym;
    }

    /**
     * @param string $TeacherAcronym
     */
    public function setTeacherAcronym($TeacherAcronym)
    {

        $this->TeacherAcronym = $TeacherAcronym;
    }

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
     * @return string
     */
    public function getSubjectGroupName()
    {

        return $this->SubjectGroupName;
    }

    /**
     * @param string $SubjectGroupName
     */
    public function setSubjectGroupName($SubjectGroupName)
    {

        $this->SubjectGroupName = $SubjectGroupName;
    }

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
     * @return bool|TblTeacher
     */
    public function getServiceTblTeacher()
    {

        if (null === $this->serviceTblTeacher) {
            return false;
        } else {
            return Teacher::useService()->getTeacherById($this->serviceTblTeacher);
        }
    }

    /**
     * @param TblTeacher|null $tblTeacher
     */
    public function setServiceTblTeacher(TblTeacher $tblTeacher = null)
    {

        $this->serviceTblTeacher = (null === $tblTeacher ? null : $tblTeacher->getId());
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
     * @return bool|TblAccount
     */
    public function getServiceTblAccount()
    {

        if (null === $this->serviceTblAccount) {
            return false;
        } else {
            return Account::useService()->getAccountById($this->serviceTblAccount);
        }
    }

    /**
     * @param TblAccount|null $tblAccount
     */
    public function setServiceTblAccount(TblAccount $tblAccount = null)
    {

        $this->serviceTblAccount = (null === $tblAccount ? null : $tblAccount->getId());
    }

    /**
     * @return bool
     */
    public function getIsIgnore()
    {

        return $this->IsIgnore;
    }

    /**
     * @param bool $IsIgnore
     */
    public function setIsIgnore($IsIgnore)
    {

        $this->IsIgnore = $IsIgnore;
    }
}
