<?php
namespace SPHERE\Application\Contact\Web\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Web\Web;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblToCompany")
 * @Cache(usage="READ_ONLY")
 */
class TblToCompany extends Element
{

    const ATT_TBL_TYPE = 'tblType';
    const ATT_TBL_WEB = 'tblWeb';
    const SERVICE_TBL_COMPANY = 'serviceTblCompany';

    /**
     * @Column(type="text")
     */
    protected $Remark;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCompany;
    /**
     * @Column(type="bigint")
     */
    protected $tblType;
    /**
     * @Column(type="bigint")
     */
    protected $tblWeb;

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
     * @return bool|TblType
     */
    public function getTblType()
    {

        if (null === $this->tblType) {
            return false;
        } else {
            return Web::useService()->getTypeById($this->tblType);
        }
    }

    /**
     * @param null|TblType $tblType
     */
    public function setTblType(TblType $tblType = null)
    {

        $this->tblType = ( null === $tblType ? null : $tblType->getId() );
    }

    /**
     * @return bool|TblWeb
     */
    public function getTblWeb()
    {

        if (null === $this->tblWeb) {
            return false;
        } else {
            return Web::useService()->getWebById($this->tblWeb);
        }
    }

    /**
     * @param null|TblWeb $tblWeb
     */
    public function setTblWeb(TblWeb $tblWeb = null)
    {

        $this->tblWeb = ( null === $tblWeb ? null : $tblWeb->getId() );
    }
}
