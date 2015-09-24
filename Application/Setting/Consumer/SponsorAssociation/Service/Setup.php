<?php
namespace SPHERE\Application\Setting\Consumer\SponsorAssociation\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Setting\Consumer\SponsorAssociation\Service
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
        $this->setTableSponsorAssociation($Schema);
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
    private function setTableSponsorAssociation(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblSponsorAssociation');
        if (!$this->Connection->hasColumn('tblSponsorAssociation', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint');
        }

        return $Table;
    }
}
