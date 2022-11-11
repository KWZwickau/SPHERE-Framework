<?php
namespace SPHERE\Application\Education\Graduation\Gradebook\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTestType;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblSubjectGroup;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGrade")
 * @Cache(usage="READ_ONLY")
 */
class TblGrade extends Element
{

    const ATTR_TBL_GRADE_TYPE = 'tblGradeType';
    const ATTR_TBL_GRADE_TEXT = 'tblGradeText';
    const ATTR_SERVICE_TBL_TEST = 'serviceTblTest';
    const ATTR_SERVICE_TBL_TEST_TYPE = 'serviceTblTestType';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_PERSON_TEACHER = 'serviceTblPersonTeacher';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_SERVICE_TBL_SUBJECT_GROUP = 'serviceTblSubjectGroup';
    const ATTR_SERVICE_TBL_PERIOD = 'serviceTblPeriod';
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_DATE = 'Date';

    const VALUE_TREND_NULL = 0;
    const VALUE_TREND_PLUS = 1;
    const VALUE_TREND_MINUS = 2;

    /**
     * @Column(type="string")
     */
    protected $Grade;

    /**
     * @Column(type="string")
     */
    protected $Comment;

    /**
     * @Column(type="string")
     */
    protected $PublicComment;

    /**
     * @Column(type="smallint")
     */
    protected $Trend;

    /**
     * @Column(type="bigint")
     */
    protected $tblGradeType;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblTest;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblTestType;

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
    protected $serviceTblSubjectGroup;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPeriod;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="datetime")
     */
    protected $Date;

    /**
     * @Column(type="bigint")
     */
    protected $tblGradeText;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPersonTeacher;

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

        $this->tblGradeType = ( null === $tblGradeType ? null : $tblGradeType->getId() );
    }

    /**
     * @return bool|TblTest
     */
    public function getServiceTblTest()
    {

        if (null === $this->serviceTblTest) {
            return false;
        } else {
            return Evaluation::useService()->getTestById($this->serviceTblTest);
        }
    }

    /**
     * @param TblTest|null $serviceTblTest
     */
    public function setServiceTblTest($serviceTblTest)
    {

        $this->serviceTblTest = ( null === $serviceTblTest ? null : $serviceTblTest->getId() );
    }

    /**
     * @return bool|TblTestType
     */
    public function getServiceTblTestType()
    {

        if (null === $this->serviceTblTestType) {
            return false;
        } else {
            return Evaluation::useService()->getTestTypeById($this->serviceTblTestType);
        }
    }

    /**
     * @param TblTestType|null $serviceTblTestType
     */
    public function setServiceTblTestType($serviceTblTestType)
    {

        $this->serviceTblTestType = ( null === $serviceTblTestType ? null : $serviceTblTestType->getId() );
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

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
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
     * @return bool|TblSubjectGroup
     */
    public function getServiceTblSubjectGroup()
    {

        if (null === $this->serviceTblSubjectGroup) {
            return false;
        } else {
            return Division::useService()->getSubjectGroupById($this->serviceTblSubjectGroup);
        }
    }

    /**
     * @param TblSubjectGroup|null $tblSubjectGroup
     */
    public function setServiceTblSubjectGroup(TblSubjectGroup $tblSubjectGroup = null)
    {

        $this->serviceTblSubjectGroup = ( null === $tblSubjectGroup ? null : $tblSubjectGroup->getId() );
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

        $this->serviceTblPeriod = ( null === $tblPeriod ? null : $tblPeriod->getId() );
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

        $this->serviceTblDivision = ( null === $tblDivision ? null : $tblDivision->getId() );
    }

    /**
     * @param bool $WithTrend
     * 
     * @return string
     */
    public function getDisplayGrade($WithTrend = true)
    {

        if ($this->getTblGradeText()){
            return $this->getTblGradeText()->getName();
        }

        $gradeValue = $this->getGrade();
        if ($gradeValue !== null && $gradeValue !== '') {
            if ($WithTrend) {
                $trend = $this->getTrend();
                if (TblGrade::VALUE_TREND_PLUS === $trend) {
                    $gradeValue .= '+';
                } elseif (TblGrade::VALUE_TREND_MINUS === $trend) {
                    $gradeValue .= '-';
                }
            }

            return $gradeValue;
        }

        return '';
    }

    /**
     * null steht für nicht teilgenommen
     *
     * @return string|null
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
     * @return int
     */
    public function getTrend()
    {

        return $this->Trend;
    }

    /**
     * @param int $Trend
     */
    public function setTrend($Trend)
    {

        $this->Trend = $Trend;
    }

    /**
     * @return string
     */
    public function getDate()
    {

        if (null === $this->Date) {
            return false;
        }
        /** @var \DateTime $Date */
        $Date = $this->Date;
        if ($Date instanceof \DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    public function getDateTime()
    {
        return $this->Date;
    }

    /**
     * @param null|\DateTime $Date
     */
    public function setDate(\DateTime $Date = null)
    {

        $this->Date = $Date;
    }

    /**
     * @return bool|TblGradeText
     */
    public function getTblGradeText()
    {

        if (null === $this->tblGradeText) {
            return false;
        } else {
            return Gradebook::useService()->getGradeTextById($this->tblGradeText);
        }
    }

    /**
     * @param TblGradeText|null $tblGradeText
     */
    public function setTblGradeText($tblGradeText)
    {

        $this->tblGradeText = ( null === $tblGradeText ? null : $tblGradeText->getId() );
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPersonTeacher()
    {

        if (null === $this->serviceTblPersonTeacher) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPersonTeacher);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPersonTeacher(TblPerson $tblPerson = null)
    {

        $this->serviceTblPersonTeacher = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return string
     */
    public function getDisplayTeacher()
    {

        if (($tblPerson = $this->getServiceTblPersonTeacher())){
            if (($tblTeacher = Teacher::useService()->getTeacherByPerson($tblPerson))){
                if ($tblTeacher->getAcronym()) {
                    return $tblTeacher->getAcronym();
                }
            }

            return $tblPerson->getLastName();
        }

        return '';
    }

    /**
     * @return bool|\DateTime
     */
    public function getDateForSorter()
    {

        if (($tblTest = $this->getServiceTblTest())) {
            if ($tblTest->isContinues()) {
                if ($this->getDate()) {
                    $date = $this->getDate();
                } else {
                    $date = $tblTest->getFinishDate();
                }
            } else {
                $date = $tblTest->getDate();
            }

            return  new \DateTime($date);
        }

        return false;
    }

    /**
     * @return string
     */
    public function getPublicComment()
    {
        return $this->PublicComment;
    }

    /**
     * @param string $PublicComment
     */
    public function setPublicComment($PublicComment)
    {
        $this->PublicComment = $PublicComment;
    }
}
