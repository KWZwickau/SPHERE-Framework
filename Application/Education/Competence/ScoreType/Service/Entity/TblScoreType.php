<?php

namespace SPHERE\Application\Education\Competence\ScoreType\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Competence\ScoreType\ScoreType;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Extension\Repository\Sorter;

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
    protected string $Name = '';
    /**
     * @Column(type="string")
     */
    protected ?string $Description = null;
    /**
     * @Column(type="string")
     */
    protected string $SortOrder = '';

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
    public function getScoreTypeItems(bool $isDisplayOrder = false): array
    {
        $list = ScoreType::useService()->getScoreTypeItemsByScoreType($this->getId() < 0 ? null : $this);
        if ($isDisplayOrder) {
            $list = $this->getSorter($list)->sortObjectBy('Value', new Sorter\StringNaturalOrderSorter(),
                $this->getSortOrder() == 'desc' ? Sorter::ORDER_ASC : Sorter::ORDER_DESC);
        }

        return $list;
    }

    /**
     * @return TblScoreTypeConversion[]
     */
    public function getScoreTypeConversions(): array
    {
        return ScoreType::useService()->getScoreTypeConversionListByScoreType($this->getId() < 0 ? null : $this);
    }

    /**
     * @return string
     */
    public function getDisplayNames(): string
    {
        $names = [];
        foreach ($this->getScoreTypeItems() as $tblScoreTypeItem) {
            $names[] = $tblScoreTypeItem->getName();
        }

        return implode(', ', $names);
    }

    /**
     * @return string
     */
    public function getSortOrder(): string
    {
        return $this->SortOrder;
    }

    /**
     * @param string $SortOrder
     *
     * @return void
     */
    public function setSortOrder(string $SortOrder): void
    {
        $this->SortOrder = $SortOrder;
    }
}