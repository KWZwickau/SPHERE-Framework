<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblGraduationScoreRuleSubject")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreRuleSubject extends Element
{
    const ATTR_SERVICE_TBL_YEAR = 'serviceTblYear';
    const ATTR_SERVICE_TBL_SCHOOL_TYPE = 'serviceTblSchoolType';
    const ATTR_LEVEL = 'Level';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_TBL_SCORE_RULE = 'tblGraduationScoreRule';

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblYear;
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblSchoolType;
    /**
     * @Column(type="integer")
     */
    protected int $Level;
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblSubject;
    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationScoreRule;

    /**
     * @param TblYear $tblYear
     * @param TblType $tblSchoolType
     * @param int $level
     * @param TblSubject $tblSubject
     * @param TblScoreRule $tblScoreRule
     */
    public function __construct(TblYear $tblYear, TblType $tblSchoolType, int $level, TblSubject $tblSubject, TblScoreRule $tblScoreRule)
    {
        $this->serviceTblYear = $tblYear->getId();
        $this->serviceTblSchoolType = $tblSchoolType->getId();
        $this->Level = $level;
        $this->serviceTblSubject = $tblSubject->getId();
        $this->tblGraduationScoreRule = $tblScoreRule->getId();
    }

    /**
     * @return bool|TblYear
     */
    public function getServiceTblYear()
    {
        return Term::useService()->getYearById($this->serviceTblYear);
    }

    /**
     * @param TblYear $tblYear
     */
    public function setServiceTblYear(TblYear $tblYear)
    {
        $this->serviceTblYear = $tblYear->getId();
    }

    /**
     * @return false|TblType
     */
    public function getServiceTblSchoolType()
    {
        return Type::useService()->getTypeById($this->serviceTblSchoolType);
    }

    /**
     * @param TblType $tblSchoolType
     */
    public function setServiceTblSchoolType(TblType $tblSchoolType): void
    {
        $this->serviceTblSchoolType = $tblSchoolType->getId();
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->Level;
    }

    /**
     * @param int $Level
     */
    public function setLevel(int $Level): void
    {
        $this->Level = $Level;
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