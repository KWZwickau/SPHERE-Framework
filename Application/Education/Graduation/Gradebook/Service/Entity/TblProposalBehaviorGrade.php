<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 26.02.2019
 * Time: 10:50
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblProposalBehaviorGrade")
 * @Cache(usage="READ_ONLY")
 */
class TblProposalBehaviorGrade extends Element
{

    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_SERVICE_TBL_TASK = 'serviceTblTask';
    const ATTR_TBL_GRADE_TYPE = 'tblGradeType';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_PERSON_TEACHER = 'serviceTblPersonTeacher';

    const VALUE_TREND_NULL = 0;
    const VALUE_TREND_PLUS = 1;
    const VALUE_TREND_MINUS = 2;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblTask;

    /**
     * @Column(type="bigint")
     */
    protected $tblGradeType;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="string")
     */
    protected $Grade;

    /**
     * @Column(type="smallint")
     */
    protected $Trend;

    /**
     * @Column(type="string")
     */
    protected $Comment;

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
     * @return bool|TblTask
     */
    public function getServiceTblTask()
    {

        if (null === $this->serviceTblTask) {
            return false;
        } else {
            return Evaluation::useService()->getTaskById($this->serviceTblTask);
        }
    }

    /**
     * @param TblTask|null $serviceTblTask
     */
    public function setServiceTblTask($serviceTblTask)
    {

        $this->serviceTblTask = ( null === $serviceTblTask ? null : $serviceTblTask->getId() );
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
}