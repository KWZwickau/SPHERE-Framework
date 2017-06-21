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
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblIndiwareImportStudentCourse")
 * @Cache(usage="READ_ONLY")
 */
class TblIndiwareImportStudentCourse extends Element
{

    const ATTR_SERVICE_TBL_YEAR = 'serviceTblYear';

//    const ATTR_FIRST_NAME = 'FirstName';
//    const ATTR_LAST_NAME = 'LastName';
//    const ATTR_BIRTHDAY = 'Birthday';
    const ATTR_SUBJECT_NAME = 'SubjectName';
    const ATTR_SUBJECT_GROUP = 'SubjectGroup';
    const ATTR_COURSE_NUMBER = 'CourseNumber';
    const ATTR_IS_INTENSIVE_COURSE = 'IsIntensiveCourse';

    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_SERVICE_TBL_ACCOUNT = 'serviceTblAccount';

    const ATTR_IS_IGNORE = 'IsIgnore';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblYear;
//    /**
//     * @Column(type="string")
//     */
//    protected $FirstName;
//    /**
//     * @Column(type="string")
//     */
//    protected $LastName;
//    /**
//     * @Column(type="datetime")
//     */
//    protected $Birthday;
    /**
     * @Column(type="string")
     */
    protected $SubjectName;
    /**
     * @Column(type="integer")
     */
    protected $CourseNumber;
    /**
     * @Column(type="boolean")
     */
    protected $IsIntensiveCourse;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;
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
     * @Column(type="boolean")
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

//    /**
//     * @return string
//     */
//    public function getFirstName()
//    {
//
//        return $this->FirstName;
//    }
//
//    /**
//     * @param string $FirstName
//     */
//    public function setFirstName($FirstName)
//    {
//
//        $this->FirstName = $FirstName;
//    }
//
//    /**
//     * @return string
//     */
//    public function getLastName()
//    {
//
//        return $this->LastName;
//    }
//
//    /**
//     * @param string $LastName
//     */
//    public function setLastName($LastName)
//    {
//
//        $this->LastName = $LastName;
//    }
//
//    /**
//     * @return string
//     */
//    public function getBirthday()
//    {
//
//        if (null === $this->Birthday) {
//            return false;
//        }
//        /** @var \DateTime $Birthday */
//        $Birthday = $this->Birthday;
//        if ($Birthday instanceof \DateTime) {
//            return $Birthday->format('d.m.Y');
//        } else {
//            return (string)$Birthday;
//        }
//    }

    /**
     * @param null|\DateTime $Birthday
     */
    public function setBirthday(\DateTime $Birthday = null)
    {

        $this->Birthday = $Birthday;
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
