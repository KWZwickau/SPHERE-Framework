<?php
namespace SPHERE\Application\Education\Certificate\Generator\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\Education\Graduation\Gradebook\Service\Entity\TblGradeType;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblCertificateBehavior")
 * @Cache(usage="READ_ONLY")
 */
class TblCertificateGrade extends Element
{

    const ATTR_LANE = 'Lane';
    const ATTR_RANKING = 'Ranking';
    const SERVICE_TBL_GRADE_TYPE = 'serviceTblGradeType';

    /**
     * @Column(type="integer")
     */
    protected $Lane;
    /**
     * @Column(type="integer")
     */
    protected $Ranking;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblGradeType;

    /**
     * @return int
     */
    public function getLane()
    {

        return $this->Lane;
    }

    /**
     * @param int $Index
     */
    public function setLane($Index)
    {

        $this->Lane = $Index;
    }

    /**
     * @return int
     */
    public function getRanking()
    {

        return $this->Ranking;
    }

    /**
     * @param int $Index
     */
    public function setRanking($Index)
    {

        $this->Ranking = $Index;
    }

    /**
     * @return bool|TblGradeType
     */
    public function getServiceTblGradeType()
    {

        if (null === $this->serviceTblGradeType) {
            return false;
        } else {
            return Gradebook::useService()->getGradeTypeById($this->serviceTblGradeType);
        }
    }

    /**
     * @param TblGradeType|null $tblGradeType
     */
    public function setServiceTblGradeType(TblGradeType $tblGradeType = null)
    {

        $this->serviceTblGradeType = ( null === $tblGradeType ? null : $tblGradeType->getId() );
    }
}
