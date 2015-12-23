<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 26.11.2015
 * Time: 14:19
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblScoreRuleConditionList")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreRuleConditionList extends Element
{

    const ATTR_TBL_SCORE_CONDITION = 'tblScoreCondition';
    const ATTR_TBL_SCORE_RULE = 'tblScoreRule';

    /**
     * @Column(type="bigint")
     */
    protected $tblScoreCondition;

    /**
     * @Column(type="bigint")
     */
    protected $tblScoreRule;

    /**
     * @return bool|TblScoreCondition
     */
    public function getTblScoreCondition()
    {

        if (null === $this->tblScoreCondition) {
            return false;
        } else {
            return Gradebook::useService()->getScoreConditionById($this->tblScoreCondition);
        }
    }

    /**
     * @param TblScoreCondition|null $tblScoreCondition
     */
    public function setTblScoreCondition($tblScoreCondition)
    {

        $this->tblScoreCondition = ( null === $tblScoreCondition ? null : $tblScoreCondition->getId() );
    }

    /**
     * @return bool|TblScoreRule
     */
    public function getTblScoreRule()
    {

        if (null === $this->tblScoreRule) {
            return false;
        } else {
            return Gradebook::useService()->getScoreRuleById($this->tblScoreRule);
        }
    }

    /**
     * @param TblScoreRule|null $tblScoreRule
     */
    public function setTblScoreRule($tblScoreRule)
    {

        $this->tblScoreRule = ( null === $tblScoreRule ? null : $tblScoreRule->getId() );
    }
}
