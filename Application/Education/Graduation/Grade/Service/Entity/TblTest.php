<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

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
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     * @param TblGradeType $tblGradeType
     * @param DateTime|null $Date
     * @param DateTime|null $FinishDate
     * @param DateTime|null $CorrectionDate
     * @param DateTime|null $ReturnDate
     * @param bool $IsContinues
     * @param string $Description
     * @param int|null $Id
     */
    public function __construct(
        TblYear $tblYear, TblSubject $tblSubject, TblGradeType $tblGradeType,
        ?DateTime $Date, ?DateTime $FinishDate, ?DateTime $CorrectionDate, ?DateTime $ReturnDate, bool $IsContinues, string $Description, ?int $Id = null
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

}