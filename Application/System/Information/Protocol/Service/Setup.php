<?php
namespace SPHERE\Application\System\Information\Protocol\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\System\Information\Protocol\Service
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
        $this->setTableProtocol( $Schema );
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
    private function setTableProtocol( Schema &$Schema )
    {

        /**
         * Install
         */
        $Table = $this->Connection->createTable( $Schema, 'tblProtocol' );
        /**
         * Upgrade
         */
        // System
        if (!$this->Connection->hasColumn( 'tblProtocol', 'ProtocolDatabase' )) {
            $Table->addColumn( 'ProtocolDatabase', 'string', array( 'notnull' => false ) );
        }
        if (!$this->Connection->hasIndex( $Table, array( 'ProtocolDatabase' ) )) {
            $Table->addIndex( array( 'ProtocolDatabase' ) );
        }
        if (!$this->Connection->hasColumn( 'tblProtocol', 'ProtocolTimestamp' )) {
            $Table->addColumn( 'ProtocolTimestamp', 'integer', array( 'notnull' => false ) );
        }
        if (!$this->Connection->hasIndex( $Table, array( 'ProtocolTimestamp' ) )) {
            $Table->addIndex( array( 'ProtocolTimestamp' ) );
        }
        // Editor
        if (!$this->Connection->hasColumn( 'tblProtocol', 'serviceGatekeeper_Account' )) {
            $Table->addColumn( 'serviceGatekeeper_Account', 'bigint', array( 'notnull' => false ) );
        }
        if (!$this->Connection->hasColumn( 'tblProtocol', 'AccountUsername' )) {
            $Table->addColumn( 'AccountUsername', 'string', array( 'notnull' => false ) );
        }
        if (!$this->Connection->hasIndex( $Table, array( 'AccountUsername' ) )) {
            $Table->addIndex( array( 'AccountUsername' ) );
        }
        if (!$this->Connection->hasColumn( 'tblProtocol', 'serviceManagement_Person' )) {
            $Table->addColumn( 'serviceManagement_Person', 'bigint', array( 'notnull' => false ) );
        }
        if (!$this->Connection->hasColumn( 'tblProtocol', 'PersonFirstName' )) {
            $Table->addColumn( 'PersonFirstName', 'string', array( 'notnull' => false ) );
        }
        if (!$this->Connection->hasIndex( $Table, array( 'PersonFirstName' ) )) {
            $Table->addIndex( array( 'PersonFirstName' ) );
        }
        if (!$this->Connection->hasColumn( 'tblProtocol', 'PersonLastName' )) {
            $Table->addColumn( 'PersonLastName', 'string', array( 'notnull' => false ) );
        }
        if (!$this->Connection->hasIndex( $Table, array( 'PersonLastName' ) )) {
            $Table->addIndex( array( 'PersonLastName' ) );
        }
        // Consumer
        if (!$this->Connection->hasColumn( 'tblProtocol', 'serviceGatekeeper_Consumer' )) {
            $Table->addColumn( 'serviceGatekeeper_Consumer', 'bigint', array( 'notnull' => false ) );
        }
        if (!$this->Connection->hasColumn( 'tblProtocol', 'ConsumerName' )) {
            $Table->addColumn( 'ConsumerName', 'string', array( 'notnull' => false ) );
        }
        if (!$this->Connection->hasIndex( $Table, array( 'ConsumerName' ) )) {
            $Table->addIndex( array( 'ConsumerName' ) );
        }
        if (!$this->Connection->hasColumn( 'tblProtocol', 'ConsumerSuffix' )) {
            $Table->addColumn( 'ConsumerSuffix', 'string', array( 'notnull' => false ) );
        }
        if (!$this->Connection->hasIndex( $Table, array( 'ConsumerSuffix' ) )) {
            $Table->addIndex( array( 'ConsumerSuffix' ) );
        }
        // Data
        if (!$this->Connection->hasColumn( 'tblProtocol', 'EntityFrom' )) {
            $Table->addColumn( 'EntityFrom', 'text', array( 'notnull' => false ) );
        }
        if (!$this->Connection->hasColumn( 'tblProtocol', 'EntityTo' )) {
            $Table->addColumn( 'EntityTo', 'text', array( 'notnull' => false ) );
        }

        return $Table;
    }
}
