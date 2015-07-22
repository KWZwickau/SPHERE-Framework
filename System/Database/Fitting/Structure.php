<?php
namespace SPHERE\System\Database\Fitting;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager as DBALSchemaManager;
use Doctrine\DBAL\Schema\Schema as DBALSchema;
use Doctrine\DBAL\Schema\Table as DBALTable;
use SPHERE\System\Database\Database;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Structure
 *
 * @package SPHERE\Application
 */
class Structure
{

    /** @var null|Database $Database */
    private $Database = null;

    /**
     * @param Identifier $Identifier
     */
    function __construct( Identifier $Identifier )
    {

        $this->Database = new Database( $Identifier );
    }

    /**
     * @return AbstractPlatform
     */
    public function getPlatform()
    {

        return $this->Database->getPlatform();
    }

    /**
     * @param DBALSchema $Schema
     * @param string $Name
     *
     * @return DBALTable
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function createTable( DBALSchema &$Schema, $Name )
    {

        if (!$this->Database->hasTable( $Name )) {
            $Table = $Schema->createTable( $Name );
            $Column = $Table->addColumn( 'Id', 'bigint' );
            $Column->setAutoincrement( true );
            $Table->setPrimaryKey( array( 'Id' ) );
        }
        $Table = $Schema->getTable( $Name );
        if (!$this->Database->hasColumn( $Name, 'EntityCreate' )) {
            $Table->addColumn( 'EntityCreate', 'datetime', array( 'notnull' => false ) );
        }
        if (!$this->Database->hasColumn( $Name, 'EntityUpdate' )) {
            $Table->addColumn( 'EntityUpdate', 'datetime', array( 'notnull' => false ) );
        }
        return $Table;
    }

    /**
     * @param DBALTable $KeyTarget Foreign Key (Column: KeySource Name)
     * @param DBALTable $KeySource Foreign Data (Column: Id)
     */
    public function addForeignKey( DBALTable &$KeyTarget, DBALTable $KeySource )
    {

        if (!$this->Database->hasColumn( $KeyTarget->getName(), $KeySource->getName() )) {
            $KeyTarget->addColumn( $KeySource->getName(), 'bigint' );
            if ($this->Database->getPlatform()->supportsForeignKeyConstraints()) {
                $KeyTarget->addForeignKeyConstraint( $KeySource, array( $KeySource->getName() ), array( 'Id' ) );
            }
        }
    }

    /**
     * @param DBALSchema $Schema
     * @param bool   $Simulate
     */
    public function setMigration( DBALSchema &$Schema, $Simulate = true )
    {

        $Statement = $this->Database->getSchema()->getMigrateToSql( $Schema,
            $this->Database->getPlatform()
        );
        if (!empty( $Statement )) {
            foreach ((array)$Statement as $Query) {
                $this->Database->addProtocol( $Query );
                if (!$Simulate) {
                    $this->Database->setStatement( $Query );
                }
            }
        }
    }

    /**
     * @param string $ViewName
     *
     * @return bool
     */
    public function hasView( $ViewName )
    {

        return $this->Database->hasView( $ViewName );
    }

    /**
     * @return DBALSchemaManager
     */
    public function getSchemaManager()
    {

        return $this->Database->getSchemaManager();
    }

    /**
     * @return DBALSchema
     */
    public function getSchema()
    {

        return $this->Database->getSchema();
    }

    /**
     * @param string $TableName
     * @param string $ColumnName
     *
     * @return bool
     */
    public function hasColumn( $TableName, $ColumnName )
    {

        return $this->Database->hasColumn( $TableName, $ColumnName );
    }

    /**
     * @param DBALTable $Table
     * @param array $ColumnList
     *
     * @return bool
     */
    public function hasIndex( DBALTable $Table, $ColumnList )
    {

        return $this->Database->hasIndex( $Table, $ColumnList );
    }

    /**
     * @param string $TableName
     *
     * @return bool
     */
    public function hasTable( $TableName )
    {

        return $this->Database->hasTable( $TableName );
    }

    /**
     * @param string $Item
     */
    public function addProtocol( $Item )
    {

        $this->Database->addProtocol( $Item );
    }


    /**
     * @param bool $Simulate
     *
     * @return string
     */
    public function getProtocol( $Simulate = false )
    {

        return $this->Database->getProtocol( $Simulate );
    }
}
