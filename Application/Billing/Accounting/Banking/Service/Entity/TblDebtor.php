<?php
namespace SPHERE\Application\Billing\Accounting\Banking\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblDebtor")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblDebtor extends Element
{

    const ATTR_DEBTOR_NUMBER = 'DebtorNumber';
    const ATTR_SERVICE_MANAGEMENT_PERSON = 'ServiceManagementPerson';

    /**
     * @Column(type="string")
     */
    protected $DebtorNumber;
    /**
     * @Column(type="string")
     */
    protected $Description;
    /**
     * @Column(type="bigint")
     */
    protected $ServiceManagementPerson;
    /**
     * @Column(type="bigint")
     */
    protected $tblPaymentType;

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
     * @return string $Description
     */
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }

    /**
     * @return bool|TblPerson $ServiceManagementPerson
     */
    public function getServiceManagementPerson()
    {

        if (null === $this->ServiceManagementPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->ServiceManagementPerson);
        }
    }

    /**
     * @param null|TblPerson $ServiceManagementPerson
     */
    public function setServiceManagementPerson(TblPerson $ServiceManagementPerson)
    {

        $this->ServiceManagementPerson = ( null === $ServiceManagementPerson ? null : $ServiceManagementPerson->getId() );
    }

    /**
     * @return TblPaymentType $tblPaymentType
     */
    public function getPaymentType()
    {

        if (null === $this->tblPaymentType) {
            return false;
        } else {
            return Banking::useService()->getPaymentTypeById($this->tblPaymentType);
        }
    }

    /**
     * @param TblPaymentType $PaymentType
     */
    public function setPaymentType(TblPaymentType $PaymentType)
    {

        $this->tblPaymentType = ( null === $PaymentType ? null : $PaymentType->getId() );
    }

}
