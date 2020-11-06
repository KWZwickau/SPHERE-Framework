<?php
namespace SPHERE\Application\People\Meta\Prospect\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
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

    const ATTR_RESERVATION_YEAR = 'ReservationYear';
    const ATTR_RESERVATION_DIVISION = 'ReservationDivision';
    const ATTR_SERVICE_TBL_TYPE_OPTION_A = 'serviceTblTypeOptionA';
    const ATTR_SERVICE_TBL_TYPE_OPTION_B = 'serviceTblTypeOptionB';
    const ATTR_SERVICE_TBL_COMPANY = 'serviceTblCompany';

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
     * @Column(type="bigint")
     */
    protected $serviceTblCompany;

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

    /**
     * @return bool|TblCompany
     */
    public function getServiceTblCompany()
    {

        if (null === $this->serviceTblCompany) {
            return false;
        } else {
            return Company::useService()->getCompanyById($this->serviceTblCompany);
        }
    }

    /**
     * @param TblCompany|null $tblCompany
     */
    public function setServiceTblCompany(TblCompany $tblCompany = null)
    {

        $this->serviceTblCompany = ( null === $tblCompany ? null : $tblCompany->getId() );
    }
}
