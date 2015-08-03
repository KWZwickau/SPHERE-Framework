<?php

namespace SPHERE\Application\Billing\Accounting\Basket\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

class Setup
{

    /** @var null|Structure $Connection */
    private $Connection = null;

    /**
     * @param Structure $Connection
     */
    function __construct( Structure $Connection )
    {

        $this->Connection = $Connection;
    }

    /**
     * @param bool $Simulate
     *
     * @return string
     */
    public function setupDatabaseSchema( $Simulate = true )
    {

        /**
         * Table
         */
        $Schema = clone $this->Connection->getSchema();
        $tblBasket = $this->setTableBasket( $Schema );
        $this->setTableBasketPerson( $Schema, $tblBasket );
        $this->setTableBasketItem( $Schema, $tblBasket );

        $tblBasketCommodity = $this->setTableBasketCommodity( $Schema, $tblBasket );
        $this->setTableBasketCommodityDebtor( $Schema, $tblBasketCommodity );
        /**
         * Migration & Protocol
         */
        $this->Connection->addProtocol( __CLASS__ );
        $this->Connection->setMigration( $Schema, $Simulate );
        return $this->Connection->getProtocol( $Simulate );
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableBasket( Schema &$Schema )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblBasket' );

        if ( !$this->Connection->hasColumn( 'tblBasket', 'CreateDate' ) ) {
            $Table->addColumn( 'CreateDate', 'datetime' );
        }
        if ( !$this->Connection->hasColumn( 'tblBasket', 'Name' ) ) {
            $Table->addColumn( 'Name', 'string' );
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblBasket
     *
     * @return Table
     */
    private function setTableBasketPerson( Schema &$Schema, Table $tblBasket )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblBasketPerson' );

        if ( !$this->Connection->hasColumn( 'tblBasketPerson', 'serviceManagement_Person' ) ) {
            $Table->addColumn( 'serviceManagement_Person', 'bigint' );
        }

        $this->Connection->addForeignKey( $Table, $tblBasket );

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblBasket
     *
     * @return Table
     */
    private function setTableBasketItem( Schema &$Schema, Table $tblBasket )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblBasketItem' );

        if ( !$this->Connection->hasColumn( 'tblBasketItem', 'serviceBilling_CommodityItem' ) ) {
            $Table->addColumn( 'serviceBilling_CommodityItem', 'bigint' );
        }
        if ( !$this->Connection->hasColumn( 'tblBasketItem', 'Price' ) ) {
            $Table->addColumn( 'Price', 'decimal', array( 'precision' => 14, 'scale' => 4 ) );
        }
        if ( !$this->Connection->hasColumn( 'tblBasketItem', 'Quantity' ) ) {
            $Table->addColumn( 'Quantity', 'decimal', array( 'precision' => 14, 'scale' => 4 ) );
        }

        $this->Connection->addForeignKey( $Table, $tblBasket );

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblBasket
     *
     * @return Table
     */
    private function setTableBasketCommodity( Schema &$Schema, Table $tblBasket )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblBasketCommodity' );

        if ( !$this->Connection->hasColumn( 'tblBasketCommodity', 'serviceManagement_Person' ) ) {
            $Table->addColumn( 'serviceManagement_Person', 'bigint' );
        }
        if ( !$this->Connection->hasColumn( 'tblBasketCommodity', 'serviceBilling_Commodity' ) ) {
            $Table->addColumn( 'serviceBilling_Commodity', 'bigint' );
        }

        $this->Connection->addForeignKey( $Table, $tblBasket );

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblBasketCommodity
     *
     * @return Table
     */
    private function setTableBasketCommodityDebtor( Schema &$Schema, Table $tblBasketCommodity )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblBasketCommodityDebtor' );

        if ( !$this->Connection->hasColumn( 'tblBasketCommodityDebtor', 'serviceBilling_Debtor' ) ) {
            $Table->addColumn( 'serviceBilling_Debtor', 'bigint' );
        }

        $this->Connection->addForeignKey( $Table, $tblBasketCommodity );

        return $Table;
    }
}