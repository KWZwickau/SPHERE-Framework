<?php

namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Inventory\Item\Service\Data;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemCalculation;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemAccount;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemGroup;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemType;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemVariant;
use SPHERE\Application\Billing\Inventory\Item\Service\Setup;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
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
        if (!$doSimulation && $withData) {
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
     * @return bool|TblItemGroup
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
    public function getCalculationById($Id)
    {

        return (new Data($this->getBinding()))->getCalculationById($Id);
    }

    /**
     * @param TblItemVariant $tblItemVariant
     *
     * @return bool|TblItemCalculation[]
     */
    public function getItemCalculationByItem(TblItemVariant $tblItemVariant)
    {

        return (new Data($this->getBinding()))->getItemCalculationByItem($tblItemVariant);
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
     * @param TblItemCalculation $tblCalculation
     * @param TblItem        $tblItem
     *
     * @return bool
     */
    public function destroyCalculation(TblItemCalculation $tblCalculation, TblItem $tblItem)
    {

        if (null === $tblCalculation) {
            return false;
        }

        if ((new Data($this->getBinding()))->destroyCalculation($tblCalculation, $tblItem)) {
            return true;
        }
        return false;
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
     * @param $Name
     * @param $Description
     *
     * @return bool
     */
    public function changeItemVariant(TblItemVariant $tblItemVariant, $Name, $Description)
    {

        return (new Data($this->getBinding()))->updateItemVariant($tblItemVariant, $Name, $Description);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblItem             $tblItem
     * @param TblItemCalculation      $tblCalculation
     * @param array               $Calculation
     *
     * @return IFormInterface|string
     */
    public function changeCalculation(
        IFormInterface &$Stage = null,
        TblItem $tblItem,
        TblItemCalculation $tblCalculation,
        $Calculation
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Calculation
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $Calculation['Value'] ) && empty( $Calculation['Value'] )) {
            $Stage->setError('Calculation[Value]', 'Bitte geben Sie einen Artikel-Preis an');
            $Error = true;
        } else {
            $Calculation['Value'] = str_replace(',', '.', $Calculation['Value']);
            if (!is_numeric($Calculation['Value']) || $Calculation['Value'] < 0) {
                $Stage->setError('Calculation[Value]', 'Bitte geben Sie eine Natürliche Zahl an');
                $Error = true;
            }
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updateCalculation(
                $tblCalculation,
                $Calculation['Value']
            )
            ) {
                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    .new Redirect('/Billing/Inventory/Item/Calculation', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $tblItem->getId()));
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden')
                    .new Redirect('/Billing/Inventory/Item/Calculation', Redirect::TIMEOUT_ERROR,
                        array('Id' => $tblItem->getId()));
            };
        }
        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblItem             $tblItem
     * @param array               $Calculation
     *
     * @return IFormInterface|string
     */
    public function createCalculation(IFormInterface &$Stage = null, TblItem $tblItem, $Calculation)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Calculation
        ) {
            return $Stage;
        }
        $Error = false;

        if (isset( $Calculation['Value'] ) && empty( $Calculation['Value'] )) {
            $Stage->setError('Calculation[Value]', 'Bitte geben Sie einen Artikel-Preis an');
            $Error = true;
        } else {
            $Calculation['Value'] = str_replace(',', '.', $Calculation['Value']);
            if (!is_numeric($Calculation['Value']) || $Calculation['Value'] < 0) {
                $Stage->setError('Calculation[Value]', 'Bitte geben Sie eine Natürliche Zahl an');
                $Error = true;
            }
        }

