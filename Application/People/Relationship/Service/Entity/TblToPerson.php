<?php
namespace SPHERE\Application\People\Relationship\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblToPerson")
 * @Cache(usage="READ_ONLY")
 */
class TblToPerson extends Element
{

    const ATTR_TBL_TYPE = 'tblType';
    const SERVICE_TBL_PERSON_FROM = 'serviceTblPersonFrom';
    const SERVICE_TBL_PERSON_TO = 'serviceTblPersonTo';

    /**
     * @Column(type="text")
     */
    protected $Remark;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPersonFrom;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPersonTo;
    /**
     * @Column(type="bigint")
     */
    protected $tblType;

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPersonFrom()
    {

        if (null === $this->serviceTblPersonFrom) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPersonFrom);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPersonFrom(TblPerson $tblPerson = null)
    {

        $this->serviceTblPersonFrom = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPersonTo()
    {

        if (null === $this->serviceTblPersonTo) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPersonTo);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPersonTo(TblPerson $tblPerson = null)
    {

        $this->serviceTblPersonTo = ( null === $tblPerson ? null : $tblPerson->getId() );
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
            return Relationship::useService()->getTypeById($this->tblType);
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
