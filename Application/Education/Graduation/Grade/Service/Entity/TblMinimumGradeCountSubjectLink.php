<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGraduationMinimumGradeCountSubjectLink")
 * @Cache(usage="READ_ONLY")
 */
class TblMinimumGradeCountSubjectLink extends Element
{
    const ATTR_TBL_MINIMUM_GRADE_COUNT = 'tblGraduationMinimumGradeCount';
    const ATTR_TBL_SERVICE_TBL_SUBJECT = 'serviceTblSubject';

    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationMinimumGradeCount;
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblSubject;

    /**
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     * @param TblSubject $tblSubject
     */
    public function __construct(TblMinimumGradeCount $tblMinimumGradeCount, TblSubject $tblSubject)
    {
        $this->tblGraduationMinimumGradeCount = $tblMinimumGradeCount->getId();
        $this->serviceTblSubject = $tblSubject->getId();
    }

    /**
     * @return TblMinimumGradeCount|false
     */
    public function getMinimumGradeCount()
    {
        return Grade::useService()->getMinimumGradeCountById($this->tblGraduationMinimumGradeCount);
    }

    /**
     * @param TblMinimumGradeCount $tblMinimumGradeCount
     */
    public function setMinimumGradeCount(TblMinimumGradeCount $tblMinimumGradeCount)
    {
        $this->tblGraduationMinimumGradeCount = $tblMinimumGradeCount->getId();
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubject()
    {
        return Subject::useService()->getSubjectById($this->serviceTblSubject);
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubject(TblSubject $tblSubject)
    {
        $this->serviceTblSubject = $tblSubject->getId();
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        if (($tblSubject = $this->getServiceTblSubject())) {
            return $tblSubject->getAcronym();
        }

        return '-NA-';
    }
}