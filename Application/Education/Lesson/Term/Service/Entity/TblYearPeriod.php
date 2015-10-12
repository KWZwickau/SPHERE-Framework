<?php
namespace SPHERE\Application\Education\Lesson\Term\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblYearPeriod")
 * @Cache(usage="READ_ONLY")
 */
class TblYearPeriod extends Element
{

    const ATTR_TBL_YEAR = 'tblYear';
    const ATTR_TBL_PERIOD = 'tblPeriod';

    /**
     * @Column(type="bigint")
     */
    protected $tblYear;
    /**
     * @Column(type="bigint")
     */
    protected $tblPeriod;

    /**
     * @return bool|TblYear
     */
    public function getTblYear()
    {

        if (null === $this->tblYear) {
            return false;
        } else {
            return Term::useService()->getYearById($this->tblYear);
        }
    }

    /**
     * @param null|TblYear $tblYear
     */
    public function setTblYear(TblYear $tblYear = null)
    {

        $this->tblYear = ( null === $tblYear ? null : $tblYear->getId() );
    }

    /**
     * @return bool|TblPeriod
     */
    public function getTblPeriod()
    {

        if (null === $this->tblPeriod) {
            return false;
        } else {
            return Term::useService()->getPeriodById($this->tblPeriod);
        }
    }

    /**
     * @param null|TblPeriod $tblPeriod
     */
    public function setTblPeriod(TblPeriod $tblPeriod = null)
    {

        $this->tblPeriod = ( null === $tblPeriod ? null : $tblPeriod->getId() );
    }
}
