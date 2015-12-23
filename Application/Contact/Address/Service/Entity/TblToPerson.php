<?php
namespace SPHERE\Application\Contact\Address\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblToPerson")
 * @Cache(usage="READ_ONLY")
 */
class TblToPerson extends Element
{

    const ATT_TBL_TYPE = 'tblType';
    const ATT_TBL_ADDRESS = 'tblAddress';
    const SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="text")
     */
    protected $Remark;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $tblType;
    /**
     * @Column(type="bigint")
     */
    protected $tblAddress;

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
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
            return Address::useService()->getTypeById($this->tblType);
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
     * @return bool|TblAddress
     */
    public function getTblAddress()
    {

        if (null === $this->tblAddress) {
            return false;
        } else {
            return Address::useService()->getAddressById($this->tblAddress);
        }
    }

    /**
     * @param null|TblAddress $tblAddress
     */
    public function setTblAddress(TblAddress $tblAddress = null)
    {

        $this->tblAddress = ( null === $tblAddress ? null : $tblAddress->getId() );
    }
}
