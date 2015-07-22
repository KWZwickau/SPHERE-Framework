<?php
namespace SPHERE\Application\System\Gatekeeper\Account\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\System\Gatekeeper\Token\Service;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\System\Gatekeeper\Account\Service
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
        $tblAccount = $this->setTableAccount( $Schema );
        $tblIdentification = $this->setTableIdentification( $Schema );
        $this->setTableSession( $Schema, $tblAccount );
        $this->setTableAuthorization( $Schema, $tblAccount );
        $this->setTableAuthentication( $Schema, $tblAccount, $tblIdentification );
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
    private function setTableAccount( Schema &$Schema )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblAccount' );
        if (!$this->Connection->hasColumn( 'tblAccount', 'Username' )) {
            $Table->addColumn( 'Username', 'string' );
        }
        if (!$this->Connection->hasIndex( $Table, array( 'Username' ) )) {
            $Table->addUniqueIndex( array( 'Username' ) );
        }
        if (!$this->Connection->hasColumn( 'tblAccount', 'Password' )) {
            $Table->addColumn( 'Password', 'string' );
        }
        if (!$this->Connection->hasIndex( $Table, array( 'Username', 'Password' ) )) {
            $Table->addIndex( array( 'Username', 'Password' ) );
        }
        if (!$this->Connection->hasColumn( 'tblAccount', 'serviceTblToken' )) {
            $Table->addColumn( 'serviceTblToken', 'bigint', array( 'notnull' => false ) );
        }
        if (!$this->Connection->hasColumn( 'tblAccount', 'serviceTblConsumer' )) {
            $Table->addColumn( 'serviceTblConsumer', 'bigint', array( 'notnull' => false ) );
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableIdentification( Schema &$Schema )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblIdentification' );
        if (!$this->Connection->hasColumn( 'tblIdentification', 'Name' )) {
            $Table->addColumn( 'Name', 'string' );
        }
        if (!$this->Connection->hasIndex( $Table, array( 'Name' ) )) {
            $Table->addIndex( array( 'Name' ) );
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblAccount
     *
     * @return Table
     */
    private function setTableSession( Schema &$Schema, Table $tblAccount )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblSession' );
        if (!$this->Connection->hasColumn( 'tblSession', 'Session' )) {
            $Table->addColumn( 'Session', 'string' );
        }
        if (!$this->Connection->hasIndex( $Table, array( 'Session' ) )) {
            $Table->addIndex( array( 'Session' ) );
        }
        if (!$this->Connection->hasColumn( 'tblSession', 'Timeout' )) {
            $Table->addColumn( 'Timeout', 'integer' );
        }
        $this->Connection->addForeignKey( $Table, $tblAccount );
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @param Table  $tblAccount
     *
     * @return Table
     */
    private function setTableAuthorization( Schema &$Schema, Table $tblAccount )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblAuthorization' );
        if (!$this->Connection->hasColumn( 'tblAuthorization', 'serviceTblRole' )) {
            $Table->addColumn( 'serviceTblRole', 'bigint', array( 'notnull' => false ) );
        }
        $this->Connection->addForeignKey( $Table, $tblAccount );
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @param Table  $tblAccount
     * @param Table  $tblIdentification
     *
     * @return Table
     */
    private function setTableAuthentication( Schema &$Schema, Table $tblAccount, Table $tblIdentification )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblAuthentication' );
        $this->Connection->addForeignKey( $Table, $tblAccount );
        $this->Connection->addForeignKey( $Table, $tblIdentification );
        return $Table;
    }
}
