<?php
namespace SPHERE\Application\Billing\Accounting\Basket\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Basket\Basket;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBasketVerification")
 * @Cache(usage="READ_ONLY")
 */
class TblBasketVerification extends Element
{

    const ATTR_TBL_BASKET = 'tblBasket';
    const SERVICE_PEOPLE_PERSON = 'servicePeople_Person';
    const SERVICE_INVENTORY_ITEM = 'serviceInventory_Item';

    /**
     * @Column(type="decimal", precision=14, scale=4)
     */
    protected $Value;
    /**
     * @Column(type="integer")
     */
    protected $Quantity;
    /**
     * @Column(type="bigint")
     */
    protected $tblBasket;
    /**
     * @Column(type="bigint")
     */
    protected $servicePeople_Person;
    /**
     * @Column(type="bigint")
     */
    protected $serviceInventory_Item;

    /**
     * @return (type="decimal", precision=14, scale=4)
     */
    public function getValue()
    {

        return $this->Value;
    }

    /**
     * @param (type="decimal", precision=14, scale=4) $Value
     */
    public function setValue($Value)
    {

        $this->Value = $Value;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {

        return $this->Quantity;
    }

    /**
     * @param int $Quantity
     */
    public function setQuantity($Quantity)
    {

        $this->Quantity = $Quantity;
    }

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
    public function getServicePeoplePerson()
    {

        if (null === $this->servicePeople_Person) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->servicePeople_Person);
        }
    }

    /**
     * @param null|TblPerson $tblPerson
     */
    public function setServicePeoplePerson(TblPerson $tblPerson = null)
    {

        $this->servicePeople_Person = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return bool|TblItem
     */
    public function getServiceInventoryItem()
    {

        if (null === $this->serviceInventory_Item) {
            return false;
        } else {
            return Item::useService()->getItemById($this->serviceInventory_Item);
        }
    }

    /**
     * @param null|TblItem $tblItem
     */
    public function setServiceInventoryItem(TblItem $tblItem = null)
    {

        $this->serviceInventory_Item = ( null === $tblItem ? null : $tblItem->getId() );
    }

    /**
     * @return string
     */
    public function getSinglePrice()
    {

        if ($this->Quantity !== 0) {
            $result = $this->Value / $this->Quantity;
        } else {
            $result = $this->Value;
        }
        return number_format($result, 2).' €';
    }

    /**
     * @return string
     */
    public function getSummaryPrice()
    {

        return number_format($this->Value, 2).' €';
    }


}
