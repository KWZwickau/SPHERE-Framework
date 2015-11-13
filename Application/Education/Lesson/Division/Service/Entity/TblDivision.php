<?php
namespace SPHERE\Application\Education\Lesson\Division\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\System\Database\Fitting\Element;

/**
 * e.g. 6 Alpha
 *
 * @Entity
 * @Table(name="tblDivision")
 * @Cache(usage="READ_ONLY")
 */
class TblDivision extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_LEVEL = 'tblLevel';
    const ATTR_YEAR = 'serviceTblYear';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;
    /**
     * @Column(type="bigint")
     */
    protected $tblLevel;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblYear;

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {

        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }

    /**
     * @return bool|TblLevel
     */
    public function getTblLevel()
    {

        if (null === $this->tblLevel) {
            return false;
        } else {
            return Division::useService()->getLevelById($this->tblLevel);
        }
    }

    /**
     * @param null|TblLevel $tblLevel
     */
    public function setTblLevel(TblLevel $tblLevel = null)
    {

        $this->tblLevel = ( null === $tblLevel ? null : $tblLevel->getId() );
    }

    /**
     * @return bool|TblYear
     */
    public function getServiceTblYear()
    {

        if (null === $this->serviceTblYear) {
            return false;
        } else {
            return Term::useService()->getYearById($this->serviceTblYear);
        }
    }

    /**
     * @param TblYear|null $tblYear
     */
    public function setServiceTblYear(TblYear $tblYear = null)
    {

        $this->serviceTblYear = ( null === $tblYear ? null : $tblYear->getId() );
    }
}
