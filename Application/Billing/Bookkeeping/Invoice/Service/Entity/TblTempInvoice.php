<?php
namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Accounting\Basket\Basket;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasket;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblTempInvoice")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblTempInvoice extends Element
{

    const ATTR_SERVICE_BILLING_BASKET = 'serviceBilling_Basket';
    const ATTR_SERVICE_MANAGEMENT_PERSON = 'serviceManagement_Person';
    const ATTR_SERVICE_BILLING_DEBTOR = 'serviceBilling_Debtor';

    /**
     * @Column(type="bigint")
     */
    protected $serviceBilling_Basket;
    /**
     * @Column(type="bigint")
     */
    protected $serviceManagement_Person;
    /**
     * @Column(type="bigint")
     */
    protected $serviceBilling_Debtor;

    /**
     * @return bool|TblBasket
     */
    public function getServiceBillingBasket()
    {

        if (null === $this->serviceBilling_Basket) {
            return false;
        } else {
            return Basket::useService()->getBasketById($this->serviceBilling_Basket);
        }
    }

    /**
     * @param TblBasket $tblBasket
     */
    public function setServiceBillingBasket(TblBasket $tblBasket = null)
    {

        $this->serviceBilling_Basket = ( null === $tblBasket ? null : $tblBasket->getId() );
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceManagementPerson()
    {

        if (null === $this->serviceManagement_Person) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceManagement_Person);
        }
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function setServiceManagementPerson(TblPerson $tblPerson = null)
    {

        $this->serviceManagement_Person = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblDebtor
     */
    public function getServiceBillingDebtor()
    {

        if (null === $this->serviceBilling_Debtor) {
            return false;
        } else {
            return Banking::useService()->getDebtorById($this->serviceBilling_Debtor);
        }
    }

    /**
     * @param TblDebtor $tblDebtor
     */
    public function setServiceBillingDebtor(TblDebtor $tblDebtor = null)
    {

        $this->serviceBilling_Debtor = ( null === $tblDebtor ? null : $tblDebtor->getId() );
    }
}
