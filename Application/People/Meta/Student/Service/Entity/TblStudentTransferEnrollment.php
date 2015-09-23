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
 * @Table(name="tblStudentTransferEnrollment")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentTransferEnrollment extends Element
{

    const SERVICE_TBL_COMPANY = 'serviceTblCompany';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCompany;
    /**
     * @Column(type="datetime")
     */
    protected $EnrollmentDate;
    /**
     * @Column(type="text")
     */
    protected $Remark;

    /**
     * @return string
     */
    public function getEnrollmentDate()
    {

        if (null === $this->EnrollmentDate) {
            return false;
        }
        /** @var \DateTime $EnrollmentDate */
        $EnrollmentDate = $this->EnrollmentDate;
        if ($EnrollmentDate instanceof \DateTime) {
            return $EnrollmentDate->format('d.m.Y');
        } else {
            return (string)$EnrollmentDate;
        }
    }

    /**
     * @param null|\DateTime $EnrollmentDate
     */
    public function setEnrollmentDate(\DateTime $EnrollmentDate = null)
    {

        $this->EnrollmentDate = $EnrollmentDate;
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
