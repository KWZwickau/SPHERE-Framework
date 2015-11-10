<?php
namespace SPHERE\Application\People\Meta\Prospect\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
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
     * @Column(type="bigint")
     */
    protected $serviceTblTypeOptionA;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblTypeOptionB;

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

    /**
     * @return bool|TblType
     */
    public function getServiceTblTypeOptionA()
    {

        if (null === $this->serviceTblTypeOptionA) {
            return false;
        } else {
            return Type::useService()->getTypeById($this->serviceTblTypeOptionA);
        }
    }

    /**
     * @param TblType|null $tblType
     */
    public function setServiceTblTypeOptionA(TblType $tblType = null)
    {

        $this->serviceTblTypeOptionA = ( null === $tblType ? null : $tblType->getId() );
    }

    /**
     * @return bool|TblType
     */
    public function getServiceTblTypeOptionB()
    {

        if (null === $this->serviceTblTypeOptionB) {
            return false;
        } else {
            return Type::useService()->getTypeById($this->serviceTblTypeOptionB);
        }
    }

    /**
     * @param TblType|null $tblType
     */
    public function setServiceTblTypeOptionB(TblType $tblType = null)
    {

        $this->serviceTblTypeOptionB = ( null === $tblType ? null : $tblType->getId() );
    }
}
