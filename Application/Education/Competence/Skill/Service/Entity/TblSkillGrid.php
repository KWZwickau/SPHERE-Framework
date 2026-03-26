<?php

namespace SPHERE\Application\Education\Competence\Skill\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Competence\ScoreType\ScoreType;
use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Competence\Skill\Skill;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblSupportFocusType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCompetenceSkillGrid")
 * @Cache(usage="READ_ONLY")
 */
class TblSkillGrid extends Element
{
    const string SERVICE_TBL_SCHOOL_TYPE = 'serviceTblSchoolType';
    const string SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const string SERVICE_TBL_SCORE_TYPE = 'serviceTblScoreType';
    const string ATTR_LEVEL = 'Level';

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblSchoolType;
    /**
     * @Column(type="integer")
     */
    protected int $Level;
    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblSubject = null;
    /**
     * @Column(type="string")
     */
    protected string $Name;
    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblCourse = null;
    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblSupportFocusType = null;
    /**
     * @Column(type="boolean")
     */
    protected bool $IsAverage;
    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblScoreType = null;

    /**
     * @return false|TblType
     */
    public function getServiceTblSchoolType(): false|TblType
    {
        return Type::useService()->getTypeById($this->serviceTblSchoolType);
    }

    /**
     * @param TblType $tblSchoolType
     */
    public function setServiceTblSchoolType(TblType $tblSchoolType): void
    {
        $this->serviceTblSchoolType = $tblSchoolType->getId();
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->Level;
    }

    /**
     * @param int $Level
     */
    public function setLevel(int $Level): void
    {
        $this->Level = $Level;
    }

    /**
     * @return false|TblSubject
     */
    public function getServiceTblSubject(): false|TblSubject
    {
        return Subject::useService()->getSubjectById($this->serviceTblSubject);
    }

    /**
     * @param ?TblSubject $tblSubject
     */
    public function setServiceTblSubject(?TblSubject $tblSubject): void
    {
        $this->serviceTblSubject = $tblSubject?->getId();
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
     *
     * @return void
     */
    public function setName(string $Name): void
    {
        $this->Name = $Name;
    }

    /**
     * @return false|TblCourse
     */
    public function getServiceTblCourse(): false|TblCourse
    {
        return $this->serviceTblCourse ? Course::useService()->getCourseById($this->serviceTblCourse) : false;
    }

    /**
     * @param TblCourse|null $tblCourse
     */
    public function setServiceTblCourse(?TblCourse $tblCourse): void
    {
        $this->serviceTblCourse = $tblCourse?->getId();
    }

    /**
     * @return false|TblSupportFocusType
     */
    public function getServiceTblSupportFocusType(): false|TblSupportFocusType
    {
        return $this->serviceTblSupportFocusType ? Student::useService()->getSupportFocusTypeById($this->serviceTblSupportFocusType) : false;
    }

    /**
     * @param TblSupportFocusType|null $tblSupportFocusType
     */
    public function setServiceTblSupportFocusType(?TblSupportFocusType $tblSupportFocusType): void
    {
        $this->serviceTblSupportFocusType = $tblSupportFocusType?->getId();
    }

    public function getIsAverage(): bool
    {
        return $this->IsAverage;
    }

    public function setIsAverage(bool $IsAverage): void
    {
        $this->IsAverage = $IsAverage;
    }

    /**
     * @return TblScoreType|false
     */
    public function getServiceTblScoreType(): TblScoreType|false
    {
        return ScoreType::useService()->getScoreTypeById($this->serviceTblScoreType);
    }

    /**
     * @param TblScoreType|null $tblScoreType
     *
     * @return void
     */
    public function setServiceTblScoreType(?TblScoreType $tblScoreType): void
    {
        $this->serviceTblScoreType = $tblScoreType?->getId();
    }

    /**
     * @return TblSkillArea[]
     */
    public function getSkillAreas(): array
    {
        return Skill::useService()->getSkillAreaListBySkillGrid($this);
    }

    /**
     * @return TblSkill[]
     */
    public function getSkills(): array
    {
        return Skill::useService()->getSkillListBySkillGrid($this);
    }

    /**
     * @return string
     */
    public function getDisplaySkillAreas(): string
    {
        $skillAreas = [];
        foreach ($this->getSkillAreas() as $tblSkillArea) {
            $skillAreas[] = $tblSkillArea->getName() ?: 'Ohne Kompetenzbereich';
        }

        return implode(', ', $skillAreas);
    }
}