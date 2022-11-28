<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGraduationTaskGradeTypeLink")
 * @Cache(usage="READ_ONLY")
 */
class TblTaskGradeTypeLink extends Element
{
    const ATTR_TBL_TASK = 'tblGraduationTask';
    const ATTR_TBL_GRADE_TYPE = 'tblGraduationGradeType';

    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationTask;
    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationGradeType;

    /**
     * @param TblTask $tblTask
     * @param TblGradeType $tblGradeType
     */
    public function __construct(TblTask $tblTask, TblGradeType $tblGradeType)
    {
        $this->tblGraduationTask = $tblTask->getId();
        $this->tblGraduationGradeType = $tblGradeType->getId();
    }

    /**
     * @return TblTask
     */
    public function getTblTask(): TblTask
    {
        return Grade::useService()->getTaskById($this->tblGraduationTask);
    }

    /**
     * @param TblTask $tblTask
     */
    public function setTblTask(TblTask $tblTask)
    {
        $this->tblGraduationTask = $tblTask->getId();
    }

    /**
     * @return false|TblGradeType
     */
    public function getTblGradeType()
    {
        return Grade::useService()->getGradeTypeById($this->tblGraduationGradeType);
    }

    /**
     * @param TblGradeType $tblGraduationGradeType
     */
    public function setTblGradeType(TblGradeType $tblGraduationGradeType)
    {
        $this->tblGraduationGradeType = $tblGraduationGradeType->getId();
    }
}