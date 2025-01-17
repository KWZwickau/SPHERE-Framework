<?php
namespace SPHERE\Application\Setting\Consumer\School\Service\Entity;

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
 * @Table(name="tblSchool")
 * @Cache(usage="READ_ONLY")
 */
class TblSchool extends Element
{

    const ATTR_SERVICE_TBL_COMPANY = 'serviceTblCompany';
    const ATTR_SERVICE_TBL_TYPE = 'serviceTblType';
    const ATTR_COMPANY_NUMBER = 'CompanyNumber'; // Unternehmensnummer
    const ATTR_SCHOOL_CODE = 'SchoolCode'; // Dienststellenschlüssel

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCompany;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblType;
    /**
     * @Column(type="string")
     */
    protected $CompanyNumber;
    /**
     * @Column(type="string")
     */
    protected $SchoolCode;

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
     * @return bool|TblType
     */
    public function getServiceTblType()
    {

        if (null === $this->serviceTblType) {
            return false;
        } else {
            return Type::useService()->getTypeById($this->serviceTblType);
        }
    }

    /**
     * @param null|TblType $serviceTblType
     */
    public function setTblType(TblType $serviceTblType = null)
    {

        $this->serviceTblType = ( null === $serviceTblType ? null : $serviceTblType->getId() );
    }

    /**
     * Unternehmensnummer
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
     * Dienststellenschlüssel
     * @return string
     */
    public function getSchoolCode()
    {
        return $this->SchoolCode;
    }

    /**
     * @param string $SchoolCode
     */
    public function setSchoolCode($SchoolCode)
    {
        $this->SchoolCode = $SchoolCode;
    }

}
