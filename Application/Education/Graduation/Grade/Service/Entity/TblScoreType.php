<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGraduationScoreType")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreType extends Element
{
    const ATTR_IDENTIFIER = 'Identifier';

    /**
     * @Column(type="string")
     */
    protected string $Name = '';
    /**
     * @Column(type="string")
     */
    protected string $Identifier = '';
    /**
     * @Column(type="string")
     */
    protected string $Pattern = '';

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
    public function getPattern(): string
    {
        return $this->Pattern;
    }

    /**
     * @param string $Pattern
     */
    public function setPattern(string $Pattern): void
    {
        $this->Pattern = $Pattern;
    }

    /**
     * @return false|TblScoreTypeSubject[]
     */
    public function getScoreTypeSubjects(?TblType $tblSchoolType = null)
    {
        return Grade::useService()->getScoreTypeSubjectListByScoreType($this, $tblSchoolType);
    }
}