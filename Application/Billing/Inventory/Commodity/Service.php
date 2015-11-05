<?php
namespace SPHERE\Application\Billing\Inventory\Commodity;

use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Accounting\Basket\Basket;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Data;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodityItem;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodityType;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Setup;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Billing\Inventory\Commodity
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }

        return $Protocol;
    }

    /**
     * @return bool|TblCommodity[]
     */
    public function getCommodityAll()
    {

        return (new Data($this->getBinding()))->getCommodityAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblCommodity
     */
    public function getCommodityById($Id)
    {

        return (new Data($this->getBinding()))->getCommodityById($Id);
    }

    /**
     * @param TblCommodity $tblCommodity
     *
     * @return int
     */
    public function countItemAllByCommodity(TblCommodity $tblCommodity)
    {

        return (new Data($this->getBinding()))->countItemAllByCommodity($tblCommodity);
    }

    /**
     * @param TblCommodity $tblCommodity
     *
     * @return string
     */
    public function sumPriceItemAllByCommodity(TblCommodity $tblCommodity)
    {

        return (new Data($this->getBinding()))->sumPriceItemAllByCommodity($tblCommodity);
    }

    /**
     * @return bool|TblCommodityType[]
     */
    public function getCommodityTypeAll()
    {

        return (new Data($this->getBinding()))->getCommodityTypeAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblCommodityItem
     */
    public function getCommodityItemById($Id)
    {

        return (new Data($this->getBinding()))->getCommodityItemById($Id);
    }

    /**
     * @param TblCommodity $tblCommodity
     *
     * @return bool|TblItem[]
     */
    public function getItemAllByCommodity(TblCommodity $tblCommodity)
    {

        return (new Data($this->getBinding()))->getItemAllByCommodity($tblCommodity);
    }

    /**
     * @param TblItem $tblItem
     *
     * @return bool|TblItem[]
     */
    public function getCommodityItemAllByItem(TblItem $tblItem)
    {

        return (new Data($this->getBinding()))->getCommodityItemAllByItem($tblItem);
    }

    /**
     * @param TblItem $tblItem
     *
     * @return TblAccount[]
     */
    public function getAccountAllByItem(TblItem $tblItem)
    {

        return (new Data($this->getBinding()))->getAccountAllByItem($tblItem);
    }

    /**
     * @param $Name
     *
     * @return bool|TblCommodity
     */
    public function getCommodityByName($Name)
    {

        return (new Data($this->getBinding()))->getCommodityByName($Name);
    }

    /**
     * @param IFormInterface $Stage
     * @param                $Commodity
     *
     * @return IFormInterface|string
     */
    public function createCommodity(IFormInterface &$Stage = null, $Commodity)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Commodity
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $Commodity['Name'] ) && empty( $Commodity['Name'] )) {
            $Stage->setError('Commodity[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        } else {
            if (isset( $Commodity['Name'] ) && (new Data($this->getBinding()))->getCommodityByName($Commodity['Name'])) {
                $Stage->setError('Commodity[Name]', 'Die Leistung exisitiert bereits.
                Bitte geben Sie eine anderen Name an');
                $Error = true;
            }
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createCommodity(
                $Commodity['Name'],
                $Commodity['Description'],
                $this->getCommodityTypeById($Commodity['Type'])
            );
            return new Success('Die Leistung wurde erfolgreich angelegt')
            .new Redirect('/Billing/Inventory/Commodity', 1);
        }
        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return bool|TblCommodityType
     */
    public function getCommodityTypeById($Id)
    {

        return (new Data($this->getBinding()))->getCommodityTypeById($Id);
    }

    /**
     * @param IFormInterface $Stage
     * @param TblCommodity   $tblCommodity
     * @param                $Commodity
     *
     * @return IFormInterface|string
     */
    public function changeCommodity(IFormInterface &$Stage = null, TblCommodity $tblCommodity, $Commodity)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Commodity
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $Commodity['Name'] ) && empty( $Commodity['Name'] )) {
            $Stage->setError('Commodity[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        } else {
            if (isset( $Commodity['Name'] ) && $tblCommodity->getName() !== $Commodity['Name']
                && (new Data($this->getBinding()))->getCommodityByName($Commodity['Name'])
            ) {
                $Stage->setError('Commodity[Name]', 'Die Leistung exisitiert bereits.
                Bitte geben Sie eine anderen Name an');
                $Error = true;
            }
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updateCommodity(
                $tblCommodity,
                $Commodity['Name'],
                $Commodity['Description'],
                $this->getCommodityTypeById($Commodity['Type'])
            )
            ) {
                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    .new Redirect('/Billing/Inventory/Commodity', 1);
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden');
            };
        }
        return $Stage;
    }

    /**
     * @param TblCommodity $tblCommodity
     *
     * @return string
     */
    public function destroyCommodity(TblCommodity $tblCommodity)
    {

        if (null === $tblCommodity) {
            return '';
        }
        $tblCommodityItemList = Commodity::useService()->getCommodityItemAllByCommodity($tblCommodity);
        /** @var TblCommodityItem $tblCommodityItem */
        foreach ($tblCommodityItemList as $tblCommodityItem) {
            $tblBasketItemList = Basket::useService()->getBasketItemAllByCommodityItem($tblCommodityItem);
            foreach ($tblBasketItemList as $tblBasketItem) {
                Basket::useService()->removeBasketItem($tblBasketItem);
            }
        }

        if ((new Data($this->getBinding()))->destroyCommodity($tblCommodity)) {
            return new Success('Die Leistung wurde erfolgreich gelöscht')
            .new Redirect('/Billing/Inventory/Commodity', 1);
        } else {
            return new Danger('Die Leistung konnte nicht gelöscht werden')
            .new Redirect('/Billing/Inventory/Commodity', 3);
        }
    }

    /**
     * @param TblCommodity $tblCommodity
     *
     * @return bool|TblItem[]
     */
    public function getCommodityItemAllByCommodity(TblCommodity $tblCommodity)
    {

        return (new Data($this->getBinding()))->getCommodityItemAllByCommodity($tblCommodity);
    }

    /**
     * @param TblCommodity $tblCommodity
     * @param TblItem      $tblItem
     * @param              $Item
     *
     * @return string
     */
    public function addItemToCommodity(TblCommodity $tblCommodity, TblItem $tblItem, $Item)
    {

        if ($Item['Quantity'] == null) {
            $Item['Quantity'] = 1;
        }

        if ((new Data($this->getBinding()))->addItemToCommodity($tblCommodity, $tblItem, $Item['Quantity'])) {
            return new Success('Der Artikel '.$tblItem->getName().' wurde erfolgreich hinzugefügt')
            .new Redirect('/Billing/Inventory/Commodity/Item/Select', 1, array('Id' => $tblCommodity->getId()));
        } else {
            return new Warning('Der Artikel '.$tblItem->getName().' konnte nicht entfernt werden')
            .new Redirect('/Billing/Inventory/Commodity/Item/Select', 3, array('Id' => $tblCommodity->getId()));
        }
    }

    /**
     * @param TblCommodityItem $tblCommodityItem
     *
     * @return string
     */
    public function removeItemToCommodity(TblCommodityItem $tblCommodityItem)
    {

        $Error = false;
        $tblBasketList = Basket::useService()->getBasketAll();
        foreach ($tblBasketList as $tblBasket) {
            $tblBasketItemList = Basket::useService()->getBasketItemAllByBasket($tblBasket);
            foreach ($tblBasketItemList as $tblBasketItem) {
                if ($tblBasketItem->getServiceBillingCommodityItem()->getId() === $tblCommodityItem->getId()) {
                    $Error = true;
                }
            }
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->removeItemToCommodity($tblCommodityItem)) {
                return new Success('Der Artikel '.$tblCommodityItem->getTblItem()->getName().' wurde erfolgreich entfernt')
                .new Redirect('/Billing/Inventory/Commodity/Item/Select', 1,
                    array('Id' => $tblCommodityItem->getTblCommodity()->getId()));
            } else {
                return new Warning('Der Artikel '.$tblCommodityItem->getTblItem()->getName().' konnte nicht entfernt werden')
                .new Redirect('/Billing/Inventory/Commodity/Item/Select', 3,
                    array('Id' => $tblCommodityItem->getTblCommodity()->getId()));
            }
        }
        return new Warning('Der Artikel '.$tblCommodityItem->getTblItem()->getName().' konnte nicht entfernt werden da er im Warenkorb benutzt wird')
        .new Redirect('/Billing/Inventory/Commodity/Item/Select', 3,
            array('Id' => $tblCommodityItem->getTblCommodity()->getId()));
    }
}
