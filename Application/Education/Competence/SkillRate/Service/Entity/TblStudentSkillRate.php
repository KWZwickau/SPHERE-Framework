<?php

namespace SPHERE\Application\Education\Competence\SkillRate\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Competence\ScoreType\ScoreType;
use SPHERE\Application\Education\Competence\ScoreType\Service\Entity\TblScoreTypeItem;
use SPHERE\Application\Education\Competence\SkillRate\SkillRate;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblCompetenceStudentSkillRate")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentSkillRate extends Element
{
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
    protected int $tblCompetenceStudentSkill;
    /**
     * @Column(type="datetime")
     */
    protected ?DateTime $Date = null;
    /**
     * @Column(type="string")
     */
    protected ?string $Comment = null;
    /**
     * @Column(type="string")
     */
    protected ?string $Rate = null;
    /**
     * @Column(type="bigint")
     */
    protected ?int $serviceTblScoreTypeItem = null;

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
     * @return false|TblStudentSkill
     */
    public function getTblStudentSkill(): false|TblStudentSkill
    {
        return SkillRate::useService()->getStudentSkillById($this->tblCompetenceStudentSkill);
    }

    /**
     * @param TblStudentSkill $tblStudentSkill
     *
     * @return void
     */
    public function setTblStudentSkill(TblStudentSkill $tblStudentSkill): void
    {
        $this->tblCompetenceStudentSkill = $tblStudentSkill->getId();
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
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->Comment;
    }

    /**
     * @param string|null $Comment
     *
     * @return void
     */
    public function setComment(?string $Comment): void
    {
        $this->Comment = $Comment;
    }

    /**
     * @return string|null
     */
    public function getRate(): ?string
    {
        return $this->Rate;
    }

    /**
     * @param string|null $Rate
     *
     * @return void
     */
    public function setRate(?string $Rate): void
    {
        $this->Rate = $Rate;
    }

    /**
     * @return false|TblScoreTypeItem|null
     */
    public function getServiceTblScoreTypeItem(): false|TblScoreTypeItem|null
    {
        return $this->serviceTblScoreTypeItem ? ScoreType::useService()->getScoreTypeItemById($this->serviceTblScoreTypeItem) : null;
    }

    /**
     * @param TblScoreTypeItem|null $tblScoreTypeItem
     *
     * @return void
     */
    public function setServiceTblScoreTypeItem(?TblScoreTypeItem $tblScoreTypeItem): void
    {
        $this->serviceTblScoreTypeItem = $tblScoreTypeItem?->getId();
    }
}