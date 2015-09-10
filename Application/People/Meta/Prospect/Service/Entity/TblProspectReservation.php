<?php
namespace SPHERE\Application\People\Meta\Prospect\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblProspectReservation")
 * @Cache(usage="READ_ONLY")
 */
class TblProspectReservation extends Element
{

    /**
     * @Column(type="string")
     */
    protected $ReservationYear;
    /**
     * @Column(type="string")
     */
    protected $ReservationDivision;

    /**
     * @return string
     */
    public function getReservationYear()
    {

        return $this->ReservationYear;
    }

    /**
     * @param string $ReservationYear
     */
    public function setReservationYear($ReservationYear)
    {

        $this->ReservationYear = $ReservationYear;
    }

    /**
     * @return string
     */
    public function getReservationDivision()
    {

        return $this->ReservationDivision;
    }

    /**
     * @param string $ReservationDivision
     */
    public function setReservationDivision($ReservationDivision)
    {

        $this->ReservationDivision = $ReservationDivision;
    }
}
