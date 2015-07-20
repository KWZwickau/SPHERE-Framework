<?php
namespace SPHERE\Application\System\Gatekeeper\Account\Data;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class DataStructure
 *
 * @package SPHERE\Application\System\Gatekeeper\Account\Data
 */
class DataStructure
{
    /** @var null|Structure $Structure */
    private $Structure = null;

    /**
     *
     */
    function __construct()
    {

        $this->Structure = new Structure(
            new Identifier( 'System', 'Gatekeeper', 'Account' )
        );
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
        $Schema = clone $this->Structure->getSchema();
        $tblAccountRole = $this->setTableAccountRole( $Schema );
        $tblAccountType = $this->setTableAccountType( $Schema );
        $tblAccount = $this->setTableAccount( $Schema,
            Gatekeeper::DataToken()->getTableToken(),
            Gatekeeper::DataConsumer()->getTableConsumer(),
            $tblAccountType,
            $tblAccountRole
        );
        $this->setTableAccountSession( $Schema,
            $tblAccount
        );
        $this->setTableAccountAccessList( $Schema,
            $tblAccountRole,
            Gatekeeper::DataAccess()->getTableAccess()
        );
        /**
         * Migration & Protocol
         */
        $this->Structure->addProtocol( __CLASS__ );
        $this->Structure->setMigration( $Schema, $Simulate );
        return $this->Structure->getProtocol( $Simulate );
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     * @throws SchemaException
     */
    private function setTableAccountRole( Schema &$Schema )
    {

        /**
         * Install
         */
        $Table = $this->Structure->createTable( $Schema, 'tblAccountRole' );
        /**
         * Upgrade
         */
        if (!$this->Structure->hasColumn( 'tblAccountRole', 'Name' )) {
            $Table->addColumn( 'Name', 'string' );
        }
        if (!$this->Structure->hasIndex( $Table, array( 'Name' ) )) {
            $Table->addUniqueIndex( array( 'Name' ) );
        }
        return $Table;
    }


    /**
     * @param Schema $Schema
     *
     * @return Table
     * @throws SchemaException
     */
    private function setTableAccountType( Schema &$Schema )
    {

        /**
         * Install
         */
        $Table = $this->Structure->createTable( $Schema, 'tblAccountType' );
        /**
         * Upgrade
         */
        if (!$this->Structure->hasColumn( 'tblAccountType', 'Name' )) {
            $Table->addColumn( 'Name', 'string' );
        }
        if (!$this->Structure->hasIndex( $Table, array( 'Name' ) )) {
            $Table->addUniqueIndex( array( 'Name' ) );
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblToken
     * @param Table  $tblConsumer
     * @param Table  $tblAccountType
     * @param Table  $tblAccountRole
     *
     * @throws SchemaException
     * @return Table
     */
    private function setTableAccount(
        Schema &$Schema,
        Table $tblToken,
        Table $tblConsumer,
        Table $tblAccountType,
        Table $tblAccountRole
    ) {

        /**
         * Install
         */
        $Table = $this->Structure->createTable( $Schema, 'tblAccount' );
        /**
         * Upgrade
         */
        if (!$this->Structure->hasColumn( 'tblAccount', 'Username' )) {
            $Table->addColumn( 'Username', 'string' );
        }
        if (!$this->Structure->hasIndex( $Table, array( 'Username' ) )) {
            $Table->addUniqueIndex( array( 'Username' ) );
        }
        if (!$this->Structure->hasColumn( 'tblAccount', 'Password' )) {
            $Table->addColumn( 'Password', 'string' );
        }
        if (!$this->Structure->hasIndex( $Table, array( 'Username', 'Password' ) )) {
            $Table->addIndex( array( 'Username', 'Password' ) );
        }
        $this->Structure->addForeignKey( $Table, $tblAccountType );
        $this->Structure->addForeignKey( $Table, $tblAccountRole );
        if (!$this->Structure->hasColumn( 'tblAccount', 'DataGatekeeper_Token' )) {
            $Table->addColumn( 'DataGatekeeper_Token', 'bigint', array( 'notnull' => false ) );
            if ($this->Structure->getDatabasePlatform()->supportsForeignKeyConstraints()) {
                $Table->addForeignKeyConstraint( $tblToken, array( 'DataGatekeeper_Token' ), array( 'Id' ) );
            }
        }
        if (!$this->Structure->hasColumn( 'tblAccount', 'DataGatekeeper_Consumer' )) {
            $Table->addColumn( 'DataGatekeeper_Consumer', 'bigint', array( 'notnull' => false ) );
            if ($this->Structure->getDatabasePlatform()->supportsForeignKeyConstraints()) {
                $Table->addForeignKeyConstraint( $tblConsumer, array( 'DataGatekeeper_Consumer' ), array( 'Id' ) );
            }
        }
        if (!$this->Structure->hasColumn( 'tblAccount', 'DataManagement_Person' )) {
            $Table->addColumn( 'DataManagement_Person', 'bigint', array( 'notnull' => false ) );
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblAccount
     *
     * @return Table
     * @throws SchemaException
     */
    private function setTableAccountSession( Schema &$Schema, Table $tblAccount )
    {

        /**
         * Install
         */
        $Table = $this->Structure->createTable( $Schema, 'tblAccountSession' );
        /**
         * Upgrade
         */
        if (!$this->Structure->hasColumn( 'tblAccountSession', 'Session' )) {
            $Table->addColumn( 'Session', 'string' );
        }
        if (!$this->Structure->hasIndex( $Table, array( 'Session' ) )) {
            $Table->addIndex( array( 'Session' ) );
        }
        if (!$this->Structure->hasColumn( 'tblAccountSession', 'Timeout' )) {
            $Table->addColumn( 'Timeout', 'integer' );
        }
        $this->Structure->addForeignKey( $Table, $tblAccount );
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblAccountRole
     * @param Table  $tblAccess
     *
     * @throws SchemaException
     * @return Table
     */
    private function setTableAccountAccessList(
        Schema &$Schema,
        Table $tblAccountRole,
        Table $tblAccess
    ) {

        /**
         * Install
         */
        $Table = $this->Structure->createTable( $Schema, 'tblAccountAccessList' );
        /**
         * Upgrade
         */
        $this->Structure->addForeignKey( $Table, $tblAccountRole );
        if (!$this->Structure->hasColumn( 'tblAccountAccessList', 'DataGatekeeper_Access' )) {
            $Table->addColumn( 'DataGatekeeper_Access', 'bigint', array( 'notnull' => false ) );
            if ($this->Structure->getDatabasePlatform()->supportsForeignKeyConstraints()) {
                $Table->addForeignKeyConstraint( $tblAccess, array( 'DataGatekeeper_Access' ), array( 'Id' ) );
            }
        }
        return $Table;
    }

    /**
     * @return Table
     * @throws SchemaException
     */
    protected function getTableAccount()
    {

        return $this->Structure->getSchema()->getTable( 'tblAccount' );
    }

}
