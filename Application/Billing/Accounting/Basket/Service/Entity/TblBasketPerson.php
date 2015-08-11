<?php
namespace SPHERE\Application\Billing\Accounting\Basket\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Basket\Basket;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBasketPerson")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblBasketPerson extends Element
{

    const ATTR_TBL_Basket = 'tblBasket';
    const ATTR_SERVICE_MANAGEMENT_PERSON = 'serviceManagement_Person';

    /**
     * @Column(type="bigint")
     */
    protected $serviceManagement_Person;

    /**
     * @Column(type="bigint")
     */
    protected $tblBasket;

    /**
     * @param null|TblBasket $tblBasket
     */
    public function setTblBasket( $tblBasket = null )
    {

        $this->tblBasket = ( null === $tblBasket ? null : $tblBasket->getId() );
    }

    /**
     * @return bool|TblBasket
     */
    public function getTblBasket()
    {

        if ( null === $this->tblBasket ) {
            return false;
        } else {
            return Basket::useService()->entityBasketById( $this->tblBasket );
        }
    }

    /**
     * @param null|TblPerson $serviceManagement_Person
     */
    public function setServiceManagementPerson( $serviceManagement_Person = null )
    {

        $this->serviceManagement_Person = ( null === $serviceManagement_Person ? null : $serviceManagement_Person->getId() );
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceManagementPerson()
    {

        if ( null === $this->serviceManagement_Person ) {
            return false;
        } else {
            return Management::servicePerson()->entityPersonById( $this->serviceManagement_Person );
        }
    }
}
