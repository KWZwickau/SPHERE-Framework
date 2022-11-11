<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGraduationGradeType")
 * @Cache(usage="READ_ONLY")
 */
class TblGradeType extends Element
{
    const ATTR_CODE = 'Code';
    const ATTR_IS_ACTIVE = 'IsActive';

    /**
     * @Column(type="string")
     */
    protected string $Code;
    /**
     * @Column(type="string")
     */
    protected string $Name;
    /**
     * @Column(type="string")
     */
    protected string $Description;
    /**
     * @Column(type="boolean")
     */
    protected bool $IsTypeBehavior;
    /**
     * @Column(type="boolean")
     */
    protected bool $IsHighlighted;
    /**
     * @Column(type="boolean")
     */
    protected bool $IsPartGrade;
    /**
     * @Column(type="boolean")
     */
    protected bool $IsActive;

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->Code;
    }

    /**
     * @param string $code
     * @param string $name
     * @param string $description
     * @param bool $isTypeBehavior
     * @param bool $isHighlighted
     * @param bool $isPartGrade
     * @param bool $isActive
     * @param int|null $id
     */
    public function __construct(string $code, string $name, string $description,
        bool $isTypeBehavior, bool $isHighlighted, bool $isPartGrade, bool $isActive, ?int $id = null)
    {
        $this->Code = $code;
        $this->Name = $name;
        $this->Description = $description;
        $this->IsTypeBehavior = $isTypeBehavior;
        $this->IsHighlighted = $isHighlighted;
        $this->IsPartGrade = $isPartGrade;
        $this->IsActive = $isActive;
        if ($id) {
            $this->Id = $id;
        }
    }

    /**
     * @param string $Code
     */
    public function setCode(string $Code): void
    {
        $this->Code = $Code;
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
     * @return bool
     */
    public function getIsTypeBehavior(): bool
    {
        return $this->IsTypeBehavior;
    }

    /**
     * @param bool $IsTypeBehavior
     */
    public function setIsTypeBehavior(bool $IsTypeBehavior): void
    {
        $this->IsTypeBehavior = $IsTypeBehavior;
    }

    /**
     * @return bool
     */
    public function getIsHighlighted(): bool
    {
        return $this->IsHighlighted;
    }

    /**
     * @param bool $IsHighlighted
     */
    public function setIsHighlighted(bool $IsHighlighted): void
    {
        $this->IsHighlighted = $IsHighlighted;
    }

    /**
     * @return bool
     */
    public function getIsPartGrade(): bool
    {
        return $this->IsPartGrade;
    }

    /**
     * @param bool $IsPartGrade
     */
    public function setIsPartGrade(bool $IsPartGrade): void
    {
        $this->IsPartGrade = $IsPartGrade;
    }
}