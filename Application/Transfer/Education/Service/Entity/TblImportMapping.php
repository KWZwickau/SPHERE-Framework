<?php

namespace SPHERE\Application\Transfer\Education\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblImportMapping")
 * @Cache(usage="READ_ONLY")
 */
class TblImportMapping extends Element
{
    const TYPE_SUBJECT_ACRONYM_TO_SUBJECT_ID = 'SubjectAcronymToSubjectId';
    const TYPE_TEACHER_ACRONYM_TO_PERSON_ID = 'TeacherAcronymToPersonId';
    const TYPE_DIVISION_NAME_TO_DIVISION_COURSE_NAME = 'DivisionNameToDivisionCourseName';
    const TYPE_COURSE_NAME_TO_DIVISION_COURSE_NAME = 'CourseNameToDivisionCourseName';

    const ATTR_TYPE = 'Type';
    const ATTR_ORIGINAL = 'Original';

    /**
     * @Column(type="string")
     */
    protected string $Type;
    /**
     * @Column(type="string")
     */
    protected string $Original;
    /**
     * @Column(type="string")
     */
    protected string $Mapping;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->Type;
    }

    /**
     * @param string $Type
     */
    public function setType(string $Type): void
    {
        $this->Type = $Type;
    }

    /**
     * @return string
     */
    public function getOriginal(): string
    {
        return $this->Original;
    }

    /**
     * @param string $Original
     */
    public function setOriginal(string $Original): void
    {
        $this->Original = $Original;
    }

    /**
     * @return string
     */
    public function getMapping(): string
    {
        return $this->Mapping;
    }

    /**
     * @param string $Mapping
     */
    public function setMapping(string $Mapping): void
    {
        $this->Mapping = $Mapping;
    }
}