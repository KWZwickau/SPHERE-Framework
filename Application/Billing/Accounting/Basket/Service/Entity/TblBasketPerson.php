<?php
namespace SPHERE\Application\Billing\Accounting\Basket\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Basket\Basket;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBasketPerson")
 * @Cache(usage="READ_ONLY")
 */
class TblBasketPerson extends Element
{

    const ATTR_TBL_BASKET = 'tblBasket';
    const SERVICE_PEOPLE_PERSON = 'servicePeople_Person';

    /**
     * @Column(type="bigint")
     */
    protected $servicePeople_Person;
    /**
     * @Column(type="bigint")
     */
    protected $tblBasket;

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
    public function getServicePeople_Person()
    {

        if (null === $this->servicePeople_Person) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->servicePeople_Person);
        }
    }

    /**
     * @param TblPerson|null $servicePeople_Person
     */
    public function setServicePeople_Person(TblPerson $servicePeople_Person = null)
    {

        $this->servicePeople_Person = ( null === $servicePeople_Person ? null : $servicePeople_Person->getId() );
    }
}
