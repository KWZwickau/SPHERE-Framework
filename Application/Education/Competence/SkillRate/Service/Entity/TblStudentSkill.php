<?php

namespace SPHERE\Application\Education\Competence\SkillRate\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Competence\ScoreType\ScoreType;
use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Competence\SkillGrid\Service\Entity\TblSkill;
use SPHERE\Application\Education\Competence\SkillGrid\SkillGrid;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblCompetenceStudentSkill")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentSkill extends Element
{
    const string SERVICE_TBL_PERSON = 'serviceTblPerson';
    const string SERVICE_TBL_YEAR = 'serviceTblYear';
    const string SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const string SERVICE_TBL_SKILL = 'serviceTblSkill';

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblYear;
    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblSubject;
    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblPersonTeacher = null;
    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblSkill = null;
    /**
     * @Column(type="string")
     */
    protected ?string $SkillArea = null;
    /**
     * @Column(type="string")
     */
    protected ?string $SkillLevel = null;
    /**
     * @Column(type="string")
     */
    protected string $Skill;
    /**
     * @Column(type="boolean")
     */
    protected ?bool $IsAverage = null;
    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblScoreType = null;

    /**
     * @return false|TblPerson
     */
    public function getServiceTblPerson(): false|TblPerson
    {
        return Person::useService()->getPersonById($this->serviceTblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson): void
    {
        $this->serviceTblPerson = $tblPerson->getId();
    }

    /**
     * @return false|TblYear
     */
    public function getServiceTblYear(): false|TblYear
    {
        return Term::useService()->getYearById($this->serviceTblYear);
    }

    /**
     * @param TblYear $tblYear
     */
    public function setServiceTblYear(TblYear $tblYear): void
    {
        $this->serviceTblYear = $tblYear->getId();
    }

    /**
     * @return false|TblSubject|null
     */
    public function getServiceTblSubject(): false|TblSubject|null
    {
        return $this->serviceTblSubject ? Subject::useService()->getSubjectById($this->serviceTblSubject) : null;
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubject(?TblSubject $tblSubject): void
    {
        $this->serviceTblSubject = $tblSubject?->getId();
    }


    /**
     * @return false|TblPerson|null
     */
    public function getServiceTblPersonTeacher(): false|TblPerson|null
    {
        return $this->serviceTblPersonTeacher ? Person::useService()->getPersonById($this->serviceTblPersonTeacher) : null;
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPersonTeacher(?TblPerson $tblPerson): void
    {
        $this->serviceTblPersonTeacher = $tblPerson?->getId();
    }

    /**
     * @return TblSkill|false|null
     */
    public function getServiceTblSkill(): TblSkill|null|false
    {
        return $this->serviceTblSkill ? SkillGrid::useService()->getSkillById($this->serviceTblSkill) : null;
    }

    /**
     * @param TblSkill|null $tblSkill
     *
     * @return void
     */
    public function setServiceTblSkill(?TblSkill $tblSkill): void
    {
        $this->serviceTblSkill = $tblSkill?->getId();
    }

    /**
     * @return string|null
     */
    public function getSkillArea(): ?string
    {
        return $this->SkillArea;
    }

    /**
     * @param string|null $SkillArea
     *
     * @return void
     */
    public function setSkillArea(?string $SkillArea): void
    {
        $this->SkillArea = $SkillArea;
    }

    /**
     * @return string|null
     */
    public function getSkillLevel(): ?string
    {
        return $this->SkillLevel;
    }

    /**
     * @param string|null $SkillLevel
     *
     * @return void
     */
    public function setSkillLevel(?string $SkillLevel): void
    {
        $this->SkillLevel = $SkillLevel;
    }

    /**
     * @return string
     */
    public function getSkill(): string
    {
        return $this->Skill;
    }

    /**
     * @param string $Skill
     *
     * @return void
     */
    public function setSkill(string $Skill): void
    {
        $this->Skill = $Skill;
    }

    /**
     * @return bool|null
     */
    public function getIsAverage(): ?bool
    {
        if (($tblSkill = $this->getServiceTblSkill())
            && ($tblSkillGrid = $tblSkill->getTblSkillGrid())
        ) {
            return $tblSkillGrid->getIsAverage();
        }

        return $this->IsAverage;
    }

    /**
     * @param bool|null $IsAverage
     * @return void
     */
    public function setIsAverage(?bool $IsAverage): void
    {
        $this->IsAverage = $IsAverage;
    }

    /**
     * @return TblScoreType|null
     */
    public function getServiceTblScoreType(): TblScoreType|null
    {
        if (($tblSkill = $this->getServiceTblSkill())
            && ($tblSkillGrid = $tblSkill->getTblSkillGrid())
        ) {
            return $tblSkillGrid->getServiceTblScoreType() ?: null;
        }

        return $this->serviceTblScoreType
            ? (ScoreType::useService()->getScoreTypeById($this->serviceTblScoreType) ?: null)
            : null;
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
}