<?php

namespace SPHERE\Application\Transfer\Education\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Transfer\Education\Education;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblImportLectureship")
 * @Cache(usage="READ_ONLY")
 */
class TblImportLectureship extends Element
{
    const ATTR_TBL_IMPORT = 'tblImport';

    /**
     * @Column(type="bigint")
     */
    protected int $tblImport;
    /**
     * @Column(type="string")
     */
    protected string $TeacherAcronym;
    /**
     * @Column(type="string")
     */
    protected string $DivisionName;
    /**
     * @Column(type="string")
     */
    protected string $SubjectAcronym;
    /**
     * @Column(type="string")
     */
    protected string $SubjectGroup;

    public function __construct(TblImport $tblImport, string $TeacherAcronym, string $DivisionName, string $SubjectAcronym, string $SubjectGroup)
    {
        $this->tblImport = $tblImport->getId();
        $this->TeacherAcronym = $TeacherAcronym;
        $this->DivisionName = $DivisionName;
        $this->SubjectAcronym = $SubjectAcronym;
        $this->SubjectGroup = $SubjectGroup;
    }

    /**
     * @return TblImport|false
     */
    public function getTblImport()
    {
        return Education::useService()->getImportById($this->tblImport);
    }

    /**
     * @param TblImport $tblImport
     */
    public function setTblImport(TblImport $tblImport): void
    {
        $this->tblImport = $tblImport->getId();
    }

    /**
     * @return string
     */
    public function getSubjectAcronym(): string
    {
        return $this->SubjectAcronym;
    }

    /**
     * @param string $SubjectAcronym
     */
    public function setSubjectAcronym(string $SubjectAcronym): void
    {
        $this->SubjectAcronym = $SubjectAcronym;
    }

    /**
     * @return string
     */
    public function getTeacherAcronym(): string
    {
        return $this->TeacherAcronym;
    }

    /**
     * @param string $TeacherAcronym
     */
    public function setTeacherAcronym(string $TeacherAcronym): void
    {
        $this->TeacherAcronym = $TeacherAcronym;
    }

    /**
     * @return string
     */
    public function getDivisionName(): string
    {
        return $this->DivisionName;
    }

    /**
     * @param string $DivisionName
     */
    public function setDivisionName(string $DivisionName): void
    {
        $this->DivisionName = $DivisionName;
    }

    /**
     * @return string
     */
    public function getSubjectGroup(): string
    {
        return $this->SubjectGroup;
    }

    /**
     * @param string $SubjectGroup
     */
    public function setSubjectGroup(string $SubjectGroup): void
    {
        $this->SubjectGroup = $SubjectGroup;
    }
}