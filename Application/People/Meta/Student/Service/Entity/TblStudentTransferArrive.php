<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

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
 * @Table(name="tblStudentTransferArrive")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentTransferArrive extends Element
{

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCompany;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblType;
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
     * @param TblType|null $tblType
     */
    public function setServiceTblType(TblType $tblType = null)
    {

        $this->serviceTblType = ( null === $tblType ? null : $tblType->getId() );
    }
}
