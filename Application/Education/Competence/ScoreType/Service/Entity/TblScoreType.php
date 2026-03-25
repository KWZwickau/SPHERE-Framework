<?php

namespace SPHERE\Application\Education\Competence\ScoreType\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Competence\ScoreType\ScoreType;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCompetenceScoreType")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreType extends Element
{
    /**
     * @Column(type="string")
     */
    protected string $Name;
    /**
     * @Column(type="string")
     */
    protected ?string $Description = null;

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
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->Description;
    }

    /**
     * @param string|null $Description
     * @return void
     */
    public function setDescription(?string $Description): void
    {
        $this->Description = $Description;
    }

    /**
     * @return TblScoreTypeItem[]
     */
    public function getScoreTypeItems(): array
    {
        return ScoreType::useService()->getScoreTypeItemsByScoreType($this);
    }
}