<?php
namespace SPHERE\Application\People\Relationship\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Relationship\Service
 */
class Setup
{

    /** @var null|Structure $Connection */
    private $Connection = null;

    /**
     * @param Structure $Connection
     */
    function __construct(Structure $Connection)
    {

        $this->Connection = $Connection;
    }

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
        $Schema = clone $this->Connection->getSchema();
        $tblType = $this->setTableType($Schema);
        $this->setTableToPerson($Schema, $tblType);
        $this->setTableToCompany($Schema, $tblType);
        /**
         * Migration & Protocol
         */
        $this->Connection->addProtocol(__CLASS__);
        $this->Connection->setMigration($Schema, $Simulate);
        return $this->Connection->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableType(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblType');
        if (!$this->Connection->hasColumn('tblType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->Connection->hasColumn('tblType', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->Connection->hasColumn('tblType', 'IsLocked')) {
            $Table->addColumn('IsLocked', 'boolean');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblType
     *
     * @return Table
     */
    private function setTableToPerson(Schema &$Schema, Table $tblType)
    {

        $Table = $this->Connection->createTable($Schema, 'tblToPerson');
        if (!$this->Connection->hasColumn('tblToPerson', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        if (!$this->Connection->hasColumn('tblToPerson', 'serviceTblPersonFrom')) {
            $Table->addColumn('serviceTblPersonFrom', 'bigint', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblToPerson', 'serviceTblPersonTo')) {
            $Table->addColumn('serviceTblPersonTo', 'bigint', array('notnull' => false));
        }
        $this->Connection->addForeignKey($Table, $tblType);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblType
     *
     * @return Table
     */
    private function setTableToCompany(Schema &$Schema, Table $tblType)
    {

        $Table = $this->Connection->createTable($Schema, 'tblToCompany');
        if (!$this->Connection->hasColumn('tblToCompany', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        if (!$this->Connection->hasColumn('tblToCompany', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblToCompany', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->Connection->addForeignKey($Table, $tblType);
        return $Table;
    }
}
