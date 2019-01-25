<?php

namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\Billing\Inventory\Item\Service\Data;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemCalculation;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemAccount;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemGroup;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemType;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemVariant;
use SPHERE\Application\Billing\Inventory\Item\Service\Setup;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Inventory\Item
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
        if(!$doSimulation && $withData){
            (new Data($this->getBinding()))->setupDatabaseContent();
        }

        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return bool|TblItem
     */
    public function getItemById($Id)
    {

        return (new Data($this->getBinding()))->getItemById($Id);
    }

    /**
     * @param $Name
     *
     * @return bool|TblItem
     */
    public function getItemByName($Name)
    {

        return (new Data($this->getBinding()))->getItemByName($Name);
    }

    /**
     * @param $Id
     *
     * @return bool|TblItemGroup
     */
    public function getItemGroupById($Id)
    {

        return (new Data($this->getBinding()))->getItemGroupById($Id);
    }

    /**
     * @param TblItem $tblItem
     *
     * @return bool|TblItemGroup[]
     */
    public function getItemGroupByItem(TblItem $tblItem)
    {

        return (new Data($this->getBinding()))->getItemGroupByItem($tblItem);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblItemVariant
     */
    public function getItemVariantById($Id)
    {

        return (new Data($this->getBinding()))->getItemVariantById($Id);
    }

    /**
     * @param TblItem $tblItem
     *
     * @return bool|TblItemVariant[]
     */
    public function getItemVariantByItem(TblItem $tblItem)
    {

        return (new Data($this->getBinding()))->getItemVariantByItem($tblItem);
    }

    /**
     * @param TblItem $tblItem
     * @param string  $Name
     *
     * @return bool|TblItemVariant
     */
    public function getItemVariantByItemAndName(TblItem $tblItem, $Name)
    {

        return (new Data($this->getBinding()))->getItemVariantByItemAndName($tblItem, $Name);
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return bool|TblItemGroup[]
     */
    public function getItemGroupByGroup(TblGroup $tblGroup)
    {

        return (new Data($this->getBinding()))->getItemGroupByGroup($tblGroup);
    }

    /**
     * @return bool|TblItemGroup[]
     */
    public function getItemGroupAll()
    {

        return (new Data($this->getBinding()))->getItemGroupAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblItemType
     */
    public function getItemTypeById($Id)
    {

        return (new Data($this->getBinding()))->getItemTypeById($Id);
    }

    /**
     * @param $Name
     *
     * @return bool|TblItemType
     */
    public function getItemTypeByName($Name)
    {

        return (new Data($this->getBinding()))->getItemTypeByName($Name);
    }

    /**
     * @return bool|TblItemType[]
     */
    public function getItemTypeAll()
    {

        return (new Data($this->getBinding()))->getItemTypeAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblItemCalculation
     */
    public function getItemCalculationById($Id)
    {

        return (new Data($this->getBinding()))->getItemCalculationById($Id);
    }

    /**
     * @param TblItemVariant $tblItemVariant
     *
     * @return bool|TblItemCalculation[]
     */
    public function getItemCalculationByItemVariant(TblItemVariant $tblItemVariant)
    {

        return (new Data($this->getBinding()))->getItemCalculationByItemVariant($tblItemVariant);
    }

    /**
     * @param TblItemVariant $tblItemVariant
     *
     * @return bool|TblItemCalculation
     */
    public function getItemCalculationNowByItemVariant(TblItemVariant $tblItemVariant)
    {

        $tblItemCalculationActive = false;
        $tblItemCalculationList = (new Data($this->getBinding()))->getItemCalculationByItemVariant($tblItemVariant);
        if($tblItemCalculationList){
            foreach($tblItemCalculationList as $tblItemCalculation) {
                $now = new \DateTime();
                $from = new \DateTime($tblItemCalculation->getDateFrom());
                if(($tblItemCalculation->getDateTo())){
                    $to = new \DateTime($tblItemCalculation->getDateTo());
                } else {
                    $to = false;
                }
                if($from <= $now && $to && $to >= $now){
                    $tblItemCalculationActive = $tblItemCalculation;
                } elseif($from <= $now && !$to && false === $tblItemCalculationActive) {
                    $tblItemCalculationActive = $tblItemCalculation;
                }
            }
        }
        return $tblItemCalculationActive;
    }

    /**
     * @return bool|TblItem[]
     */
    public function getItemAll()
    {

        return (new Data($this->getBinding()))->getItemAll();
    }

    /**
     * @param TblItem $tblItem
     *
     * @return bool|TblItemAccount[]
     */
    public function getItemAccountAllByItem(TblItem $tblItem)
    {

        return (new Data($this->getBinding()))->getItemAccountAllByItem($tblItem);
    }

    /**
     * @param $Id
     *
     * @return bool|TblItemAccount
     */
    public function getItemAccountById($Id)
    {

        return (new Data($this->getBinding()))->getItemAccountById($Id);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return TblItem[]|bool
     */
    public function getItemAllByPerson(TblPerson $tblPerson)
    {

        $ItemList = array();
        if(($tblGroupList = Group::useService()->getGroupAllByPerson($tblPerson))){
            foreach($tblGroupList as $tblGroup) {
                if(($tblItemGroupList = $this->getItemGroupByGroup($tblGroup))){
                    foreach($tblItemGroupList as $tblItemGroup) {
                        if(($tblItem = $tblItemGroup->getTblItem())){
                            $ItemList[$tblItem->getId()] = $tblItem;
                        }
                    }
                }
            }
        }

        return (empty($ItemList) ? false : $ItemList);
    }

    /**
     * @param string $ItemName
     * @param string $Description
     *
     * @return TblItem
     */
    public function createItem($ItemName, $Description = '')
    {

        // ToDO Standard Einzelleistung (later choosable)
        $tblItemType = Item::useService()->getItemTypeByName(TblItemType::TYPE_SINGLE);
        return (new Data($this->getBinding()))->createItem($tblItemType, $ItemName, $Description);
    }

    /**
     * @param TblItem  $tblItem
     * @param TblGroup $tblGroup
     *
     * @return TblItemGroup
     */
    public function createItemGroup(TblItem $tblItem, TblGroup $tblGroup)
    {

        return (new Data($this->getBinding()))->createItemGroup($tblItem, $tblGroup);
    }

    /**
     * @param TblItem $tblItem
     * @param string  $Name
     * @param string  $Description
     *
     * @return TblItemVariant
     */
    public function createItemVariant(TblItem $tblItem, $Name, $Description = '')
    {

        return (new Data($this->getBinding()))->createItemVariant($tblItem, $Name, $Description);
    }

    /**
     * @param TblItemVariant $tblItemVariant
     * @param string         $Value
     * @param string         $DateFrom
     * @param string         $DateTo
     *
     * @return TblItemCalculation
     */
    public function createItemCalculation(TblItemVariant $tblItemVariant, $Value, $DateFrom, $DateTo = '')
    {

        // set Komma as Possible Input
        $Value = str_replace(',', '.', $Value);
        return (new Data($this->getBinding()))->createItemCalculation($tblItemVariant, $Value, $DateFrom, $DateTo);
    }

    /**
     * @param TblItem $tblItem
     * @param         $ItemName
     *
     * @return string
     */
    public function changeItem(TblItem $tblItem, $ItemName)
    {

        return (new Data($this->getBinding()))->updateItem($tblItem, $ItemName, '');
    }

    /**
     * @param TblItemVariant $tblItemVariant
     * @param                $Name
     * @param                $Description
     *
     * @return bool
     */
    public function changeItemVariant(TblItemVariant $tblItemVariant, $Name, $Description)
    {

        return (new Data($this->getBinding()))->updateItemVariant($tblItemVariant, $Name, $Description);
    }


    /**
     * @param TblItemCalculation $tblItemCalculation
     * @param string             $Value
     * @param string             $DateFrom
     * @param string             $DateTo
     *
     * @return bool
     */
    public function changeItemCalculation(TblItemCalculation $tblItemCalculation, $Value, $DateFrom, $DateTo = '')
    {

        return (new Data($this->getBinding()))->updateItemCalculation($tblItemCalculation, $Value, $DateFrom, $DateTo);
    }

    /**
     * @param TblItem $tblItem
     *
     * @return bool
     */
    public function removeItem(TblItem $tblItem)
    {

        // remove tblItemGroup
        if(($tblItemGroupPersonList = $this->getItemGroupByItem($tblItem))){
            foreach($tblItemGroupPersonList as $tblItemGroupPerson) {
                $this->removeItemGroup($tblItemGroupPerson);
            }
        }
        // remove tblItemVariant
        if(($tblItemVariantList = $this->getItemVariantByItem($tblItem))){
            foreach($tblItemVariantList as $tblItemVariant) {
                $this->removeItemVariant($tblItemVariant);
            }
        }
        // remove tblItem
        return (new Data($this->getBinding()))->removeItem($tblItem);
    }

    /**
     * @param TblItemGroup $tblItemGroup
     *
     * @return bool
     */
    public function removeItemGroup(TblItemGroup $tblItemGroup)
    {

        return (new Data($this->getBinding()))->removeItemGroup($tblItemGroup);
    }

    /**
     * @param TblItemVariant $tblItemVariant
     *
     * @return bool
     */
    public function removeItemVariant(TblItemVariant $tblItemVariant)
    {
        // remove tblItemCalculation
        if(($tblItemCalculationList = $this->getItemCalculationByItemVariant($tblItemVariant))){
            foreach($tblItemCalculationList as $tblItemCalculation) {
                $this->removeItemCalculation($tblItemCalculation);
            }
        }

        return (new Data($this->getBinding()))->removeItemVariant($tblItemVariant);
    }

    /**
     * @param TblItemCalculation $tblItemCalculation
     *
     * @return bool
     */
    public function removeItemCalculation(TblItemCalculation $tblItemCalculation)
    {

        return (new Data($this->getBinding()))->removeItemCalculation($tblItemCalculation);
    }

//    /**
//     * @param TblItemAccount $tblItemAccount
//     *
//     * @return string
//     */
//    public function removeItemAccount(TblItemAccount $tblItemAccount)
//    {
//
//        if ((new Data($this->getBinding()))->removeItemAccount($tblItemAccount)) {
//            return new Success('Das FIBU-Konto '.$tblItemAccount->getServiceBillingAccount()->getDescription().' wurde erfolgreich entfernt')
//            .new Redirect('/Billing/Inventory/Commodity/Item/Account/Select', Redirect::TIMEOUT_SUCCESS,
//                array('Id' => $tblItemAccount->getTblItem()->getId()));
//        } else {
//            return new Warning('Das FIBU-Konto '.$tblItemAccount->getServiceBillingAccount()->getDescription().' konnte nicht entfernt werden')
//            .new Redirect('/Billing/Inventory/Commodity/Item/Account/Select', Redirect::TIMEOUT_ERROR,
//                array('Id' => $tblItemAccount->getTblItem()->getId()));
//        }
//    }

//    /**
//     * @param TblItem    $tblItem
//     * @param TblAccount $tblAccount
//     *
//     * @return string
//     */
//    public function addItemToAccount(TblItem $tblItem, TblAccount $tblAccount)
//    {
//
//        if ((new Data($this->getBinding()))->addItemAccount($tblItem, $tblAccount)) {
//            return new Success('Das FIBU-Konto '.$tblAccount->getDescription().' wurde erfolgreich hinzugefügt')
//            .new Redirect('/Billing/Inventory/Commodity/Item/Account/Select', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblItem->getId()));
//        } else {
//            return new Warning('Das FIBU-Konto '.$tblAccount->getDescription().' konnte nicht entfernt werden')
//            .new Redirect('/Billing/Inventory/Commodity/Item/Account/Select', Redirect::TIMEOUT_ERROR, array('Id' => $tblItem->getId()));
//        }
//    }
}
