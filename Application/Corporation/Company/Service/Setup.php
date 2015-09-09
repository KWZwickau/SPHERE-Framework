<?php
namespace SPHERE\Application\Corporation\Company\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Corporation\Company\Service
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
        $this->setTableCompany($Schema);
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
    private function setTableCompany(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblCompany');
        if (!$this->Connection->hasColumn('tblCompany', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->Connection->hasColumn('tblCompany', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }
}
