<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblLessonDivisionCourseType")
 * @Cache(usage="READ_ONLY")
 */
class TblDivisionCourseType extends Element
{
    const ATTR_NAME = 'Name';
    const ATTR_IDENTIFIER = 'Identifier';

    const TYPE_DIVISION = 'DIVISION';
    const TYPE_CORE_GROUP = 'CORE_GROUP';
    const TYPE_TEACHING_GROUP = 'TEACHING_GROUP';
    const TYPE_ADVANCED_COURSE = 'ADVANCED_COURSE';
    const TYPE_BASIC_COURSE = 'BASIC_COURSE';
    const TYPE_TEACHER_GROUP = 'TEACHER_GROUP';

    /**
     * @Column(type="string")
     */
    protected string $Name = '';

    /**
     * @Column(type="string")
     */
    protected string $Identifier = '';

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
     * @return bool
     */
    public function getIsCourseSystem(): bool
    {
        return $this->getIdentifier() == TblDivisionCourseType::TYPE_ADVANCED_COURSE || $this->getIdentifier() == TblDivisionCourseType::TYPE_BASIC_COURSE;
    }
}