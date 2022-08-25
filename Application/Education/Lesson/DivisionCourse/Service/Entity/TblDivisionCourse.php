<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblLessonDivisionCourse")
 * @Cache(usage="READ_ONLY")
 */
class TblDivisionCourse extends Element
{
    const ATTR_TBL_TYPE = 'tblLessonDivisionCourseType';
    const SERVICE_TBL_YEAR = 'serviceTblYear';

    /**
     * @Column(type="bigint")
     */
    protected int $tblLessonDivisionCourseType;

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblYear;

    /**
     * @Column(type="string")
     */
    protected string $Name = '';

    /**
     * @Column(type="string")
     */
    protected string $Description = '';

    /**
     * @Column(type="boolean")
     */
    protected bool $IsShownInPersonData = false;

    /**
     * @Column(type="boolean")
     */
    protected bool $IsReporting = false;

    /**
     * @Column(type="boolean")
     */
    protected bool $IsUcs = false;

    /**
     * @param TblDivisionCourseType $tblType
     * @param TblYear $tblYear
     * @param string $name
     * @param string $description
     * @param bool $isShownInPersonData
     * @param bool $isReporting
     * @param bool $isUcs
     *
     * @return TblDivisionCourse
     */
    public static function withParameter(TblDivisionCourseType $tblType, TblYear $tblYear, string $name, string $description,
        bool $isShownInPersonData = false, bool $isReporting = false, bool $isUcs = false): TblDivisionCourse
    {
        // php erlaubt leider keine mehrfach Konstruktoren :(
        $instance = new self();

        $instance->tblLessonDivisionCourseType = $tblType->getId();
        $instance->serviceTblYear = $tblYear->getId();
        $instance->Name = $name;
        $instance->Description = $description;
        $instance->IsShownInPersonData = $isShownInPersonData;
        $instance->IsReporting = $isReporting;
        $instance->IsUcs = $isUcs;

        return  $instance;
    }

    /**
     * @param TblDivisionCourseType $tblType
     * @param TblYear $tblYear
     * @param string $Name
     * @param string $Description
     * @param int $Id
     * @param bool $isShownInPersonData
     * @param bool $isReporting
     * @param bool $isUcs
     *
     * @return TblDivisionCourse
     */
    public static function withParameterAndId(TblDivisionCourseType $tblType, TblYear $tblYear, string $Name, string $Description, int $Id,
        bool $isShownInPersonData = false, bool $isReporting = false, bool $isUcs = false): TblDivisionCourse
    {
        $instance = self::withParameter($tblType, $tblYear, $Name, $Description, $isShownInPersonData, $isReporting, $isUcs);
        $instance->Id = $Id;

        return  $instance;
    }



    /**
     * @return false|TblDivisionCourseType
     */
    public function getType()
    {
        return DivisionCourse::useService()->getDivisionCourseTypeById($this->tblLessonDivisionCourseType);
    }

    /**
     * @param TblDivisionCourseType $tblType
     */
    public function setType(TblDivisionCourseType $tblType)
    {
        $this->tblLessonDivisionCourseType = $tblType->getId();
    }

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
        $this->serviceTblYear = $tblYear->getId();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName(string $Name): void
    {
        $this->Name = $Name;
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
    public function setDescription(string $Description): void
    {
        $this->Description = $Description;
    }

    /**
     * @return bool
     */
    public function getIsShownInPersonData(): bool
    {
        return $this->IsShownInPersonData;
    }

    /**
     * @param bool $IsShownInPersonData
     */
    public function setIsShownInPersonData(bool $IsShownInPersonData): void
    {
        $this->IsShownInPersonData = $IsShownInPersonData;
    }

    /**
     * @return bool
     */
    public function getIsReporting(): bool
    {
        return $this->IsReporting;
    }

    /**
     * @param bool $IsReporting
     */
    public function setIsReporting(bool $IsReporting): void
    {
        $this->IsReporting = $IsReporting;
    }

    /**
     * @return bool
     */
    public function getIsUcs(): bool
    {
        return $this->IsUcs;
    }

    /**
     * @param bool $IsUcs
     */
    public function setIsUcs(bool $IsUcs): void
    {
        $this->IsUcs = $IsUcs;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        if (($tblType = $this->getType())) {
            return $tblType->getName();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getYearName(): string
    {
        if (($tblYear = $this->getServiceTblYear())) {
            return $tblYear->getDisplayName();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getDivisionTeacherName(): string
    {
        switch ($this->getTypeName()) {
            case 'Klasse': return 'Klassenlehrer';
            case 'Stammgruppe': return 'Tutoren/Mentoren';
            case 'Unterrichtsgruppe':
            default: return 'Gruppenleiter';
        }
    }
}