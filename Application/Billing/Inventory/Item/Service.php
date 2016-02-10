<?php

namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Inventory\Item\Service\Data;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblCalculation;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemAccount;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemType;
use SPHERE\Application\Billing\Inventory\Item\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Debugger;

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
     * @param $Id
     *
     * @return bool|TblItemType
     */
    public function getItemTypeById($Id)
    {

        return (new Data($this->getBinding()))->getItemTypeById($Id);
    }

    public function getItemTypeAll()
    {

        return (new Data($this->getBinding()))->getItemTypeAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblCalculation
     */
    public function getCalculationById($Id)
    {

        return (new Data($this->getBinding()))->getCalculationById($Id);
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
     * @param TblItem $tblItem
     *
     * @return bool|TblCalculation
     */
    public function getCalculationAllByItem(TblItem $tblItem)
    {

        return (new Data($this->getBinding()))->getCalculationAllByItem($tblItem);
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
     * @param IFormInterface $Stage
     * @param array          $Item
     *
     * @return IFormInterface|string
     */
    public function createItem(IFormInterface &$Stage = null, $Item)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Item
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $Item['Name'] ) && empty( $Item['Name'] )) {
            $Stage->setError('Item[Name]', 'Bitte geben Sie einen Artikel-Namen an');
            $Error = true;
        }
        if (isset( $Item['ItemType'] ) && empty( $Item['ItemType'] )) {
            $Stage->setError('Item[ItemType]', 'Art des Artikels wird benötigt');
            $Error = true;
        }
        if ($this->existsItem($Item['Name'])) {
            $Stage->setError('Item[Name]', 'Bitte geben Sie einen nicht vergebenen Artikel-Namen an');
            $Error = true;
        }

        if (!$Error) {
            $tblItemType = Item::useService()->getItemTypeById($Item['ItemType']);
            if ($tblItemType) {
                (new Data($this->getBinding()))->createItem(
                    $tblItemType,
                    $Item['Name'],
                    $Item['Description']
                );
                return new Success('Der Artikel wurde erfolgreich angelegt')
                .new Redirect('/Billing/Inventory/Item', Redirect::TIMEOUT_SUCCESS);
            }
            return new Danger('Der Artikel konnte nicht angelegt werden')
            .new Redirect('/Billing/Inventory/Item', Redirect::TIMEOUT_ERROR);

        }
        return $Stage;
    }

    /**
     * @param TblItem $tblItem
     *
     * @return string
     */
    public function destroyItem(TblItem $tblItem)
    {

        if (null === $tblItem) {
            return '';
        }

        if ((new Data($this->getBinding()))->destroyItem($tblItem)) {
            return new Success('Der Artikel wurde erfolgreich gelöscht')
            .new Redirect('/Billing/Inventory/Item', Redirect::TIMEOUT_SUCCESS);
        } else {
            return new Danger('Der Artikel konnte nicht gelöscht werden. Überprüfen Sie ob er noch in einer Leistung verwendet wird.')
            .new Redirect('/Billing/Inventory/Item', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param IFormInterface $Stage
     * @param TblItem        $tblItem
     * @param                $Item
     *
     * @return IFormInterface|string
     */
    public function changeItem(IFormInterface &$Stage = null, TblItem $tblItem, $Item)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Item
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $Item['Name'] ) && empty( $Item['Name'] )) {
            $Stage->setError('Item[Name]', 'Bitte geben Sie einen Artikel-Namen an');
            $Error = true;
        }
//        if (isset( $Item['ItemType'] ) && empty( $Item['ItemType'] )) {
//            $Stage->setError('Item[ItemType]', 'Art des Artikels wird benötigt');
//            $Error = true;
//        }
        if ($this->existsItem($Item['Name'])) {
            $Ref = $this->existsItem($Item['Name']);
            if ($Ref) {
                if ($Ref->getId() === $tblItem->getId()) {

                } else {
                    $Stage->setError('Item[Name]', 'Bitte geben Sie einen nicht vergebenen Artikel-Namen an');
                    $Error = true;
                }
            }
        }

        if (!$Error) {
            $tblItemType = Item::useService()->getItemTypeById($Item['ItemType']);
            if ($tblItemType) {
                if ((new Data($this->getBinding()))->updateItem(
                    $tblItem,
                    $Item['Name'],
                    $Item['Description']
                )
                ) {
                    $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                        .new Redirect('/Billing/Inventory/Item', Redirect::TIMEOUT_SUCCESS);
                } else {
                    $Stage .= new Danger('Änderungen konnten nicht gespeichert werden')
                        .new Redirect('/Billing/Inventory/Item', Redirect::TIMEOUT_ERROR);
                };
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden')
                    .new Redirect('/Billing/Inventory/Item', Redirect::TIMEOUT_ERROR);
            }
        }
        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblItem             $tblItem
     * @param TblCalculation      $tblCalculation
     * @param array               $Condition
     *
     * @return IFormInterface|string
     */
    public function changeCalculation(
        IFormInterface &$Stage = null,
        TblItem $tblItem,
        TblCalculation $tblCalculation,
        $Condition
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Condition
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $Condition['Price'] ) && empty( $Condition['Price'] )) {
            $Stage->setError('Condition[Price]', 'Bitte geben Sie einen Artikel-Preis an');
            $Error = true;
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updateCalculation(
                $tblCalculation,
                $Condition['Value']
            )
            ) {
                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    .new Redirect('/Billing/Inventory/Item/Condition', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $tblItem->getId()));
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden')
                    .new Redirect('/Billing/Inventory/Item/Condition', Redirect::TIMEOUT_ERROR,
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
        }

        if ($this->existsCalculation($tblItem, $Calculation['SchoolType'], $Calculation['SiblingRank'])) {
            $Stage->setError('Calculation[SchoolType]', 'Bedingungskombination vorhanden!');
            $Stage->setError('Calculation[SiblingRank]', 'Bedingungskombination vorhanden!');
            $Error = true;
        }

        Debugger::screenDump($Calculation);

        if (!$Error) {
            if ((new Data($this->getBinding()))->createCalculation(
                $tblItem,
                $Calculation['Value'],
                $Calculation['SchoolType'],
                $Calculation['SiblingRank']
            )
            ) {
                $Stage .= new Success('Gespeichert, die Daten werden neu geladen...')
                    .new Redirect('/Billing/Inventory/Item/Condition', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $tblItem->getId()));
            } else {
                $Stage .= new Danger('Es konnten nicht gespeichert werden.
                                        Möglicherweise gibt es die Bedingungskombination schon')
                    .new Redirect('/Billing/Inventory/Item/Condition', Redirect::TIMEOUT_ERROR,
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
     * @return bool|TblCalculation
     */
    public function existsCalculation(TblItem $tblItem, $SchoolType, $SiblingRank)
    {

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

    /**
     * @param int $Price
     *
     * @return string
     */
    public function formatPrice($Price)
    {

        return (new Data($this->getBinding()))->formatPrice($Price);
    }
}
