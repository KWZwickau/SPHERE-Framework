<?php
namespace SPHERE\Application\Platform\System\Archive\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Platform\System\Archive\Service
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

        $Schema = clone $this->Connection->getSchema();
        $this->setTableArchive($Schema);
        /**
         * Migration & Archive
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
    private function setTableArchive(Schema &$Schema)
    {

        /**
         * Install
         */
        $Table = $this->Connection->createTable($Schema, 'tblArchive');
        /**
         * Upgrade
         */
        if (!$this->Connection->hasColumn('tblArchive', 'ArchiveType')) {
            $Table->addColumn('ArchiveType', 'integer');
        }
        if (!$this->Connection->hasColumn('tblArchive', 'ArchiveDatabase')) {
            $Table->addColumn('ArchiveDatabase', 'string', array('notnull' => false));
        }
        if (!$this->Connection->hasIndex($Table, array('ArchiveDatabase'))) {
            $Table->addIndex(array('ArchiveDatabase'));
        }
        if (!$this->Connection->hasColumn('tblArchive', 'ArchiveTimestamp')) {
            $Table->addColumn('ArchiveTimestamp', 'integer', array('notnull' => false));
        }
        if (!$this->Connection->hasIndex($Table, array('ArchiveTimestamp'))) {
            $Table->addIndex(array('ArchiveTimestamp'));
        }
        // Editor
        if (!$this->Connection->hasColumn('tblArchive', 'serviceTblAccount')) {
            $Table->addColumn('serviceTblAccount', 'bigint', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblArchive', 'AccountUsername')) {
            $Table->addColumn('AccountUsername', 'string', array('notnull' => false));
        }
        if (!$this->Connection->hasIndex($Table, array('AccountUsername'))) {
            $Table->addIndex(array('AccountUsername'));
        }
        // Consumer
        if (!$this->Connection->hasColumn('tblArchive', 'serviceTblConsumer')) {
            $Table->addColumn('serviceTblConsumer', 'bigint', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblArchive', 'ConsumerName')) {
            $Table->addColumn('ConsumerName', 'string', array('notnull' => false));
        }
        if (!$this->Connection->hasIndex($Table, array('ConsumerName'))) {
            $Table->addIndex(array('ConsumerName'));
        }
        if (!$this->Connection->hasColumn('tblArchive', 'ConsumerAcronym')) {
            $Table->addColumn('ConsumerAcronym', 'string', array('notnull' => false));
        }
        if (!$this->Connection->hasIndex($Table, array('ConsumerAcronym'))) {
            $Table->addIndex(array('ConsumerAcronym'));
        }
        // Data
        if (!$this->Connection->hasColumn('tblArchive', 'Entity')) {
            $Table->addColumn('Entity', 'text', array('notnull' => false));
        }

        return $Table;
    }
}
