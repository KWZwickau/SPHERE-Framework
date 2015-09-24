<?php
namespace SPHERE\Application\Setting\Consumer\School\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Setting\Consumer\School\Service
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
        $this->setTableSchool($Schema, $tblType);
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

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param        $tblType
     *
     * @return Table
     */
    private function setTableSchool(Schema &$Schema, $tblType)
    {

        $Table = $this->Connection->createTable($Schema, 'tblSchool');
        if (!$this->Connection->hasColumn('tblSchool', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint');
        }

        $this->Connection->addForeignKey($Table, $tblType);

        return $Table;
    }
}
