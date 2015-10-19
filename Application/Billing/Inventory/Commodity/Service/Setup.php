<?php
namespace SPHERE\Application\Billing\Inventory\Commodity\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Inventory\Commodity\Service
 */
class Setup extends AbstractSetup
{

    /**
     * @param bool $Simulate
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true)
    {

        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();
        $tblCommodityType = $this->setTableCommodityType($Schema);
        $this->setTableCommodity($Schema, $tblCommodityType);
        $this->setTableCommodityItem($Schema);
//        $tblItem = $this->setTableItem( $Schema );
//        $this->setTableCommodityItem( $Schema, $tblCommodity, $tblItem );
//        $this->setTableItemAccount( $Schema, $tblItem );

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);
        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCommodityType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCommodityType');
        if (!$this->getConnection()->hasColumn('tblCommodityType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblCommodityType
     *
     * @return Table
     */
    private function setTableCommodity(Schema &$Schema, Table $tblCommodityType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCommodity');

        if (!$this->getConnection()->hasColumn('tblCommodity', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCommodity', 'Description')) {
            $Table->addColumn('Description', 'string');
        }

        $this->getConnection()->addForeignKey($Table, $tblCommodityType);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCommodityItem(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCommodityItem');

        if (!$this->getConnection()->hasColumn('tblCommodityItem', 'Quantity')) {
            $Table->addColumn('Quantity', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        if (!$this->getConnection()->hasColumn('tblCommodityItem', 'tblItem')) {
            $Table->addColumn('tblItem', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblCommodityItem', 'tblCommodity')) {
            $Table->addColumn('tblCommodity', 'bigint');
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
//        $Table = $this->getConnection()->createTable( $Schema, 'tblItem' );
//        if ( !$this->getConnection()->hasColumn( 'tblItem', 'Name' ) ) {
//            $Table->addColumn( 'Name', 'string' );
//        }
//        if ( !$this->getConnection()->hasColumn( 'tblItem', 'Description' ) ) {
//            $Table->addColumn( 'Description', 'string' );
//        }
//        if ( !$this->getConnection()->hasColumn( 'tblItem', 'Price' ) ) {
//            $Table->addColumn( 'Price', 'decimal', array( 'precision' => 14, 'scale' => 4 ) );
//        }
//        if ( !$this->getConnection()->hasColumn( 'tblItem', 'CostUnit' ) ) {
//            $Table->addColumn( 'CostUnit', 'string' );
//        }
//        if ( !$this->getConnection()->hasColumn( 'tblItem', 'serviceManagement_Student_ChildRank' ) ) {
//            $Table->addColumn( 'serviceManagement_Student_ChildRank', 'bigint', array( 'notnull' => false ) );
//        }
//        if ( !$this->getConnection()->hasColumn( 'tblItem', 'serviceManagement_Course' ) ) {
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
//        $Table = $this->getConnection()->createTable( $Schema, 'tblItemAccount' );
//
//        if ( !$this->getConnection()->hasColumn( 'tblItemAccount', 'serviceBilling_Account' ) ) {
//            $Table->addColumn( 'serviceBilling_Account', 'bigint' );
//        }
//
//        $this->getConnection()->addForeignKey( $Table, $tblItem );
//
//        return $Table;
//    }
}
