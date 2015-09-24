<?php
namespace SPHERE\Application\Setting\Consumer\School\Service\Entity;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblSchool")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblSchool extends Element
{

    const SERVICE_TBL_COMPANY = 'serviceTblCompany';
    const ATT_TBL_TYPE = 'tblType';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCompany;
    /**
     * @Column(type="bigint")
     */
    protected $tblType;

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
    public function getTblType()
    {

        if (null === $this->tblType) {
            return false;
        } else {
            return School::useService()->getTypeById($this->tblType);
        }
    }

    /**
     * @param null|TblType $tblType
     */
    public function setTblType(TblType $tblType = null)
    {

        $this->tblType = ( null === $tblType ? null : $tblType->getId() );
    }

}
