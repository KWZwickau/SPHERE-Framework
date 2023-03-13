<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificate;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGraduationTaskGrade")
 * @Cache(usage="READ_ONLY")
 */
class TblTaskGrade extends Element
{
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_TBL_TASK = 'tblGraduationTask';
    const ATTR_TBL_GRADE_TYPE = 'tblGraduationGradeType';

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblSubject;
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
     * @Column(type="bigint")
     */
    protected ?int $tblGraduationGradeText = null;
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
     * @param TblSubject $tblSubject
     * @param TblTask $tblTask
     * @param TblGradeType|null $tblGradeType
     * @param string|null $Grade
     * @param TblGradeText|null $tblGradeText
     * @param string|null $Comment
     * @param TblPerson|null $tblTeacher
     */
    public function __construct(
        TblPerson $tblPerson, TblSubject $tblSubject, TblTask $tblTask, ?TblGradeType $tblGradeType,
        ?string $Grade, ?TblGradeText $tblGradeText, ?string $Comment, ?TblPerson $tblTeacher
    ) {
        $this->serviceTblPerson = $tblPerson->getId();
        $this->serviceTblSubject = $tblSubject->getId();
        $this->tblGraduationTask = $tblTask->getId();
        $this->tblGraduationGradeType = $tblGradeType ? $tblGradeType->getId() : null;
        $this->Grade = $Grade;
        $this->tblGraduationGradeText = $tblGradeText ? $tblGradeText->getId() : null;
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
     * @return TblGradeText|false
     */
    public function getTblGradeText()
    {
        return Grade::useService()->getGradeTextById($this->tblGraduationGradeText);
    }

    /**
     * @param ?TblGradeText $tblGradeText
     */
    public function setTblGradeText(?TblGradeText $tblGradeText)
    {
        $this->tblGraduationGradeText = $tblGradeText ? $tblGradeText->getId() : null;
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
     * bei Zeugnissen werden die Tendenzen entfernt
     *
     * @param bool $isGradeTextShortName
     * @param TblCertificate|null $tblCertificate
     *
     * @return string
     */
    public function getDisplayGrade(bool $isGradeTextShortName = true, ?TblCertificate $tblCertificate = null): string
    {
        if (($tblGradeText = $this->getTblGradeText())) {
            return $isGradeTextShortName ? $tblGradeText->getShortName() : $tblGradeText->getName();
        }

        $grade = $this->getGrade() ?: '';
        if ($tblCertificate && !$tblCertificate->isInformation()) {
            $grade = str_replace('+', '', $grade);
            $grade = str_replace('-', '', $grade);
        }

        return $grade;
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
}