<?php
namespace SPHERE\Application\Contact\Address\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Contact\Address\Service
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
        $tblCity = $this->setTableCity($Schema);
        $tblState = $this->setTableState($Schema);
        $tblAddress = $this->setTableAddress($Schema, $tblCity, $tblState);
        $tblType = $this->setTableType($Schema);
        $this->setTableToPerson($Schema, $tblAddress, $tblType);
        $this->setTableToCompany($Schema, $tblAddress, $tblType);
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
    private function setTableCity(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblCity');
        if (!$this->Connection->hasColumn('tblCity', 'Code')) {
            $Table->addColumn('Code', 'string');
        }
        if (!$this->Connection->hasColumn('tblCity', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->Connection->hasColumn('tblCity', 'District')) {
            $Table->addColumn('District', 'string', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableState(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblState');
        if (!$this->Connection->hasColumn('tblState', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->Connection->hasIndex($Table, array('Name'))) {
            $Table->addUniqueIndex(array('Name'));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblCity
     * @param Table  $tblState
     *
     * @return Table
     */
    private function setTableAddress(Schema &$Schema, Table $tblCity, Table $tblState)
    {

        $Table = $this->Connection->createTable($Schema, 'tblAddress');
        if (!$this->Connection->hasColumn('tblAddress', 'StreetName')) {
            $Table->addColumn('StreetName', 'string');
        }
        if (!$this->Connection->hasColumn('tblAddress', 'StreetNumber')) {
            $Table->addColumn('StreetNumber', 'string');
        }
        if (!$this->Connection->hasColumn('tblAddress', 'PostOfficeBox')) {
            $Table->addColumn('PostOfficeBox', 'string');
        }
        $this->Connection->addForeignKey($Table, $tblCity);
        $this->Connection->addForeignKey($Table, $tblState);
        return $Table;
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

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblAddress
     * @param Table  $tblType
     *
     * @return Table
     */
    private function setTableToPerson(Schema &$Schema, Table $tblAddress, Table $tblType)
    {

        $Table = $this->Connection->createTable($Schema, 'tblToPerson');
        if (!$this->Connection->hasColumn('tblToPerson', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        if (!$this->Connection->hasColumn('tblToPerson', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->Connection->addForeignKey($Table, $tblAddress);
        $this->Connection->addForeignKey($Table, $tblType);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblAddress
     * @param Table  $tblType
     *
     * @return Table
     */
    private function setTableToCompany(Schema &$Schema, Table $tblAddress, Table $tblType)
    {

        $Table = $this->Connection->createTable($Schema, 'tblToCompany');
        if (!$this->Connection->hasColumn('tblToCompany', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        if (!$this->Connection->hasColumn('tblToCompany', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }
        $this->Connection->addForeignKey($Table, $tblAddress);
        $this->Connection->addForeignKey($Table, $tblType);
        return $Table;
    }
}
