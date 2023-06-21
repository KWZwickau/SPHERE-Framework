<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGraduationProposalBehaviorGrade")
 * @Cache(usage="READ_ONLY")
 */
class TblProposalBehaviorGrade extends Element
{
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_TBL_TASK = 'tblGraduationTask';
    const ATTR_TBL_GRADE_TYPE = 'tblGraduationGradeType';

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationTask;
    /**
     * @Column(type="bigint")
     */
    protected ?int $tblGraduationGradeType = null;
    /**
     * @Column(type="string")
     */
    protected ?string $Grade = null;
    /**
     * @Column(type="string")
     */
    protected ?string $Comment = null;
    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblPersonTeacher = null;

    /**
     * @param TblPerson $tblPerson
     * @param TblTask $tblTask
     * @param TblGradeType|null $tblGradeType
     * @param string|null $Grade
     * @param string|null $Comment
     * @param TblPerson|null $tblTeacher
     */
    public function __construct(
        TblPerson $tblPerson, TblTask $tblTask, ?TblGradeType $tblGradeType, ?string $Grade, ?string $Comment, ?TblPerson $tblTeacher
    ) {
        $this->serviceTblPerson = $tblPerson->getId();
        $this->tblGraduationTask = $tblTask->getId();
        $this->tblGraduationGradeType = $tblGradeType ? $tblGradeType->getId() : null;
        $this->Grade = $Grade;
        $this->Comment = $Comment;
        $this->serviceTblPersonTeacher = $tblTeacher ? $tblTeacher->getId() : null;
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
     * @return TblTask
     */
    public function getTblTask(): TblTask
    {
        return Grade::useService()->getTaskById($this->tblGraduationTask);
    }

    /**
     * @return TblGradeType|false
     */
    public function getTblGradeType()
    {
        return Grade::useService()->getGradeTypeById($this->tblGraduationGradeType);
    }

    /**
     * @param ?TblGradeType $tblGradeType
     */
    public function setTblGradeType(?TblGradeType $tblGradeType)
    {
        $this->tblGraduationGradeType = $tblGradeType ? $tblGradeType->getId() : null;
    }

    /**
     * @return string|null
     */
    public function getGrade(): ?string
    {
        return $this->Grade;
    }

    /**
     * @param string|null $Grade
     */
    public function setGrade(?string $Grade): void
    {
        $this->Grade = $Grade;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->Comment;
    }

    /**
     * @param string|null $Comment
     */
    public function setComment(?string $Comment): void
    {
        $this->Comment = $Comment;
    }

    /**
     * @param bool $IsForce
     *
     * @return false|TblPerson
     */
    public function getServiceTblPersonTeacher(bool $IsForce = false)
    {
        return Person::useService()->getPersonById($this->serviceTblPersonTeacher, $IsForce);
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function setServiceTblPersonTeacher(TblPerson $tblPerson)
    {
        $this->serviceTblPersonTeacher = $tblPerson->getId();
    }

    /**
     * @return string
     */
    public function getDisplayTeacher(): string
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
     * @return float|null
     */
    public function getGradeNumberValue(): ?float
    {
        return Grade::useService()->getGradeNumberValue($this->getGrade());
    }

    /**
     * @return bool
     */
    public function getIsGradeNumeric(): bool
    {
        return $this->getGradeNumberValue() !== null;
    }

    /**
     * @return string
     */
    public function getGradeTypeName(): string
    {
        if (($tblGradeType = $this->getTblGradeType())) {
            return $tblGradeType->getName();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getGradeTypeCode(): string
    {
        if (($tblGradeType = $this->getTblGradeType())) {
            return $tblGradeType->getCode();
        }

        return '';
    }
}