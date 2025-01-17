<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblLessonDivisionCourseMemberType")
 * @Cache(usage="READ_ONLY")
 */
class TblDivisionCourseMemberType extends Element
{
    const ATTR_NAME = 'Name';
    const ATTR_IDENTIFIER = 'Identifier';

    const TYPE_STUDENT = 'STUDENT';
    const TYPE_DIVISION_TEACHER = 'DIVISION_TEACHER';
    const TYPE_CUSTODY = 'CUSTODY';
    const TYPE_REPRESENTATIVE = 'REPRESENTATIVE';

    /**
     * @Column(type="string")
     */
    protected string $Name;

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
}