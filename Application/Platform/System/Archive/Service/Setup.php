<?php
namespace SPHERE\Application\Platform\System\Archive\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Platform\System\Archive\Service
 */
class Setup extends AbstractSetup
{

    /**
     * @param bool $Simulate
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true)
    {

        $Schema = clone $this->getConnection()->getSchema();
        $this->setTableArchive($Schema);
        /**
         * Migration & Archive
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);
        return $this->getConnection()->getProtocol($Simulate);
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
        $Table = $this->getConnection()->createTable($Schema, 'tblArchive');
        /**
         * Upgrade
         */
        if (!$this->getConnection()->hasColumn('tblArchive', 'ArchiveType')) {
            $Table->addColumn('ArchiveType', 'integer');
        }
        if (!$this->getConnection()->hasColumn('tblArchive', 'ArchiveDatabase')) {
            $Table->addColumn('ArchiveDatabase', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasIndex($Table, array('ArchiveDatabase'))) {
            $Table->addIndex(array('ArchiveDatabase'));
        }
        if (!$this->getConnection()->hasColumn('tblArchive', 'ArchiveTimestamp')) {
            $Table->addColumn('ArchiveTimestamp', 'integer', array('notnull' => false));
        }
        if (!$this->getConnection()->hasIndex($Table, array('ArchiveTimestamp'))) {
            $Table->addIndex(array('ArchiveTimestamp'));
        }
        // Editor
        if (!$this->getConnection()->hasColumn('tblArchive', 'serviceTblAccount')) {
            $Table->addColumn('serviceTblAccount', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblArchive', 'AccountUsername')) {
            $Table->addColumn('AccountUsername', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasIndex($Table, array('AccountUsername'))) {
            $Table->addIndex(array('AccountUsername'));
        }
        // Consumer
        if (!$this->getConnection()->hasColumn('tblArchive', 'serviceTblConsumer')) {
            $Table->addColumn('serviceTblConsumer', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblArchive', 'ConsumerName')) {
            $Table->addColumn('ConsumerName', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasIndex($Table, array('ConsumerName'))) {
            $Table->addIndex(array('ConsumerName'));
        }
        if (!$this->getConnection()->hasColumn('tblArchive', 'ConsumerAcronym')) {
            $Table->addColumn('ConsumerAcronym', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasIndex($Table, array('ConsumerAcronym'))) {
            $Table->addIndex(array('ConsumerAcronym'));
        }
        // Data
        if (!$this->getConnection()->hasColumn('tblArchive', 'Entity')) {
            $Table->addColumn('Entity', 'text', array('notnull' => false));
        }

        return $Table;
    }
}
