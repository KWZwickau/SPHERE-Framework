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
    protected string $Name;

    /**
     * @Column(type="string")
     */
    protected string $Description;

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
}