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
 * @Table(name="tblScoreConditionGradeTypeList")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreConditionGradeTypeList extends Element
{

    const ATTR_TBL_GRADE_TYPE = 'tblGradeType';
    const ATTR_TBL_SCORE_CONDITION = 'tblScoreCondition';

    /**
     * @Column(type="bigint")
     */
    protected $tblGradeType;

    /**
     * @Column(type="bigint")
     */
    protected $tblScoreCondition;

    /**
     * @return bool|TblGradeType
     */
    public function getTblGradeType()
    {
        if (null === $this->tblGradeType) {
            return false;
        } else {
            return Gradebook::useService()->getGradeTypeById($this->tblGradeType);
        }
    }

    /**
     * @param TblGradeType|null $tblGradeType
     */
    public function setTblGradeType($tblGradeType)
    {
        $this->tblGradeType = (null === $tblGradeType ? null : $tblGradeType->getId());
    }

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
        $this->tblScoreCondition = (null === $tblScoreCondition ? null : $tblScoreCondition->getId());
    }
}