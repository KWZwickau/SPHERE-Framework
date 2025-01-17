<?php
namespace SPHERE\System\Database\Binding;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class AbstractSetup
 *
 * @package SPHERE\System\Database\Binding
 */
abstract class AbstractSetup
{

    const FIELD_TYPE_BIGINT = 'bigint';
    const FIELD_TYPE_STRING = 'string';
    const FIELD_TYPE_TEXT = 'text';
    const FIELD_TYPE_INTEGER = 'integer';
    const FIELD_TYPE_BOOLEAN = 'boolean';
    const FIELD_TYPE_DATETIME = 'datetime';
    const FIELD_TYPE_BINARY = 'blob';
    const FIELD_TYPE_FLOAT = 'float';
    const FIELD_TYPE_SMALLINT = 'smallint';

    /** @var null|Structure $Connection */
    private $Connection = null;

    /**
     * @param Structure $Connection
     */
    final public function __construct(Structure $Connection)
    {

        $this->Connection = $Connection;
    }

    /**
     * @param bool $Simulate
     * @param bool $IsCollation
     *
     * @return string
     */
    abstract public function setupDatabaseSchema($Simulate = true, $IsCollation = false);

    /**
     * @return Schema
     */
    final protected function loadSchema()
    {

        return clone $this->getConnection()->getSchema();
    }

    /**
     * @return Structure
     */
    final protected function getConnection()
    {

        return $this->Connection;
    }

    /**
     * @param Schema $Schema
     * @param bool   $Simulate
     *
     * @return string Protocol
     */
    final protected function saveSchema(Schema $Schema, $Simulate = true)
    {

        $this->getConnection()->addProtocol(debug_backtrace()[1]['class'].' @'.$Schema->getName());
        $this->getConnection()->setMigration($Schema, $Simulate);
        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * Create / Update: Table
     *
     * @param Schema $Schema
     * @param string|AbstractEntity $Name
     *
     * @return Table
     */
    final protected function createTable(Schema $Schema, $Name)
    {
        if( $Name instanceof AbstractEntity ) {
            $Name = $Name->getEntityShortName();
        }

        if (!$Schema->hasTable($Name)) {
            return $this->getConnection()->createTable($Schema, $Name);
        } else {
            return $Schema->getTable($Name);
        }
    }

    /**
     * Create / Update: Column
     *
     * @param Table $Table
     * @param string $Name
     * @param string $Type
     * @param bool $IsNull
     * @param null $Default
     *
     * @return Table
     */
    final protected function createColumn(Table $Table, $Name, $Type = self::FIELD_TYPE_STRING, $IsNull = false, $Default = null)
    {

        if (!$this->getConnection()->hasColumn($Table->getName(), $Name)) {
            if( $Default === null ) {
                $Table->addColumn($Name, $Type, array('notnull' => $IsNull ? false : true));
            } else {
                $Table->addColumn($Name, $Type, array('notnull' => $IsNull ? false : true, 'default' => $Default));
            }
        } else {
            $Column = $Table->getColumn($Name);
            // Definition has changed?
            if ($Column->getNotnull() == $IsNull
                || $Column->getType()->getName() != $Type
                || $Column->getDefault() != $Default
            ) {
                $Table->changeColumn($Name, array(
                    'notnull' => $IsNull ? false : true,
                    'type'    => Type::getType($Type),
                    'default'    => $Default
                ));
            }
        }
        return $Table;
    }

    /**
     * Table: Column exists
     *
     * @param Table $Table
     * @param string $Name
     * @return bool
     */
    final protected function hasColumn( Table $Table, $Name ) {

        return $this->getConnection()->hasColumn( $Table->getName(), $Name );
    }

    /**
     * Drop: Index
     *
     * @param Table $Table
     * @param array $FieldList Column-Names
     *
     * @return Table
     */
    final protected function removeIndex(Table $Table, $FieldList)
    {

        $this->getConnection()->removeIndex($Table, $FieldList);
        return $Table;
    }

    /**
     * Create: Index
     *
     * @param Table $Table
     * @param array $FieldList Column-Names
     * @param bool  $IsUnique
     *
     * @return Table
     */
    final protected function createIndex(Table $Table, $FieldList, $IsUnique = true)
    {

        if (!$this->getConnection()->hasIndex($Table, $FieldList)) {
            if ($IsUnique) {
                $Table->addUniqueIndex($FieldList);
            } else {
                $Table->addIndex($FieldList);
            }
        }
        return $Table;
    }

    /**
     * Create: Foreign-Key
     *
     * [Table] Insert new Column (Column-Name equals ForeignTable-Name)
     *
     * [ForeignTable] Index to Table on Column: "Id"
     *
     * @param Table $Table
     * @param Table $ForeignTable
     * @param bool $IsNull
     *
     * @return Table
     */
    final protected function createForeignKey(Table $Table, Table $ForeignTable, $IsNull = false)
    {

        $this->getConnection()->addForeignKey($Table, $ForeignTable, $IsNull);
        return $Table;
    }

    /**
     * Create: Service-Key
     *
     * [Table] Insert new Column (Column-Name equals ForeignTable-Name, Replacing "tbl[..]" -> "serviceTbl[..]")
     *
     * [ServiceTable] Index to Table on Column: "Id", Without Foreign-Key Constrain, Null
     *
     * @param Table $Table
     * @param string|AbstractEntity|Element|Table $ServiceTable
     *
     * @return Table
     */
    final protected function createServiceKey(Table $Table, $ServiceTable)
    {

        if( $ServiceTable instanceof AbstractEntity || $ServiceTable instanceof Element ) {
            $Name = $ServiceTable->getEntityShortName();
        } else if( $ServiceTable instanceof Table ) {
            $Name = $ServiceTable->getName();
        } else {
            $Name = $ServiceTable;
        }
        $Name = preg_replace( '!^tbl!is', 'serviceTbl', $Name );
        $this->createColumn( $Table, $Name, self::FIELD_TYPE_BIGINT, true );
        return $Table;
    }
}
