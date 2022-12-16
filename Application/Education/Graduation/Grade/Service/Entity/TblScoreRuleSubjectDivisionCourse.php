<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblGraduationScoreRuleSubjectDivisionCourse")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreRuleSubjectDivisionCourse extends Element
{
    const ATTR_SERVICE_TBL_DIVISION_COURSE = 'serviceTblDivisionCourse';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_TBL_SCORE_RULE = 'tblGraduationScoreRule';

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblDivisionCourse;
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblSubject;
    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationScoreRule;

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     * @param TblScoreRule $tblScoreRule
     */
    public function __construct(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject, TblScoreRule $tblScoreRule)
    {
        $this->serviceTblDivisionCourse = $tblDivisionCourse->getId();
        $this->serviceTblSubject = $tblSubject->getId();
        $this->tblGraduationScoreRule = $tblScoreRule->getId();
    }

    /**
     * @return false|TblDivisionCourse
     */
    public function getServiceTblDivisionCourse()
    {
        return DivisionCourse::useService()->getDivisionCourseById($this->serviceTblDivisionCourse);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     */
    public function setServiceTblDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        $this->serviceTblDivisionCourse = $tblDivisionCourse->getId();
    }

    /**
     * @return false|TblSubject
     */
    public function getServiceTblSubject()
    {
        return Subject::useService()->getSubjectById($this->serviceTblSubject);
    }

    /**
     * @param TblSubject $tblSubject
     */
    public function setServiceTblSubject(TblSubject $tblSubject)
    {
        $this->serviceTblSubject = $tblSubject->getId();
    }

    /**
     * @return false|TblScoreRule
     */
    public function getTblScoreRule()
    {
        return Grade::useService()->getScoreRuleById($this->tblGraduationScoreRule);
    }

    /**
     * @param TblScoreRule $tblScoreRule
     */
    public function setTblScoreRule(TblScoreRule $tblScoreRule)
    {
        $this->tblGraduationScoreRule = $tblScoreRule->getId();
    }
}