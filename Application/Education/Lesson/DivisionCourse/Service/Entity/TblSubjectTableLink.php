<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblLessonSubjectTableLink")
 * @Cache(usage="READ_ONLY")
 */
class TblSubjectTableLink extends Element
{
    const ATTR_TBL_LINK_ID = 'LinkId';
    const ATTR_TBL_SUBJECT_TABLE = 'tblLessonSubjectTable';

    /**
     * @Column(type="bigint")
     */
    protected int $LinkId;

    /**
     * @Column(type="bigint")
     */
    protected int $tblLessonSubjectTable;

    /**
     * @Column(type="integer")
     */
    protected int $MinCount;

    /**
     * @return int
     */
    public function getLinkId(): int
    {
        return $this->LinkId;
    }

    /**
     * @param int $LinkId
     */
    public function setLinkId(int $LinkId): void
    {
        $this->LinkId = $LinkId;
    }

    /**
     * @return TblSubjectTable|false
     */
    public function getTblSubjectTable()
    {
        return DivisionCourse::useService()->getSubjectTableById($this->tblLessonSubjectTable);
    }

    /**
     * @param TblSubjectTable $tblSubjectTable
     */
    public function setTblSubjectTable(TblSubjectTable $tblSubjectTable): void
    {
        $this->tblLessonSubjectTable = $tblSubjectTable->getId();
    }

    /**
     * @return int
     */
    public function getMinCount(): int
    {
        return $this->MinCount;
    }

    /**
     * @param int $MinCount
     */
    public function setMinCount(int $MinCount): void
    {
        $this->MinCount = $MinCount;
    }
}