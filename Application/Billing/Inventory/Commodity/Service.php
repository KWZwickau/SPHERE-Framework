<?php

namespace SPHERE\Application\Billing\Inventory\Commodity;

use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Data;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodityItem;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodityType;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Setup;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Service
 *
 * @package SPHERE\Application\Billing\Inventory\Commodity
 */
class Service implements IServiceInterface
{

    /** @var null|Binding */
    private $Binding = null;
    /** @var null|Structure */
    private $Structure = null;

    /**
     * Define Database Connection
     *
     * @param Identifier $Identifier
     * @param string     $EntityPath
     * @param string     $EntityNamespace
     */
    public function __construct(Identifier $Identifier, $EntityPath, $EntityNamespace)
    {

        $this->Binding = new Binding($Identifier, $EntityPath, $EntityNamespace);
        $this->Structure = new Structure($Identifier);
    }

    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {

        $Protocol = (new Setup($this->Structure))->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            (new Data($this->Binding))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @return bool|TblCommodity[]
     */
    public function entityCommodityAll()
    {

        return (new Data($this->Binding))->entityCommodityAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblCommodity
     */
    public function entityCommodityById($Id)
    {

        return (new Data($this->Binding))->entityCommodityById($Id);
    }

    /**
     * @param TblCommodity $tblCommodity
     *
     * @return int
     */
    public function countItemAllByCommodity(TblCommodity $tblCommodity)
    {

        return (new Data($this->Binding))->countItemAllByCommodity($tblCommodity);
    }

    /**
     * @param TblCommodity $tblCommodity
     *
     * @return string
     */
    public function sumPriceItemAllByCommodity(TblCommodity $tblCommodity)
    {

        return (new Data($this->Binding))->sumPriceItemAllByCommodity($tblCommodity);
    }

    /**
     * @return bool|TblCommodityType[]
     */
    public function entityCommodityTypeAll()
    {

        return (new Data($this->Binding))->entityCommodityTypeAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblCommodityItem
     */
    public function entityCommodityItemById($Id)
    {

        return (new Data($this->Binding))->entityCommodityItemById($Id);
    }

    /**
     * @param TblCommodity $tblCommodity
     *
     * @return bool|TblItem[]
     */
    public function entityCommodityItemAllByCommodity(TblCommodity $tblCommodity)
    {

        return (new Data($this->Binding))->entityCommodityItemAllByCommodity($tblCommodity);
    }

    /**
     * @param TblCommodity $tblCommodity
     *
     * @return bool|TblItem[]
     */
    public function entityItemAllByCommodity(TblCommodity $tblCommodity)
    {

        return (new Data($this->Binding))->entityItemAllByCommodity($tblCommodity);
    }

    /**
     * @param TblItem $tblItem
     *
     * @return bool|TblItem[]
     */
    public function entityCommodityItemAllByItem(TblItem $tblItem)
    {

        return (new Data($this->Binding))->entityCommodityItemAllByItem($tblItem);
    }

    /**
     * @param TblItem $tblItem
     *
     * @return TblAccount[]
     */
    public function entityAccountAllByItem(TblItem $tblItem)
    {

        return (new Data($this->Binding))->entityAccountAllByItem($tblItem);
    }

    /**
     * @param $Name
     *
     * @return bool|TblCommodity
     */
    public function entityCommodityByName($Name)
    {

        return (new Data($this->Binding))->entityCommodityByName($Name);
    }

    /**
     * @param IFormInterface $Stage
     * @param                $Commodity
     *
     * @return IFormInterface|string
     */
    public function executeCreateCommodity(IFormInterface &$Stage = null, $Commodity)
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
            if (isset( $Commodity['Name'] ) && (new Data($this->Binding))->entityCommodityByName($Commodity['Name'])) {
                $Stage->setError('Commodity[Name]', 'Die Leistung exisitiert bereits.
                Bitte geben Sie eine anderen Name an');
                $Error = true;
            }
        }

        if (!$Error) {
            (new Data($this->Binding))->actionCreateCommodity(
                $Commodity['Name'],
                $Commodity['Description'],
                $this->entityCommodityTypeById($Commodity['Type'])
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
    public function entityCommodityTypeById($Id)
    {

        return (new Data($this->Binding))->entityCommodityTypeById($Id);
    }

    /**
     * @param TblCommodity $tblCommodity
     *
     * @return string
     */
    public function executeRemoveCommodity(TblCommodity $tblCommodity)
    {

        if (null === $tblCommodity) {
            return '';
        }

        if ((new Data($this->Binding))->actionRemoveCommodity($tblCommodity)) {
            return new Success('Die Leistung wurde erfolgreich gelöscht')
            .new Redirect('/Billing/Inventory/Commodity', 1);
        } else {
            return new Danger('Die Leistung konnte nicht gelöscht werden')
            .new Redirect('/Billing/Inventory/Commodity', 3);
        }
    }

    /**
     * @param IFormInterface $Stage
     * @param TblCommodity   $tblCommodity
     * @param                $Commodity
     *
     * @return IFormInterface|string
     */
    public function executeEditCommodity(IFormInterface &$Stage = null, TblCommodity $tblCommodity, $Commodity)
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
                && (new Data($this->Binding))->entityCommodityByName($Commodity['Name'])
            ) {
                $Stage->setError('Commodity[Name]', 'Die Leistung exisitiert bereits.
                Bitte geben Sie eine anderen Name an');
                $Error = true;
            }
        }

        if (!$Error) {
            if ((new Data($this->Binding))->actionEditCommodity(
                $tblCommodity,
                $Commodity['Name'],
                $Commodity['Description'],
                $this->entityCommodityTypeById($Commodity['Type'])
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
     * @param TblCommodityItem $tblCommodityItem
     *
     * @return string
     */
    public function executeRemoveCommodityItem(TblCommodityItem $tblCommodityItem)
    {

        if ((new Data($this->Binding))->actionRemoveCommodityItem($tblCommodityItem)) {
            return new Success('Der Artikel '.$tblCommodityItem->getTblItem()->getName().' wurde erfolgreich entfernt')
            .new Redirect('/Billing/Inventory/Commodity/Item/Select', 1,
                array('Id' => $tblCommodityItem->getTblCommodity()->getId()));
        } else {
            return new Warning('Der Artikel '.$tblCommodityItem->getTblItem()->getName().' konnte nicht entfernt werden')
            .new Redirect('/Billing/Inventory/Commodity/Item/Select', 3,
                array('Id' => $tblCommodityItem->getTblCommodity()->getId()));
        }
    }

    /**
     * @param TblCommodity $tblCommodity
     * @param TblItem      $tblItem
     * @param              $Item
     *
     * @return string
     */
    public function executeAddCommodityItem(TblCommodity $tblCommodity, TblItem $tblItem, $Item)
    {

        if ($Item['Quantity'] == null) {
            $Item['Quantity'] = 1;
        }

        if ((new Data($this->Binding))->actionAddCommodityItem($tblCommodity, $tblItem, $Item['Quantity'])) {
            return new Success('Der Artikel '.$tblItem->getName().' wurde erfolgreich hinzugefügt')
            .new Redirect('/Billing/Inventory/Commodity/Item/Select', 1, array('Id' => $tblCommodity->getId()));
        } else {
            return new Warning('Der Artikel '.$tblItem->getName().' konnte nicht entfernt werden')
            .new Redirect('/Billing/Inventory/Commodity/Item/Select', 3, array('Id' => $tblCommodity->getId()));
        }
    }
}
