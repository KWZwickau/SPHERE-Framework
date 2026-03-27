<?php

namespace SPHERE\Application\Education\Competence\ScoreType\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCompetenceScoreTypeConversion")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreTypeConversion extends Element
{
    CONST string TBL_SCORE_TYPE = "tblCompetenceScoreType";
    CONST string ATTR_GRADE = "Grade";

    /**
     * @Column(type="bigint")
     */
    protected ?int $tblCompetenceScoreType = null;
    /**
     * @Column(type="string")
     */
    protected string $Grade;
    /**
     * @Column(type="string")
     */
    protected string $Value;

    /**
     * @param TblScoreType|null $tblScoreType
     * @return void
     */
    public function setTblScoreType(?TblScoreType $tblScoreType): void
    {
        $this->tblCompetenceScoreType = $tblScoreType?->getId();
    }

    public function getGrade(): string
    {
        return $this->Grade;
    }

    public function setGrade(string $Grade): void
    {
        $this->Grade = $Grade;
    }

    public function getValue(): string
    {
        return $this->Value;
    }

    public function setValue(string $Value): void
    {
        $this->Value = $Value;
    }
}