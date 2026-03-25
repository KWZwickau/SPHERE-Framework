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
 * @Table(name="tblCompetenceScoreTypeItem")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreTypeItem extends Element
{
    /**
     * @Column(type="bigint")
     */
    protected int $tblCompetenceScoreType;
    /**
     * @Column(type="string")
     */
    protected string $Value;
    /**
     * @Column(type="string")
     */
    protected string $Name;
    /**
     * @Column(type="string")
     */
    protected ?string $Description = null;

    /**
     * @return TblScoreType|false
     */
    public function getTblScoreType(): TblScoreType|false
    {
        return ScoreType::useService()->getScoreTypeById($this->tblCompetenceScoreType);
    }

    /**
     * @param TblScoreType $tblScoreType
     * @return void
     */
    public function setTblScoreType(TblScoreType $tblScoreType): void
    {
        $this->tblCompetenceScoreType = $tblScoreType->getId();
    }
    
    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->Value;
    }

    /**
     * @param string $Value
     *
     * @return void
     */
    public function setValue(string $Value): void
    {
        $this->Value = $Value;
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
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->Description;
    }

    /**
     * @param string|null $Description
     *
     * @return void
     */
    public function setDescription(?string $Description): void
    {
        $this->Description = $Description;
    }
}