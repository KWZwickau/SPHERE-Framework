<?php
namespace SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblResponsibility")
 * @Cache(usage="READ_ONLY")
 */
class TblResponsibility extends Element
{

    const SERVICE_TBL_COMPANY = 'serviceTblCompany';
    const ATTR_COMPANY_NUMBER = 'CompanyNumber';
    const ATTR_COMPANY_NUMBER_STAFF = 'CompanyNumberStaff';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCompany;
    /**
     * @Column(type="string")
     */
    protected $CompanyNumber;
    /**
     * @Column(type="string")
     */
    protected $CompanyNumberStaff;

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

    /**
     * @return string
     */
    public function getCompanyNumber()
    {
        return $this->CompanyNumber;
    }

    /**
     * @param string $CompanyNumber
     */
    public function setCompanyNumber($CompanyNumber)
    {
        $this->CompanyNumber = $CompanyNumber;
    }

    /**
     * @return string
     */
    public function getCompanyNumberStaff()
    {
        return $this->CompanyNumberStaff;
    }

    /**
     * @param string $CompanyNumberStaff
     */
    public function setCompanyNumberStaff($CompanyNumberStaff)
    {
        $this->CompanyNumberStaff = $CompanyNumberStaff;
    }
}
