<?php
namespace SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblToResponsibility")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblToResponsibility extends Element
{

    const SERVICE_TBL_COMPANY = 'serviceTblCompany';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCompany;

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
