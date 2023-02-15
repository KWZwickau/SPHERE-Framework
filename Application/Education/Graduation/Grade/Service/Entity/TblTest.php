<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use DateInterval;
use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Meta\Teacher\Teacher;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGraduationTest")
 * @Cache(usage="READ_ONLY")
 */
class TblTest extends Element
{
    const ATTR_TBL_GRADE_TYPE = 'tblGraduationGradeType';

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblYear;
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblSubject;
    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationGradeType;
    /**
     * @Column(type="datetime")
     */
    protected ?DateTime $Date = null;
    /**
     * @Column(type="datetime")
     */
    protected ?DateTime $FinishDate = null;
    /**
     * @Column(type="datetime")
     */
    protected ?DateTime $CorrectionDate = null;
    /**
     * @Column(type="datetime")
     */
    protected ?DateTime $ReturnDate = null;
    /**
     * @Column(type="boolean")
     */
    protected bool $IsContinues = false;
    /**
     * @Column(type="string")
     */
    protected string $Description = '';
    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblPersonTeacher = null;

    /**
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     * @param TblGradeType $tblGradeType
     * @param DateTime|null $Date
     * @param DateTime|null $FinishDate
     * @param DateTime|null $CorrectionDate
     * @param DateTime|null $ReturnDate
     * @param bool $IsContinues
     * @param string $Description
     * @param TblPerson|null $tblTeacher
     * @param int|null $Id
     */
    public function __construct(
        TblYear $tblYear, TblSubject $tblSubject, TblGradeType $tblGradeType,
        ?DateTime $Date, ?DateTime $FinishDate, ?DateTime $CorrectionDate, ?DateTime $ReturnDate, bool $IsContinues, string $Description,
        ?TblPerson $tblTeacher, ?int $Id = null
    ) {
        $this->serviceTblYear = $tblYear->getId();
        $this->serviceTblSubject = $tblSubject->getId();
        $this->tblGraduationGradeType = $tblGradeType->getId();
        $this->Date = $Date;
        $this->FinishDate = $FinishDate;
        $this->CorrectionDate = $CorrectionDate;
        $this->ReturnDate = $ReturnDate;
        $this->IsContinues = $IsContinues;
        $this->Description = $Description;
        $this->serviceTblPersonTeacher = $tblTeacher ? $tblTeacher->getId() : null;
        if ($Id) {
            $this->Id = $Id;
        }
    }

    /**
     * @return false|TblYear
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
        $this->serviceTblYear = $tblYear->getId();
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
     * @return TblGradeType
     */
    public function getTblGradeType(): TblGradeType
    {
        return Grade::useService()->getGradeTypeById($this->tblGraduationGradeType);
    }

    /**
     * @param TblGradeType $tblGradeType
     */
    public function setTblGradeType(TblGradeType $tblGradeType)
    {
        $this->tblGraduationGradeType = $tblGradeType->getId();
    }

    /**
     * @return DateTime|null
     */
    public function getDate(): ?DateTime
    {
        return $this->Date;
    }

    /**
     * @return string
     */
    public function getDateString(): string
    {
        return $this->Date instanceof DateTime ? $this->Date->format('d.m.Y') : '';
    }

    /**
     * @param DateTime|null $Date
     */
    public function setDate(?DateTime $Date): void
    {
        $this->Date = $Date;
    }

    /**
     * @return DateTime|null
     */
    public function getFinishDate(): ?DateTime
    {
        return $this->FinishDate;
    }

    /**
     * @return string
     */
    public function getFinishDateString(): string
    {
        return $this->FinishDate instanceof DateTime ? $this->FinishDate->format('d.m.Y') : '';
    }

    /**
     * @param DateTime|null $FinishDate
     */
    public function setFinishDate(?DateTime $FinishDate): void
    {
        $this->FinishDate = $FinishDate;
    }

    /**
     * @return DateTime|null
     */
    public function getCorrectionDate(): ?DateTime
    {
        return $this->CorrectionDate;
    }

