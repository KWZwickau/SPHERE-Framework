<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentTransferArrive")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentTransferArrive extends Element
{

    const SERVICE_TBL_COMPANY = 'serviceTblCompany';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCompany;
    /**
     * @Column(type="datetime")
     */
    protected $ArriveDate;
    /**
     * @Column(type="text")
     */
    protected $Remark;

    /**
     * @return string
     */
    public function getArriveDate()
    {

        if (null === $this->ArriveDate) {
            return false;
        }
        /** @var \DateTime $ArriveDate */
        $ArriveDate = $this->ArriveDate;
        if ($ArriveDate instanceof \DateTime) {
            return $ArriveDate->format('d.m.Y');
        } else {
            return (string)$ArriveDate;
        }
    }

    /**
     * @param null|\DateTime $ArriveDate
     */
    public function setArriveDate(\DateTime $ArriveDate = null)
    {

        $this->ArriveDate = $ArriveDate;
    }

    /**
     * @return string
     */
    public function getRemark()
    {

        return $this->Remark;
    }

    /**
     * @param string $Remark
     */
    public function setRemark($Remark)
    {

        $this->Remark = $Remark;
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
