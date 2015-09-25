<?php
namespace SPHERE\Application\People\Meta\Prospect\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
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
    protected $serviceTblCompanyOptionA;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCompanyOptionB;

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
     * @return bool|TblCompany
     */
    public function getServiceTblCompanyOptionA()
    {

        if (null === $this->serviceTblCompanyOptionA) {
            return false;
        } else {
            return Company::useService()->getCompanyById($this->serviceTblCompanyOptionA);
        }
    }

    /**
     * @param TblCompany|null $tblCompany
     */
    public function setServiceTblCompanyOptionA(TblCompany $tblCompany = null)
    {

        $this->serviceTblCompanyOptionA = ( null === $tblCompany ? null : $tblCompany->getId() );
    }

    /**
     * @return bool|TblCompany
     */
    public function getServiceTblCompanyOptionB()
    {

        if (null === $this->serviceTblCompanyOptionB) {
            return false;
        } else {
            return Company::useService()->getCompanyById($this->serviceTblCompanyOptionB);
        }
    }

    /**
     * @param TblCompany|null $tblCompany
     */
    public function setServiceTblCompanyOptionB(TblCompany $tblCompany = null)
    {

        $this->serviceTblCompanyOptionB = ( null === $tblCompany ? null : $tblCompany->getId() );
    }
}
