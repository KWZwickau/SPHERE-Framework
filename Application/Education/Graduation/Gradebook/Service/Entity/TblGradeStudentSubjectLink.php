<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 03.11.2015
 * Time: 14:35
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGradeStudentSubjectLink")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblGradeStudentSubjectLink extends Element
{

    const ATTR_TBL_GRADE_TYPE = 'tblGradeType';
    const ATTR_TBL_TEST = 'tblTest';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_SERVICE_TBL_PERIOD = 'serviceTblPeriod';
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';

    /**
     * @Column(type="string")
     */
    protected $Grade;

    /**
     * @Column(type="string")
     */
    protected $Comment;

    /**
     * @Column(type="bigint")
     */
    protected $tblGradeType;

    /**
     * @Column(type="bigint")
     */
    protected $tblTest;

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
    protected $serviceTblPeriod;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @return string
     */
    public function getGrade()
    {
        return $this->Grade;
    }

    /**
     * @param string $Grade
     */
    public function setGrade($Grade)
    {
        $this->Grade = $Grade;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->Comment;
    }

    /**
     * @param string $Comment
     */
    public function setComment($Comment)
    {
        $this->Comment = $Comment;
    }

    /**
     * @return bool|TblGradeType
     */
    public function getTblGradeType()
    {
        if (null === $this->tblGradeType) {
            return false;
        } else {
            return Gradebook::useService()->getGradeTypeById($this->tblGradeType);
        }
    }

    /**
     * @param TblGradeType|null $tblGradeType
     */
    public function setTblGradeType($tblGradeType)
    {
        $this->tblGradeType = (null === $tblGradeType ? null : $tblGradeType->getId());
    }

    /**
     * @return bool|TblTest
     */
    public function getTblTest()
    {
        if (null === $this->tblTest) {
            return false;
        } else {
            return Gradebook::useService()->getTestById($this->tblTest);
        }
    }

    /**
     * @param TblTest|null $tblTest
     */
    public function setTblTest($tblTest)
    {
        var_dump($tblTest->getId());
        $this->tblTest = (null === $tblTest ? null : $tblTest->getId());
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

        $this->serviceTblSubject = (null === $tblSubject ? null : $tblSubject->getId());
    }

    /**
     * @return bool|TblPeriod
     */
    public function getServiceTblPeriod()
    {

        if (null === $this->serviceTblPeriod) {
            return false;
        } else {
            return Term::useService()->getPeriodById($this->serviceTblPeriod);
        }
    }

    /**
     * @param TblPeriod|null $tblPeriod
     */
    public function setServiceTblPeriod(TblPeriod $tblPeriod = null)
    {

        $this->serviceTblPeriod = (null === $tblPeriod ? null : $tblPeriod->getId());
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
}