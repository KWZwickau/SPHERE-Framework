<?php
namespace SPHERE\Application\People\Person\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Person\Service
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
        $tblSalutation = $this->setTableSalutation($Schema);
        $this->setTablePerson($Schema, $tblSalutation);
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
    private function setTableSalutation(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblSalutation');
        if (!$this->Connection->hasColumn('tblSalutation', 'Salutation')) {
            $Table->addColumn('Salutation', 'string');
        }
        if (!$this->Connection->hasColumn('tblSalutation', 'IsLocked')) {
            $Table->addColumn('IsLocked', 'boolean');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblSalutation
     *
     * @return Table
     */
    private function setTablePerson(Schema &$Schema, Table $tblSalutation)
    {

        $Table = $this->Connection->createTable($Schema, 'tblPerson');
        if (!$this->Connection->hasColumn('tblPerson', 'Title')) {
            $Table->addColumn('Title', 'string');
        }
        if (!$this->Connection->hasColumn('tblPerson', 'FirstName')) {
            $Table->addColumn('FirstName', 'string');
        }
        if (!$this->Connection->hasColumn('tblPerson', 'SecondName')) {
            $Table->addColumn('SecondName', 'string');
        }
        if (!$this->Connection->hasColumn('tblPerson', 'LastName')) {
            $Table->addColumn('LastName', 'string');
        }
        $this->Connection->addForeignKey($Table, $tblSalutation);
        return $Table;
    }
}
