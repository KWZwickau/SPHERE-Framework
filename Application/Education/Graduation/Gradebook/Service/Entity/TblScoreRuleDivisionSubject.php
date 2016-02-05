<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.01.2016
 * Time: 08:59
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblScoreRuleDivisionSubject")
 * @Cache(usage="READ_ONLY")
 */
class TblScoreRuleDivisionSubject extends Element
{

    const ATTR_TBL_SCORE_RULE = 'tblScoreRule';
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_TBL_SCORE_TYPE = 'tblScoreType';

    /**
     * @Column(type="bigint")
     */
    protected $tblScoreRule;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;

    /**
     * @Column(type="bigint")
     */
    protected $tblScoreType;

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

    /**
     * @return bool|TblDivision
     */
    public function getServiceTblDivision()
    {

        if (null === $this->serviceTblDivision) {
            return false;
        } else {
            return Division::useService()->getDivisionById($this->serviceTblDivision);
        }
    }

    /**
     * @param TblDivision|null $tblDivision
     */
    public function setServiceTblDivision(TblDivision $tblDivision = null)
    {

        $this->serviceTblDivision = ( null === $tblDivision ? null : $tblDivision->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubject()
    {

        if (null === $this->serviceTblSubject) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubject);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubject(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubject = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblScoreType
     */
    public function getTblScoreType()
    {

        if (null === $this->tblScoreType) {
            return false;
        } else {
            return Gradebook::useService()->getScoreTypeById($this->tblScoreType);
        }
    }

    /**
     * @param TblScoreType|null $tblScoreType
     */
    public function setTblScoreType($tblScoreType)
    {

        $this->tblScoreType = ( null === $tblScoreType ? null : $tblScoreType->getId() );
    }

}