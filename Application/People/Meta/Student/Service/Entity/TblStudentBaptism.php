<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentBaptism")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentBaptism extends Element
{

    /**
     * @Column(type="datetime")
     */
    protected $BaptismDate;
    /**
     * @Column(type="string")
     */
    protected $Location;

    /**
     * @return string
     */
    public function getBaptismDate()
    {

        if (null === $this->BaptismDate) {
            return false;
        }
        /** @var \DateTime $BaptismDate */
        $BaptismDate = $this->BaptismDate;
        if ($BaptismDate instanceof \DateTime) {
            return $BaptismDate->format('d.m.Y');
        } else {
            return (string)$BaptismDate;
        }
    }

    /**
     * @param null|\DateTime $BaptismDate
     */
    public function setBaptismDate(\DateTime $BaptismDate = null)
    {

        $this->BaptismDate = $BaptismDate;
    }

    /**
     * @return string
     */
    public function getLocation()
    {

        return $this->Location;
    }

    /**
     * @param string $Location
     */
    public function setLocation($Location)
    {

        $this->Location = $Location;
    }
}
