<?php
namespace SPHERE\Application\Billing\Accounting\Basket\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Basket\Basket;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodityItem;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBasketItem")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblBasketItem extends Element
{

    const ATTR_TBL_BASKET = 'tblBasket';
    const ATTR_SERVICE_BILLING_COMMODITY_ITEM = 'serviceBilling_CommodityItem';

    /**
     * @Column(type="bigint")
     */
    protected $serviceBilling_CommodityItem;
    /**
     * @Column(type="bigint")
     */
    protected $tblBasket;
    /**
     * @Column(type="decimal", precision=14, scale=4)
     */
    protected $Quantity;
    /**
     * @Column(type="decimal", precision=14, scale=4)
     */
    protected $Price;

    /**
     * @return string
     */
    public function getTotalPriceString()
    {

//        $result = 0.00;       // ToDo erasable?
//        $tblCommodityItem = $this->getServiceBillingCommodityItem();
//        if ($tblCommodityItem) {
//            $tblItem = $this->getServiceBillingCommodityItem()->getTblItem();
//            $quantity = $this->getQuantity();
//            if ($tblItem && $tblItem->getPrice() > 0 && $quantity > 0) {
//                $result = sprintf("%01.4f", $tblItem->getPrice() * $quantity);
//            }
//        }
//        return str_replace('.', ',', $result)." €";
        $result = 0.00;
        $ItemPrice = $this->getPrice();
        if ($ItemPrice) {
            $quantity = $this->getQuantity();
            if ($ItemPrice > 0 && $quantity > 0) {
                $result = sprintf("%01.4f", $ItemPrice * $quantity);
            }
        }
        return str_replace('.', ',', $result)." €";
    }

    /**
     * @return (type="decimal", precision=14, scale=4)
     */
    public function getPrice()
    {

        return $this->Price;
    }

    /**
     * @param (type="decimal", precision=14, scale=4) $Price
     */
    public function setPrice($Price)
    {

        $this->Price = $Price;
    }

    /**
     * @return (type="decimal", precision=14, scale=4)
     */
    public function getQuantity()
    {

        return $this->Quantity;
    }

    /**
     * @param (type="decimal", precision=14, scale=4) $Quantity
     */
    public function setQuantity($Quantity)
    {

        $this->Quantity = $Quantity;
    }

    /**
     * @return string
     */
    public function getPriceString()
    {

        $result = sprintf("%01.4f", $this->Price);
        return str_replace('.', ',', $result)." €";
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
     * @return bool|TblCommodityItem
     */
    public function getServiceBillingCommodityItem()
    {

        if (null === $this->serviceBilling_CommodityItem) {
            return false;
        } else {
            return Commodity::useService()->getCommodityItemById($this->serviceBilling_CommodityItem);
        }
    }

    /**
     * @param null|TblCommodityItem $serviceBilling_CommodityItem
     */
    public function setServiceBillingCommodityItem($serviceBilling_CommodityItem = null)
    {

        $this->serviceBilling_CommodityItem = ( null === $serviceBilling_CommodityItem ? null : $serviceBilling_CommodityItem->getId() );
    }
}
