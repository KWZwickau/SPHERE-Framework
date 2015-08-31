<?php
namespace SPHERE\Application\Contact\Mail\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Contact\Mail\Service
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
        $tblMail = $this->setTableMail($Schema);
        $tblType = $this->setTableType($Schema);
        $this->setTableToPerson($Schema, $tblMail, $tblType);
        $this->setTableToCompany($Schema, $tblMail, $tblType);
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
    private function setTableMail(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblMail');
        if (!$this->Connection->hasColumn('tblMail', 'Address')) {
            $Table->addColumn('Address', 'string');
        }
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
     * @param Table  $tblMail
     * @param Table  $tblType
     *
     * @return Table
     */
    private function setTableToPerson(Schema &$Schema, Table $tblMail, Table $tblType)
    {

        $Table = $this->Connection->createTable($Schema, 'tblToPerson');
        if (!$this->Connection->hasColumn('tblToPerson', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        if (!$this->Connection->hasColumn('tblToPerson', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->Connection->addForeignKey($Table, $tblMail);
        $this->Connection->addForeignKey($Table, $tblType);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblMail
     * @param Table  $tblType
     *
     * @return Table
     */
    private function setTableToCompany(Schema &$Schema, Table $tblMail, Table $tblType)
    {

        $Table = $this->Connection->createTable($Schema, 'tblToCompany');
        if (!$this->Connection->hasColumn('tblToCompany', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        if (!$this->Connection->hasColumn('tblToCompany', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }
        $this->Connection->addForeignKey($Table, $tblMail);
        $this->Connection->addForeignKey($Table, $tblType);
        return $Table;
    }
}
