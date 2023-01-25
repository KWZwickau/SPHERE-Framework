<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service;

use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblSubjectTable;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;

class VirtualSubject
{
    private ?TblSubject $tblSubject;

    private bool $HasGrading;

    private ?TblSubjectTable $tblSubjectTable;

    private ?bool $IsAdvancedCourse;

    /**
     * @param TblSubject|null $tblSubject
     * @param bool $hasGrading
     * @param TblSubjectTable|null $tblSubjectTable
     * @param bool|null $IsAdvancedCourse
     */
    public function __construct(?TblSubject $tblSubject, bool $hasGrading, ?TblSubjectTable $tblSubjectTable, ?bool $IsAdvancedCourse = null)
    {
        $this->setTblSubject($tblSubject);
        $this->setHasGrading($hasGrading);
        $this->setTblSubjectTable($tblSubjectTable);
        $this->setIsAdvancedCourse($IsAdvancedCourse);
    }

    /**
     * @return TblSubject|null
     */
    public function getTblSubject(): ?TblSubject
    {
        return $this->tblSubject;
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setTblSubject(?TblSubject $tblSubject): void
    {
        $this->tblSubject = $tblSubject;
    }

    /**
     * @return bool
     */
    public function getHasGrading(): bool
    {
        return $this->HasGrading;
    }

    /**
     * @param bool $HasGrading
     */
    public function setHasGrading(bool $HasGrading): void
    {
        $this->HasGrading = $HasGrading;
    }

    /**
     * @return TblSubjectTable|null
     */
    public function getTblSubjectTable(): ?TblSubjectTable
    {
        return $this->tblSubjectTable;
    }

    /**
     * @param TblSubjectTable|null $tblSubjectTable
     */
    public function setTblSubjectTable(?TblSubjectTable $tblSubjectTable): void
    {
        $this->tblSubjectTable = $tblSubjectTable;
    }

    /**
     * @return bool|null
     */
    public function getIsAdvancedCourse(): ?bool
    {
        return $this->IsAdvancedCourse;
    }

    /**
     * @param bool|null $IsAdvancedCourse
     */
    public function setIsAdvancedCourse(?bool $IsAdvancedCourse): void
    {
        $this->IsAdvancedCourse = $IsAdvancedCourse;
    }
}