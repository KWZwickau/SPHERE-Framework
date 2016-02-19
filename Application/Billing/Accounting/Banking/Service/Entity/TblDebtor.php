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
 * @Table(name="tblDebtor")
 * @Cache(usage="READ_ONLY")
 */
class TblDebtor extends Element
{

    const ATTR_DEBTOR_NUMBER = 'DebtorNumber';
    const SERVICE_TBL_PERSON = 'ServicePeople_Person';

    /**
     * @Column(type="string")
     */
    protected $DebtorNumber;
    /**
     * @Column(type="bigint")
     */
    protected $ServicePeople_Person;

    /**
     * @return string $DebtorNumber
     */
    public function getDebtorNumber()
    {

        return $this->DebtorNumber;
    }

    /**
     * @param string $DebtorNumber
     */
    public function setDebtorNumber($DebtorNumber)
    {

        $this->DebtorNumber = $DebtorNumber;
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
}
