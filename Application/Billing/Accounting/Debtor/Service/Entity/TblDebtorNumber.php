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
 * @Table(name="tblDebtorNumber")
 * @Cache(usage="READ_ONLY")
 */
class TblDebtorNumber extends Element
{

    const ATTR_DEBTOR_NUMBER = 'DebtorNumber';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="string")
     */
    protected $DebtorNumber;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

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
    public function getServiceTblPerson()
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson $serviceTblPerson
     */
    public function setServiceTblPerson($serviceTblPerson)
    {

        $this->serviceTblPerson = ( null === $serviceTblPerson ? null : $serviceTblPerson->getId() );
    }
}