//        if (!isset( $Calculation['SchoolType'] ) && !isset( $Calculation['SiblingRank'] )) {
//            $Calculation['SchoolType'] = '0';
//            $Calculation['SiblingRank'] = '0';
//        }

        if ($this->existsCalculation($tblItem, $Calculation['SchoolType'], $Calculation['SiblingRank'])) {
            $Stage->setError('Calculation[SchoolType]', 'Bedingungskombination vorhanden');
            $Stage->setError('Calculation[SiblingRank]', 'Bedingungskombination vorhanden');
            $Error = true;
        }

        if (!$Error) {
            $tblType = Type::useService()->getTypeById($Calculation['SchoolType']);
            if (!$tblType) {
                $tblType = null;
            }
            $tblSiblingRank = Relationship::useService()->getSiblingRankById($Calculation['SiblingRank']);
            if (!$tblSiblingRank) {
                $tblSiblingRank = null;
            }
            $tblCalculation = (new Data($this->getBinding()))->createCalculation(
                $Calculation['Value'],
                $tblType,
                $tblSiblingRank);
            if ($tblCalculation) {
                (new Data($this->getBinding()))->createItemCalculation($tblItem, $tblCalculation);
                $Stage .= new Success('Gespeichert, die Daten werden neu geladen...')
                    .new Redirect('/Billing/Inventory/Item/Calculation', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $tblItem->getId()));

            } else {
                $Stage .= new Danger('Es konnten nicht gespeichert werden.
                                        Möglicherweise gibt es die Bedingungskombination schon')
                    .new Redirect('/Billing/Inventory/Item/Calculation', Redirect::TIMEOUT_ERROR,
                        array('Id' => $tblItem->getId()));
            };
        }
        return $Stage;
    }

    /**
     * @param TblItem $tblItem
     * @param         $SchoolType
     * @param         $SiblingRank
     *
     * @return bool|TblItemCalculation
     */
    public function existsCalculation(TblItem $tblItem, $SchoolType, $SiblingRank)
    {

        return false;
        return (new Data($this->getBinding()))->existsCalculation($tblItem, $SchoolType, $SiblingRank);
    }

    /**
     * @param $Name
     *
     * @return bool|TblItem
     */
    public function existsItem($Name)
    {

        return (new Data($this->getBinding()))->existsItem($Name);
    }

    /**
     * @param TblItem $tblItem
     *
     * @return string
     */
    public function removeItem(TblItem $tblItem)
    {

        // remove tblItemGroup link
        if(($tblItemGroupPersonList = $this->getItemGroupByItem($tblItem))){
            foreach($tblItemGroupPersonList as $tblItemGroupPerson){
                $this->removeItemGroup($tblItemGroupPerson);
            }
        }
        // remove tblItemVariant link
        if(($tblItemVariantList = $this->getItemVariantByItem($tblItem))){
            foreach($tblItemVariantList as $tblItemVariant){
                $this->removeItemVariant($tblItemVariant);
            }
        }
        // remove tblItem
        return (new Data($this->getBinding()))->removeItem($tblItem);
    }

    /**
     * @param TblItemGroup $tblItemGroup
     *
     * @return string
     */
    public function removeItemGroup(TblItemGroup $tblItemGroup)
    {

        return (new Data($this->getBinding()))->removeItemGroup($tblItemGroup);
    }

    /**
     * @param TblItemVariant $tblItemVariant
     *
     * @return string
     */
    public function removeItemVariant(TblItemVariant $tblItemVariant)
    {
        // ToDO Remove Calculation if exist
        return (new Data($this->getBinding()))->removeItemVariant($tblItemVariant);
    }

    /**
     * @param TblItemAccount $tblItemAccount
     *
     * @return string
     */
    public function removeItemAccount(TblItemAccount $tblItemAccount)
    {

        if ((new Data($this->getBinding()))->removeItemAccount($tblItemAccount)) {
            return new Success('Das FIBU-Konto '.$tblItemAccount->getServiceBillingAccount()->getDescription().' wurde erfolgreich entfernt')
            .new Redirect('/Billing/Inventory/Commodity/Item/Account/Select', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblItemAccount->getTblItem()->getId()));
        } else {
            return new Warning('Das FIBU-Konto '.$tblItemAccount->getServiceBillingAccount()->getDescription().' konnte nicht entfernt werden')
            .new Redirect('/Billing/Inventory/Commodity/Item/Account/Select', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblItemAccount->getTblItem()->getId()));
        }
    }

    /**
     * @param TblItem    $tblItem
     * @param TblAccount $tblAccount
     *
     * @return string
     */
    public function addItemToAccount(TblItem $tblItem, TblAccount $tblAccount)
    {

        if ((new Data($this->getBinding()))->addItemAccount($tblItem, $tblAccount)) {
            return new Success('Das FIBU-Konto '.$tblAccount->getDescription().' wurde erfolgreich hinzugefügt')
            .new Redirect('/Billing/Inventory/Commodity/Item/Account/Select', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblItem->getId()));
        } else {
            return new Warning('Das FIBU-Konto '.$tblAccount->getDescription().' konnte nicht entfernt werden')
            .new Redirect('/Billing/Inventory/Commodity/Item/Account/Select', Redirect::TIMEOUT_ERROR, array('Id' => $tblItem->getId()));
        }
    }
}
