<?php

namespace SPHERE\Application\Billing\Inventory\Commodity\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Inventory\Commodity\Service
 */
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
        $tblCommodityType = $this->setTableCommodityType( $Schema );
        $this->setTableCommodity( $Schema, $tblCommodityType );
        $this->setTableCommodityItem( $Schema );
//        $tblItem = $this->setTableItem( $Schema );
//        $this->setTableCommodityItem( $Schema, $tblCommodity, $tblItem );
//        $this->setTableItemAccount( $Schema, $tblItem );

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
    private function setTableCommodityType( Schema &$Schema )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblCommodityType' );
        if ( !$this->Connection->hasColumn( 'tblCommodityType', 'Name' ) ) {
            $Table->addColumn( 'Name', 'string' );
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblCommodityType
     *
     * @return Table
     */
    private function setTableCommodity( Schema &$Schema, Table $tblCommodityType )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblCommodity' );

        if ( !$this->Connection->hasColumn( 'tblCommodity', 'Name' ) ) {
            $Table->addColumn( 'Name', 'string' );
        }
        if ( !$this->Connection->hasColumn( 'tblCommodity', 'Description' ) ) {
            $Table->addColumn( 'Description', 'string' );
        }

        $this->Connection->addForeignKey( $Table, $tblCommodityType );

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCommodityItem( Schema &$Schema )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblCommodityItem' );

        if ( !$this->Connection->hasColumn( 'tblCommodityItem', 'Quantity' ) ) {
            $Table->addColumn( 'Quantity', 'decimal', array( 'precision' => 14, 'scale' => 4 ) );
        }
        if ( !$this->Connection->hasColumn( 'tblCommodityItem', 'tblItem' ) ) {
            $Table->addColumn( 'tblItem', 'bigint' );
        }
        if ( !$this->Connection->hasColumn( 'tblCommodityItem', 'tblCommodity' ) ) {
            $Table->addColumn( 'tblCommodity', 'bigint' );
        }



        return $Table;
    }

//    /**
//     * @param Schema $Schema
//     *
//     * @return Table
//     */
//    private function setTableItem( Schema &$Schema )
//    {
//
//        $Table = $this->Connection->createTable( $Schema, 'tblItem' );
//        if ( !$this->Connection->hasColumn( 'tblItem', 'Name' ) ) {
//            $Table->addColumn( 'Name', 'string' );
//        }
//        if ( !$this->Connection->hasColumn( 'tblItem', 'Description' ) ) {
//            $Table->addColumn( 'Description', 'string' );
//        }
//        if ( !$this->Connection->hasColumn( 'tblItem', 'Price' ) ) {
//            $Table->addColumn( 'Price', 'decimal', array( 'precision' => 14, 'scale' => 4 ) );
//        }
//        if ( !$this->Connection->hasColumn( 'tblItem', 'CostUnit' ) ) {
//            $Table->addColumn( 'CostUnit', 'string' );
//        }
//        if ( !$this->Connection->hasColumn( 'tblItem', 'serviceManagement_Student_ChildRank' ) ) {
//            $Table->addColumn( 'serviceManagement_Student_ChildRank', 'bigint', array( 'notnull' => false ) );
//        }
//        if ( !$this->Connection->hasColumn( 'tblItem', 'serviceManagement_Course' ) ) {
//            $Table->addColumn( 'serviceManagement_Course', 'bigint', array( 'notnull' => false ) );
//        }
//
//        return $Table;
//    }
//
//    /**
//     * @param Schema $Schema
//     * @param Table $tblItem
//     *
//     * @return Table
//     */
//    private function setTableItemAccount( Schema &$Schema, Table $tblItem )
//    {
//
//        $Table = $this->Connection->createTable( $Schema, 'tblItemAccount' );
//
//        if ( !$this->Connection->hasColumn( 'tblItemAccount', 'serviceBilling_Account' ) ) {
//            $Table->addColumn( 'serviceBilling_Account', 'bigint' );
//        }
//
//        $this->Connection->addForeignKey( $Table, $tblItem );
//
//        return $Table;
//    }
}
