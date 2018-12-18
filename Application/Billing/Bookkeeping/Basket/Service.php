<?php

namespace SPHERE\Application\Billing\Bookkeeping\Basket;

use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblDebtorSelection;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Data;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketItem;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Setup;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Bookkeeping\Basket
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
     * @param $Id
     *
     * @return bool|TblBasket
     */
    public function getBasketById($Id)
    {

        return (new Data($this->getBinding()))->getBasketById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketItem
     */
    public function getBasketItemById($Id)
    {

        return (new Data($this->getBinding()))->getBasketItemById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblBasketVerification
     */
    public function getBasketVerificationById($Id)
    {

        return (new Data($this->getBinding()))->getBasketVerificationById($Id);
    }

    /**
     * @return bool|TblBasket[]
     */
    public function getBasketAll()
    {

        return (new Data($this->getBinding()))->getBasketAll();
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblItem[]
     */
    public function getItemAllByBasket(TblBasket $tblBasket)
    {

        $tblBasketItemList = $this->getBasketItemAllByBasket($tblBasket);
        $tblItemList = array();
        if ($tblBasketItemList) {
            foreach ($tblBasketItemList as $tblBasketItem) {
                if(($tblItem = $tblBasketItem->getServiceTblItem())){
                    $tblItemList[] = $tblItem;
                }
            }

        }
        return ( empty( $tblItemList ) ? false : $tblItemList );
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblBasketItem[]
     */
    public function getBasketItemAllByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getBasketItemAllByBasket($tblBasket);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return false|TblBasketItem[]
     */
    public function getBasketItemByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getBasketItemByBasket($tblBasket);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return false|TblBasketVerification[]
     */
    public function getBasketVerificationByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getBasketVerificationByBasket($tblBasket);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return false|\SPHERE\System\Database\Fitting\Element
     */
    public function countDebtorSelectionCountByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->countDebtorSelectionCountByBasket($tblBasket);
    }

    /**
     * @param string $Name
     * @param string $Desctiption
     *
     * @return string
     */
    public function createBasket($Name = '', $Desctiption = '')
    {

        return (new Data($this->getBinding()))->createBasket($Name, $Desctiption);

        //ToDO in API
        /**
         * Skip to Frontend
         */
        if (null === $Basket) {
            return $this->form();
        }
        $Error = false;
        if (isset( $Basket['Name'] ) && empty( $Basket['Name'] )) {
            $Stage->setError('Basket[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblItem   $tblItem
     *
     * @return string
     */
    public function createBasketItem(TblBasket $tblBasket, TblItem $tblItem)
    {
        //ToDO Kontrolle
        return (new Data($this->getBinding()))->createBasketItem($tblBasket, $tblItem);
    }

    /**
     * @param string $BasketId
     *
     * @return bool
     */
    public function createBasketVerificationBulk($BasketId = '')
    {

        if(($tblBasket = Basket::useService()->getBasketById($BasketId))){
            if(($tblItemList = Basket::useService()->getItemAllByBasket($tblBasket))){
                $PersonList = array();
                foreach($tblItemList as $tblItem){
                    // Find all Person who matched on this Item (Debtorselection / Zahlungszuweisung)
                    if(($tblDebtorSelectionList = Debtor::useService()->getDebtorSelectionByItem($tblItem))){
                        array_walk($tblDebtorSelectionList, function(TblDebtorSelection $tblDebtorSelection) use(&$PersonList) {
                            //ToDO create BulkSaveList
                            $tblDebtorSelection->getServiceTblPerson();
                        });
                    }

                }
            }
        }

        //ToDO return Boolean from Create
        return true;
    }

    /**
     * @param TblBasket      $tblBasket
     * @param                $Basket
     *
     * @return IFormInterface|string
     */
    public function changeBasket(TblBasket $tblBasket, $Basket)
    {

        return (new Data($this->getBinding()))->updateBasket($tblBasket, $Basket['Name'], $Basket['Description']);
        // ToDO move Check to API
        /**
         * Skip to Frontend
         */
        if (null === $Basket
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $Basket['Name'] ) && empty( $Basket['Name'] )) {
            $Stage->setError('Basket[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     * @param array $Varification
     *
     * @return bool
     */
    public function changeBasketVerification(TblBasketVerification $tblBasketVerification, $Varification)
    {

        return (new Data($this->getBinding()))->updateBasketVerification($tblBasketVerification, $Varification['Price']
            , $Varification['Quantity']);
        //ToDO move to API
        /**
         * Skip to Frontend
         */
        if (null === $Varification
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $Varification['Price'] ) && empty( $Varification['Price'] )) {
            $Stage->setError('Varification[Price]', 'Bitte geben Sie einen Preis an');
            $Error = true;
        } else {
            $Varification['Price'] = str_replace(',', '.', $Varification['Price']);
            if (!is_numeric($Varification['Price']) || $Varification['Price'] < 0) {
                $Stage->setError('Varification[Price]', 'Bitte geben Sie eine Natürliche Zahl an');
                $Error = true;
            }
        }
        if (isset( $Varification['Quantity'] ) && empty( $Varification['Quantity'] )) {
            $Stage->setError('Varification[Quantity]', 'Bitte geben Sie eine Anzahl an');
            $Error = true;
        } else {
            $Varification['Quantity'] = round(str_replace(',', '.', $Varification['Quantity']), 0);
            if (!is_numeric($Varification['Quantity']) || $Varification['Quantity'] < 1) {
                $Stage->setError('Varification[Quantity]', 'Bitte geben Sie eine Natürliche Zahl an');
                $Error = true;
            }
        }
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function destroyBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->destroyBasket($tblBasket);
    }

    /**
     * @param TblBasketItem $tblBasketItem
     *
     * @return string
     */
    public function destroyBasketItem(TblBasketItem $tblBasketItem)
    {
        //ToDO Kontrolle
        return (new Data($this->getBinding()))->destroyBasketItem($tblBasketItem);
    }

    /**
     * @param TblBasketVerification $tblBasketVerification
     *
     * @return string
     */
    public function destroyBasketVerification(TblBasketVerification $tblBasketVerification)
    {
        //ToDO Kontrolle
        return (new Data($this->getBinding()))->destroyBasketVerification($tblBasketVerification);
    }
}
