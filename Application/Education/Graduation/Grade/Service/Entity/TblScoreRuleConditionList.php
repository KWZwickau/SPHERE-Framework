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
 * @Table(name="tblGraduationScoreRuleConditionList")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreRuleConditionList extends Element
{
    const ATTR_TBL_SCORE_CONDITION = 'tblGraduationScoreCondition';
    const ATTR_TBL_SCORE_RULE = 'tblGraduationScoreRule';

    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationScoreCondition;
    /**
     * @Column(type="bigint")
     */
    protected int $tblGraduationScoreRule;

    /**
     * @param TblScoreCondition $tblScoreCondition
     * @param TblScoreRule $tblScoreRule
     */
    public function __construct(TblScoreCondition $tblScoreCondition, TblScoreRule $tblScoreRule)
    {
        $this->tblGraduationScoreCondition = $tblScoreCondition->getId();
        $this->tblGraduationScoreRule = $tblScoreRule->getId();
    }

    /**
     * @return TblScoreCondition|false
     */
    public function getTblScoreCondition()
    {
        return Grade::useService()->getScoreConditionById($this->tblGraduationScoreCondition);
    }

    /**
     * @param TblScoreCondition $tblScoreCondition
     */
    public function setTblScoreCondition(TblScoreCondition $tblScoreCondition)
    {
        $this->tblGraduationScoreCondition = $tblScoreCondition->getId();
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