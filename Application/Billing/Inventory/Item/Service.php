<?php

namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Inventory\Item\Service\Data;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemAccount;
use SPHERE\Application\Billing\Inventory\Item\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

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
     * @param IFormInterface $Stage
     * @param                $Item
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
            $Stage->setError('Item[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
        if (isset( $Item['Price'] ) && empty( $Item['Price'] )) {
            $Stage->setError('Item[Price]', 'Bitte geben Sie einen Preis an');
            $Error = true;
        }

        if (!$Error) {
            (new Data($this->getBinding()))->createItem(
                $Item['Name'],
                $Item['Description'],
                $Item['Price'],
                $Item['CostUnit'] //,
//                $Item['Course'],
//                $Item['ChildRank']
            );
            return new Success('Der Artikel wurde erfolgreich angelegt')
            .new Redirect('/Billing/Inventory/Item', 1);
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
            .new Redirect('/Billing/Inventory/Item', 1);
        } else {
            return new Danger('Der Artikel konnte nicht gelöscht werden. Überprüfen Sie ob er noch in einer Leistung verwendet wird.')
            .new Redirect('/Billing/Inventory/Item', 3);
        }
    }

    /**
     * @param IFormInterface $Stage
     * @param TblItem        $tblItem
     * @param                $Item
     *
     * @return IFormInterface|string
     */
    public function changeItem(
        IFormInterface &$Stage = null,
        TblItem $tblItem,
        $Item
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Item
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $Item['Name'] ) && empty( $Item['Name'] )) {
            $Stage->setError('Item[Name]', 'Bitte geben Sie einen Namen an');
            $Error = true;
        }
        if (isset( $Item['Price'] ) && empty( $Item['Price'] )) {
            $Stage->setError('Item[Price]', 'Bitte geben Sie einen Preis an');
            $Error = true;
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updateItem(
                $tblItem,
                $Item['Name'],
                $Item['Description'],
                $Item['Price'],
                $Item['CostUnit'] //,
//                $Item['Course'],
//                $Item['ChildRank']
            )
            ) {
                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    .new Redirect('/Billing/Inventory/Item', 1);
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden');
            };
        }
        return $Stage;
    }

    /**
     * @param TblItemAccount $tblItemAccount
     *
     * @return string
     */
    public function removeItemAccount(TblItemAccount $tblItemAccount)
    {

        if ((new Data($this->getBinding()))->removeItemAccount($tblItemAccount)) {
            return new Success('Das FIBU-Konto '.$tblItemAccount->getServiceBilling_Account()->getDescription().' wurde erfolgreich entfernt')
            .new Redirect('/Billing/Inventory/Commodity/Item/Account/Select', 0,
                array('Id' => $tblItemAccount->getTblItem()->getId()));
        } else {
            return new Warning('Das FIBU-Konto '.$tblItemAccount->getServiceBilling_Account()->getDescription().' konnte nicht entfernt werden')
            .new Redirect('/Billing/Inventory/Commodity/Item/Account/Select', 3,
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
            .new Redirect('/Billing/Inventory/Commodity/Item/Account/Select', 0, array('Id' => $tblItem->getId()));
        } else {
            return new Warning('Das FIBU-Konto '.$tblAccount->getDescription().' konnte nicht entfernt werden')
            .new Redirect('/Billing/Inventory/Commodity/Item/Account/Select', 2, array('Id' => $tblItem->getId()));
        }
    }
}
