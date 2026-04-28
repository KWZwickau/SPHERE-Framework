<?php

namespace SPHERE\Application\Education\Competence\SkillGrid\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Competence\SkillGrid\SkillGrid;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCompetenceSkill")
 * @Cache(usage="READ_ONLY")
 */
class TblSkill extends Element
{
    const string ATTR_TBL_SKILL_AREA = 'tblCompetenceSkillArea';

    /**
     * @Column(type="bigint")
     */
    protected int $tblCompetenceSkillArea;
    /**
     * @Column(type="string")
     */
    protected ?string $Level = null;
    /**
     * @Column(type="string")
     */
    protected string $Skill;
    /**
     * @Column(type="bigint")
     */
    protected int $SortOrder;

    /**
     * @return TblSkillArea|false
     */
    public function getTblSkillArea(): TblSkillArea|false
    {
        return SkillGrid::useService()->getSkillAreaById($this->tblCompetenceSkillArea);
    }

    /**
     * @param TblSkillArea $tblSkillArea
     * @return void
     */
    public function setTblSkillArea(TblSkillArea $tblSkillArea): void
    {
        $this->tblCompetenceSkillArea = $tblSkillArea->getId();
    }

    public function getLevel(): ?string
    {
        return $this->Level;
    }

    public function setLevel(?string $Level): void
    {
        $this->Level = $Level;
    }

    public function getSkill(): string
    {
        return $this->Skill;
    }

    public function setSkill(string $Skill): void
    {
        $this->Skill = $Skill;
    }

    public function getSortOrder(): int
    {
        return $this->SortOrder;
    }

    public function setSortOrder(int $SortOrder): void
    {
        $this->SortOrder = $SortOrder;
    }

    /**
     * @return false|TblSkillGrid
     */
    public function getTblSkillGrid(): false|TblSkillGrid
    {
        if (($tblSkillArea = $this->getTblSkillArea())) {
            return $tblSkillArea->getTblSkillGrid();
        }

        return false;
    }
}