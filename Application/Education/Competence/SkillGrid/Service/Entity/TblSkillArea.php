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
 * @Table(name="tblCompetenceSkillArea")
 * @Cache(usage="READ_ONLY")
 */
class TblSkillArea extends Element
{
    const string ATTR_TBL_SKILL_GRID = 'tblCompetenceSkillGrid';
    const string ATTR_SORT_ORDER = 'SortOrder';

    /**
     * @Column(type="bigint")
     */
    protected int $tblCompetenceSkillGrid;
    /**
     * @Column(type="string")
     */
    protected ?string $Name = null;
    /**
     * @Column(type="bigint")
     */
    protected int $SortOrder;

    /**
     * @return TblSkillGrid|false
     */
    public function getTblSkillGrid(): TblSkillGrid|false
    {
        return SkillGrid::useService()->getSkillGridById($this->tblCompetenceSkillGrid);
    }

    /**
     * @param TblSkillGrid $tblSkillGrid
     * @return void
     */
    public function setTblSkillGrid(TblSkillGrid $tblSkillGrid): void
    {
        $this->tblCompetenceSkillGrid = $tblSkillGrid->getId();
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function getDisplayName(): string
    {
        return $this->Name ?: 'Ohne Kompetenzbereich';
    }

    public function setName(?string $Name): void
    {
        $this->Name = $Name;
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
     * @return TblSkill[]
     */
    public function getSkills(): array
    {
        return SkillGrid::useService()->getSkillListBySkillArea($this);
    }
}