<?php

namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Inventory\Item\Service\Data;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemAccount;
use SPHERE\Application\Billing\Inventory\Item\Service\Setup;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

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
     * @param string $EntityPath
     * @param string $EntityNamespace
     */
    public function __construct( Identifier $Identifier, $EntityPath, $EntityNamespace )
    {

        $this->Binding = new Binding( $Identifier, $EntityPath, $EntityNamespace );
        $this->Structure = new Structure( $Identifier );
    }

    /**
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService( $Simulate, $withData )
    {

        $Protocol = ( new Setup( $this->Structure ) )->setupDatabaseSchema( $Simulate );
        if (!$Simulate && $withData) {
            ( new Data( $this->Binding ) )->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return bool|TblItem
     */
    public function entityItemById( $Id )
    {

        return ( new Data( $this->Binding ) )->entityItemById( $Id );
    }

    /**
     * @return bool|TblItem[]
     */
    public function entityItemAll()
    {

        return ( new Data( $this->Binding ) )->entityItemAll();
    }

    /**
     * @param TblItem $tblItem
     *
     * @return bool|TblItemAccount[]
     */
    public function entityItemAccountAllByItem( TblItem $tblItem )
    {

        return ( new Data( $this->Binding ) )->entityItemAccountAllByItem( $tblItem );
    }

    /**
     * @param $Id
     *
     * @return bool|TblItemAccount
     */
    public function entityItemAccountById( $Id )
    {

        return ( new Data( $this->Binding ) )->entityItemAccountById( $Id );
    }

    /**
     * @param IFormInterface $Stage
     * @param $Item
     *
     * @return IFormInterface|string
     */
    public function executeCreateItem( IFormInterface &$Stage = null, $Item )
    {

        /**
         * Skip to Frontend
         */
        if ( null === $Item
        ) {
            return $Stage;
        }

        $Error = false;

        if ( isset( $Item['Name'] ) && empty( $Item['Name'] ) ) {
            $Stage->setError( 'Item[Name]', 'Bitte geben Sie einen Namen an' );
            $Error = true;
        }
        if ( isset( $Item['Price'] ) && empty( $Item['Price'] ) ) {
            $Stage->setError( 'Item[Price]', 'Bitte geben Sie einen Preis an' );
            $Error = true;
        }

        if ( !$Error ) {
            ( new Data( $this->Binding ) )->actionCreateItem(
                $Item['Name'],
                $Item['Description'],
                $Item['Price'],
                $Item['CostUnit'] //,
//                $Item['Course'],
//                $Item['ChildRank']
            );
            return new Success( 'Der Artikel wurde erfolgreich angelegt' )
            .new Redirect( '/Billing/Inventory/Item', 1 );
        }
        return $Stage;
    }

    /**
     * @param TblItem $tblItem
     *
     * @return string
     */
    public function executeDeleteItem( TblItem $tblItem )
    {

        if ( null === $tblItem ) {
            return '';
        }

        if ( ( new Data( $this->Binding ) )->actionDestroyItem( $tblItem ) ) {
            return new Success( 'Der Artikel wurde erfolgreich gelöscht' )
            .new Redirect( '/Billing/Inventory/Item', 1 );
        } else {
            return new Danger( 'Der Artikel konnte nicht gelöscht werden. Überprüfen Sie ob er noch in einer Leistung verwendet wird.' )
            .new Redirect( '/Billing/Inventory/Item', 3 );
        }
    }

    /**
     * @param IFormInterface $Stage
     * @param TblItem $tblItem
     * @param $Item
     *
     * @return IFormInterface|string
     */
    public function executeEditItem(
        IFormInterface &$Stage = null, TblItem $tblItem, $Item )
    {

        /**
         * Skip to Frontend
         */
        if ( null === $Item
        ) {
            return $Stage;
        }

        $Error = false;

        if ( isset( $Item['Name'] ) && empty( $Item['Name'] ) ) {
            $Stage->setError( 'Item[Name]', 'Bitte geben Sie einen Namen an' );
            $Error = true;
        }
        if ( isset( $Item['Price'] ) && empty( $Item['Price'] ) ) {
            $Stage->setError( 'Item[Price]', 'Bitte geben Sie einen Preis an' );
            $Error = true;
        }

        if ( !$Error ) {
            if ( ( new Data( $this->Binding ) )->actionEditItem(
                $tblItem,
                $Item['Name'],
                $Item['Description'],
                $Item['Price'],
                $Item['CostUnit'],
                $Item['Course'],
                $Item['ChildRank']
            )
            ) {
                $Stage .= new Success( 'Änderungen gespeichert, die Daten werden neu geladen...' )
                    .new Redirect( '/Billing/Inventory/Commodity/Item', 1 );
            } else {
                $Stage .= new Danger( 'Änderungen konnten nicht gespeichert werden' );
            };
        }
        return $Stage;
    }

    /**
     * @param TblItemAccount $tblItemAccount
     *
     * @return string
     */
    public function executeRemoveItemAccount( TblItemAccount $tblItemAccount )
    {

        if ( ( new Data( $this->Binding ) )->actionRemoveItemAccount( $tblItemAccount ) ) {
            return new Success( 'Das FIBU-Konto '.$tblItemAccount->getServiceBilling_Account()->getDescription().' wurde erfolgreich entfernt' )
            .new Redirect( '/Billing/Inventory/Commodity/Item/Account/Select', 1, array( 'Id' => $tblItemAccount->getTblItem()->getId() ) );
        } else {
            return new Warning( 'Das FIBU-Konto '.$tblItemAccount->getServiceBilling_Account()->getDescription().' konnte nicht entfernt werden' )
            .new Redirect( '/Billing/Inventory/Commodity/Item/Account/Select', 3, array( 'Id' => $tblItemAccount->getTblItem()->getId() ) );
        }
    }

    /**
     * @param TblItem $tblItem
     * @param TblAccount $tblAccount
     *
     * @return string
     */
    public function executeAddItemAccount( TblItem $tblItem, TblAccount $tblAccount )
    {

        if ( ( new Data( $this->Binding ) )->actionAddItemAccount( $tblItem, $tblAccount ) ) {
            return new Success( 'Das FIBU-Konto '.$tblAccount->getDescription().' wurde erfolgreich hinzugefügt' )
            .new Redirect( '/Billing/Inventory/Commodity/Item/Account/Select', 0, array( 'Id' => $tblItem->getId() ) );
        } else {
            return new Warning( 'Das FIBU-Konto '.$tblAccount->getDescription().' konnte nicht entfernt werden' )
            .new Redirect( '/Billing/Inventory/Commodity/Item/Account/Select', 2, array( 'Id' => $tblItem->getId() ) );
        }
    }
}
