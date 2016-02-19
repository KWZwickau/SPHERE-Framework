<?php
namespace SPHERE\Application\Billing\Accounting\Banking\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBankReference")
 * @Cache(usage="READ_ONLY")
 */
class TblBankReference extends Element
{

    const ATTR_REFERENCE_NUMBER = 'Reference';
    const SERVICE_TBL_PERSON = 'ServicePeople_Person';
    const ATTR_IS_VOID = 'IsVoid';

    /**
     * @Column(type="string")
     */
    protected $Reference;
    /**
     * @Column(type="bigint")
     */
    protected $ServicePeople_Person;
    /**
     * @Column(type="date")
     */
    protected $ReferenceDate;
    /**
     * @Column(type="boolean")
     */
    protected $IsVoid;

    /**
     * @return string $Reference
     */
    public function getReference()
    {

        return $this->Reference;
    }

    /**
     * @param string $Reference
     */
    public function setReference($Reference)
    {

        $this->Reference = $Reference;
    }

    /**
     * @return bool|TblPerson
     */
    public function getServicePeoplePerson()
    {

        if (null === $this->ServicePeople_Person) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->ServicePeople_Person);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServicePeoplePerson(TblPerson $tblPerson = null)
    {

        $this->ServicePeople_Person = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return string
     */
    public function getReferenceDate()
    {

        if (null === $this->ReferenceDate) {
            return false;
        }
        /** @var \DateTime $ReferenceDate */
        $ReferenceDate = $this->ReferenceDate;
        if ($ReferenceDate instanceof \DateTime) {
            return $ReferenceDate->format('d.m.Y');
        } else {
            return (string)$ReferenceDate;
        }
    }
    /**
     * @param \DateTime $ReferenceDate
     */
    public function setReferenceDate(\DateTime $ReferenceDate)
    {

        $this->ReferenceDate = $ReferenceDate;
    }

    /**
     * @return boolean $IsVoid
     */
    public function isVoid()
    {

        return $this->IsVoid;
    }

    /**
     * @param boolean $IsVoid
     */
    public function setVoid($IsVoid)
    {

        $this->IsVoid = $IsVoid;
    }
}