    /**
     * @return string
     */
    public function getCorrectionDateString(): string
    {
        return $this->CorrectionDate instanceof DateTime ? $this->CorrectionDate->format('d.m.Y') : '';
    }

    /**
     * @param DateTime|null $CorrectionDate
     */
    public function setCorrectionDate(?DateTime $CorrectionDate)
    {
        $this->CorrectionDate = $CorrectionDate;
    }

    /**
     * @return DateTime|null
     */
    public function getReturnDate(): ?DateTime
    {
        return $this->ReturnDate;
    }

    /**
     * @return string
     */
    public function getReturnDateString(): string
    {
        return $this->ReturnDate instanceof DateTime ? $this->ReturnDate->format('d.m.Y') : '';
    }

    /**
     * @param DateTime|null $ReturnDate
     */
    public function setReturnDate(?DateTime $ReturnDate)
    {
        $this->ReturnDate = $ReturnDate;
    }

    /**
     * @return bool
     */
    public function getIsContinues(): bool
    {
        return $this->IsContinues;
    }

    /**
     * @param bool $IsContinues
     */
    public function setIsContinues(bool $IsContinues)
    {
        $this->IsContinues = $IsContinues;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription(string $Description)
    {
        $this->Description = $Description;
    }

    /**
     * @return DateTime|null
     */
    public function getSortDate(): ?DateTime
    {
        return $this->getIsContinues()
            ? $this->getFinishDate()
            : $this->getDate();
    }

    /**
     * @return false|TblDivisionCourse[]
     */
    public function getDivisionCourses()
    {
        return Grade::useService()->getDivisionCourseListByTest($this);
    }

    /**
     * @return false|TblTestGrade[]
     */
    public function getGrades()
    {
        return Grade::useService()->getTestGradeListByTest($this);
    }

    /**
     * @return string
     */
    public function getGradeTypeDisplayName(): string
    {
        if (($tblGradeType = $this->getTblGradeType())) {
            return $tblGradeType->getDisplayName();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getYearName(): string
    {
        if (($tblYear = $this->getServiceTblYear())) {
            return $tblYear->getName();
        }

        return '&nbsp;';
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
     * @param TblTestGrade|null $tblTestGrade
     * @param DateTime|null $taskDate
     * @param int $AutoPublicationOfTestsAfterXDays
     *
     * @return bool
     */
    public function getIsShownInParentView(?TblTestGrade $tblTestGrade, ?DateTime $taskDate, int $AutoPublicationOfTestsAfterXDays): bool
    {
        $isAddTest = false;
        $today = (new DateTime('today'));
        // fortlaufendes Datum
        if ($this->getIsContinues()) {
            if ($tblTestGrade && $tblTestGrade->getDate()) {
                if ($tblTestGrade->getDate() <= $today) {
                    $isAddTest = true;
                }
            } elseif ($this->getFinishDate()) {
                // continues grades without date can be view if finish date is arrived
                if ($this->getFinishDate() <= $today) {
                    $isAddTest = true;
                }
            }
        // Bekanntgabedatum gesetzt
        } elseif ($this->getReturnDate()) {
            if ($this->getReturnDate() <= $today) {
                $isAddTest = true;
            }
        // automatische Bekanntgabe durch den Stichtagsnotenauftrag
        } elseif ($taskDate) {
            if ($taskDate <= $today
                && $this->getDate()
                && $this->getDate() <= $taskDate
            ) {
                $isAddTest = true;
            }
        }

        // automatische Bekanntgabe nach X Tagen
        if (!$isAddTest && $this->getDate()) {
            $autoReturnDate = (new DateTime($this->getDateString()))->add(new DateInterval('P' . $AutoPublicationOfTestsAfterXDays . 'D'));
            if ($autoReturnDate <= $today) {
                $isAddTest = true;
            }
        }

        return $isAddTest;
    }
}