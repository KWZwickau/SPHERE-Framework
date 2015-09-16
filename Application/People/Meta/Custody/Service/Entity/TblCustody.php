<?php
namespace SPHERE\Application\People\Meta\Custody\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCustody")
 * @Cache(usage="READ_ONLY")
 */
class TblCustody extends Element
{

    const SERVICE_TBL_PERSON = 'serviceTblPerson';
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $Occupation;
    /**
     * @Column(type="string")
     */
    protected $Employment;
    /**
     * @Column(type="text")
     */
    protected $Remark;

    /**
     * @return string
     */
    public function getOccupation()
    {

        return $this->Occupation;
    }

    /**
     * @param string $Occupation
     */
    public function setOccupation($Occupation)
    {

        $this->Occupation = $Occupation;
    }

    /**
     * @return string
     */
    public function getEmployment()
    {

        return $this->Employment;
    }

    /**
     * @param string $Employment
     */
    public function setEmployment($Employment)
    {

        $this->Employment = $Employment;
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
}
