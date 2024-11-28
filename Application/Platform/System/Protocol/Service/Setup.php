<?php
namespace SPHERE\Application\Platform\System\Protocol\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Platform\System\Protocol\Service
 */
class Setup extends AbstractSetup
{

    /**
     * @param bool $Simulate
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true, $UTF8 = false)
    {

        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();
        $this->setTableProtocol($Schema);
        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($Schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }
        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableProtocol(Schema &$Schema)
    {

        /**
         * Install
         */
        $Table = $this->getConnection()->createTable($Schema, 'tblProtocol');
        /**
         * Upgrade
         */
        // System
        if (!$this->getConnection()->hasColumn('tblProtocol', 'ProtocolDatabase')) {
            $Table->addColumn('ProtocolDatabase', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasIndex($Table, array('ProtocolDatabase'))) {
            $Table->addIndex(array('ProtocolDatabase'));
        }
        if (!$this->getConnection()->hasColumn('tblProtocol', 'ProtocolTimestamp')) {
            $Table->addColumn('ProtocolTimestamp', 'integer', array('notnull' => false));
        }
        if (!$this->getConnection()->hasIndex($Table, array('ProtocolTimestamp'))) {
            $Table->addIndex(array('ProtocolTimestamp'));
        }
        // Editor
        if (!$this->getConnection()->hasColumn('tblProtocol', 'serviceTblAccount')) {
            $Table->addColumn('serviceTblAccount', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblProtocol', 'AccountUsername')) {
            $Table->addColumn('AccountUsername', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasIndex($Table, array('AccountUsername'))) {
            $Table->addIndex(array('AccountUsername'));
        }
        // Consumer
        if (!$this->getConnection()->hasColumn('tblProtocol', 'serviceTblConsumer')) {
            $Table->addColumn('serviceTblConsumer', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblProtocol', 'ConsumerName')) {
            $Table->addColumn('ConsumerName', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasIndex($Table, array('ConsumerName'))) {
            $Table->addIndex(array('ConsumerName'));
        }
        if (!$this->getConnection()->hasColumn('tblProtocol', 'ConsumerAcronym')) {
            $Table->addColumn('ConsumerAcronym', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasIndex($Table, array('ConsumerAcronym'))) {
            $Table->addIndex(array('ConsumerAcronym'));
        }
        // Data
        if (!$this->getConnection()->hasColumn('tblProtocol', 'EntityFrom')) {
            $Table->addColumn('EntityFrom', 'text', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblProtocol', 'EntityTo')) {
            $Table->addColumn('EntityTo', 'text', array('notnull' => false));
        }
        if (!$this->getConnection()->hasIndex($Table, array(Element::ENTITY_CREATE))) {
            $Table->addIndex(array(Element::ENTITY_CREATE));
        }

        return $Table;
    }
}
