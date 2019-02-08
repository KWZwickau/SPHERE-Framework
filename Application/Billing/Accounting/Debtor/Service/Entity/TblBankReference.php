<?php
namespace SPHERE\Application\Billing\Accounting\Debtor\Service\Entity;

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

    const ATTR_REFERENCE_NUMBER = 'ReferenceNumber';
    const ATTR_REFERENCE_DATE = 'ReferenceDate';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="string")
     */
    protected $ReferenceNumber;
    /**
     * @Column(type="datetime")
     */
    protected $ReferenceDate;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;


    /**
     * @return string $ReferenceNumber
     */
    public function getReferenceNumber()
    {

        return $this->ReferenceNumber;
    }

    /**
     * @param string $ReferenceNumber
     */
    public function setReference($ReferenceNumber)
    {

        $this->ReferenceNumber = $ReferenceNumber;
    }

    /**
     * @return string
     */
    public function getReferenceDate()
    {

        if(null === $this->ReferenceDate){
            return false;
        }
        /** @var \DateTime $ReferenceDate */
        $ReferenceDate = $this->ReferenceDate;
        if($ReferenceDate instanceof \DateTime){
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
     * @return false|TblPerson
     */
    public function getServiceTblPerson()
    {

        if(null === $this->serviceTblPerson){
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson)
    {

        $this->serviceTblPerson = $tblPerson;
    }
}
