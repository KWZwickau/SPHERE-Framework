<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGraduationGradeText")
 * @Cache(usage="READ_ONLY")
 */
class TblGradeText extends Element
{
    const ATTR_NAME = 'Name';
    const ATTR_IDENTIFIER = 'Identifier';

    /**
     * @Column(type="string")
     */
    protected string $Name = '';
    /**
     * @Column(type="string")
     */
    protected string $ShortName;
    /**
     * @Column(type="string")
     */
    protected string $Identifier;

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
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->Identifier;
    }

    /**
     * @param string $Identifier
     */
    public function setIdentifier(string $Identifier): void
    {
        $this->Identifier = $Identifier;
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return $this->ShortName;
    }

    /**
     * @param string $ShortName
     */
    public function setShortName(string $ShortName): void
    {
        $this->ShortName = $ShortName;
    }
}