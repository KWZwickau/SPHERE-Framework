<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance\Service;

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

        $tblBalance = $this->setTableBalance( $Schema );
        $this->setTablePayment( $Schema, $tblBalance );
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
    private function setTableBalance( Schema &$Schema )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblBalance' );

        if ( !$this->Connection->hasColumn( 'tblBalance', 'serviceBilling_Banking' ) ) {
            $Table->addColumn( 'serviceBilling_Banking', 'bigint' );
        }
        if ( !$this->Connection->hasColumn( 'tblBalance', 'serviceBilling_Invoice' ) ) {
            $Table->addColumn( 'serviceBilling_Invoice', 'bigint' );
        }
        if ( !$this->Connection->hasColumn( 'tblBalance', 'ExportDate' ) ) {
            $Table->addColumn( 'ExportDate', 'date', array( 'notnull' => false ) );
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblBalance
     *
     * @return Table
     */
    private function setTablePayment( Schema &$Schema, Table $tblBalance )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblPayment' );

        if ( !$this->Connection->hasColumn( 'tblPayment', 'Value' ) ) {
            $Table->addColumn( 'Value', 'decimal', array( 'precision' => 14, 'scale' => 4 ) );
        }
        if ( !$this->Connection->hasColumn( 'tblPayment', 'Date' ) ) {
            $Table->addColumn( 'Date', 'date' );
        }

        $this->Connection->addForeignKey( $Table, $tblBalance );

        return $Table;
    }
}