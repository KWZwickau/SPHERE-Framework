<?php
namespace SPHERE\Application\Billing\Accounting\Basket\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Basket\Basket;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBasketCommodity")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblBasketCommodity extends Element
{

    const ATTR_TBL_BASKET = 'tblBasket';
    const ATTR_SERVICE_MANAGEMENT_PERSON = 'serviceManagement_Person';
    const ATTR_SERVICE_BILLING_COMMODITY = 'serviceBilling_Commodity';

    /**
     * @Column(type="bigint")
     */
    protected $tblBasket;
    /**
     * @Column(type="bigint")
     */
    protected $serviceManagement_Person;
    /**
     * @Column(type="bigint")
     */
    protected $serviceBilling_Commodity;

    /**
     * @return bool|TblBasket
     */
    public function getTblBasket()
    {

        if (null === $this->tblBasket) {
            return false;
        } else {
            return Basket::useService()->getBasketById($this->tblBasket);
        }
    }

    /**
     * @param null|TblBasket $tblBasket
     */
    public function setTblBasket($tblBasket = null)
    {

        $this->tblBasket = ( null === $tblBasket ? null : $tblBasket->getId() );
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
     * @return bool|TblCommodity
     */
    public function getServiceBillingCommodity()
    {

        if (null === $this->serviceBilling_Commodity) {
            return false;
        } else {
            return Commodity::useService()->getCommodityById($this->serviceBilling_Commodity);
        }
    }

    /**
     * @param null|TblCommodity $tblCommodity
     */
    public function setServiceBillingCommodity($tblCommodity = null)
    {

        $this->serviceBilling_Commodity = ( null === $tblCommodity ? null : $tblCommodity->getId() );
    }
}
