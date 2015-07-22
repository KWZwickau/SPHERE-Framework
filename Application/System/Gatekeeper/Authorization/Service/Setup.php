<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Service
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
        $tblRight = $this->setTableRight( $Schema );
        $tblPrivilege = $this->setTablePrivilege( $Schema );
        $tblAccess = $this->setTableAccess( $Schema );
        $tblRole = $this->setTableRole( $Schema );

        $this->setTablePrivilegeRight( $Schema, $tblPrivilege, $tblRight );
        $this->setTableAccessPrivilege( $Schema, $tblAccess, $tblPrivilege );
        $this->setTableRoleAccess( $Schema, $tblRole, $tblAccess );
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
    private function setTableRight( Schema &$Schema )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblRight' );
        if (!$this->Connection->hasColumn( 'tblRight', 'Route' )) {
            $Table->addColumn( 'Route', 'string' );
        }
        if (!$this->Connection->hasIndex( $Table, array( 'Route' ) )) {
            $Table->addUniqueIndex( array( 'Route' ) );
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTablePrivilege( Schema &$Schema )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblPrivilege' );
        if (!$this->Connection->hasColumn( 'tblPrivilege', 'Name' )) {
            $Table->addColumn( 'Name', 'string' );
        }
        if (!$this->Connection->hasIndex( $Table, array( 'Name' ) )) {
            $Table->addUniqueIndex( array( 'Name' ) );
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableAccess( Schema &$Schema )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblAccess' );
        if (!$this->Connection->hasColumn( 'tblAccess', 'Name' )) {
            $Table->addColumn( 'Name', 'string' );
        }
        if (!$this->Connection->hasIndex( $Table, array( 'Name' ) )) {
            $Table->addUniqueIndex( array( 'Name' ) );
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableRole( Schema &$Schema )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblRole' );
        if (!$this->Connection->hasColumn( 'tblRole', 'Name' )) {
            $Table->addColumn( 'Name', 'string' );
        }
        if (!$this->Connection->hasIndex( $Table, array( 'Name' ) )) {
            $Table->addUniqueIndex( array( 'Name' ) );
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblPrivilege
     * @param Table  $tblRight
     *
     * @return Table
     */
    private function setTablePrivilegeRight(
        Schema &$Schema,
        Table $tblPrivilege,
        Table $tblRight
    ) {

        $Table = $this->Connection->createTable( $Schema, 'tblPrivilegeRight' );
        $this->Connection->addForeignKey( $Table, $tblPrivilege );
        $this->Connection->addForeignKey( $Table, $tblRight );
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblAccess
     * @param Table  $tblPrivilege
     *
     * @return Table
     */
    private function setTableAccessPrivilege(
        Schema &$Schema,
        Table $tblAccess,
        Table $tblPrivilege
    ) {

        $Table = $this->Connection->createTable( $Schema, 'tblAccessPrivilege' );
        $this->Connection->addForeignKey( $Table, $tblAccess );
        $this->Connection->addForeignKey( $Table, $tblPrivilege );
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblRole
     * @param Table  $tblAccess
     *
     * @return Table
     */
    private function setTableRoleAccess(
        Schema &$Schema,
        Table $tblRole,
        Table $tblAccess
    ) {

        $Table = $this->Connection->createTable( $Schema, 'tblRoleAccess' );
        $this->Connection->addForeignKey( $Table, $tblRole );
        $this->Connection->addForeignKey( $Table, $tblAccess );
        return $Table;
    }
}
