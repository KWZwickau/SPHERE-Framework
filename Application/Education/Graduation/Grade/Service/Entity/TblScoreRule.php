<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGraduationScoreRule")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreRule extends Element
{
    const ATTR_NAME = 'Name';
    const ATTR_IS_ACTIVE = 'IsActive';

    /**
     * @Column(type="string")
     */
    protected string $Name = '';
    /**
     * @Column(type="string")
     */
    protected string $Description = '';
    /**
     * @Column(type="string")
     */
    protected string $DescriptionForExtern = '';
    /**
     * @Column(type="boolean")
     */
    protected bool $IsActive;

    /**
     * @param string $name
     * @param string $description
     * @param string $descriptionForExtern
     * @param bool $isActive
     * @param int|null $id
     */
    public function __construct(string $name, string $description, string $descriptionForExtern, bool $isActive = true, ?int $id = null)
    {
        $this->Name = $name;
        $this->Description = $description;
        $this->DescriptionForExtern = $descriptionForExtern;
        $this->IsActive = $isActive;
        if ($id) {
            $this->Id = $id;
        }
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
     * @return string
     */
    public function getDescriptionForExtern(): string
    {
        return $this->DescriptionForExtern;
    }

    /**
     * @param string $DescriptionForExtern
     */
    public function setDescriptionForExtern(string $DescriptionForExtern): void
    {
        $this->DescriptionForExtern = $DescriptionForExtern;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->IsActive;
    }

    /**
     * @param bool $IsActive
     */
    public function setIsActive(bool $IsActive): void
    {
        $this->IsActive = $IsActive;
    }

    /**
     * @return bool
     */
    public function getIsUsed(): bool
    {
        return Grade::useService()->getIsScoreRuleUsed($this);
    }

    /**
     * @return false|TblScoreRuleSubject[]
     */
    public function getScoreRuleSubjects(TblYear $tblYear, TblType $tblSchoolType)
    {
        return Grade::useService()->getScoreRuleSubjectListByScoreRuleAndYearAndSchoolType($this, $tblYear, $tblSchoolType);
    }

    /**
     * @return false|TblScoreRuleSubjectDivisionCourse[]
     */
    public function getScoreRuleSubjectDivisionCourses(TblYear $tblYear, TblType $tblSchoolType)
    {
        return Grade::useService()->getScoreRuleSubjectDivisionCourseListByScoreRuleAndYearAndSchoolType($this, $tblYear, $tblSchoolType);
    }

    /**
     * @return false|TblGradeType[]
     */
    public function getGradeTypeList()
    {
        return Grade::useService()->getGradeTypeListByScoreRule($this);
    }
}