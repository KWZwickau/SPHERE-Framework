<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service
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

        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();
        $this->setTableToken($Schema);
        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);
        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     * @throws SchemaException
     */
    private function setTableToken(Schema &$Schema)
    {

        /**
         * Install
         */
        $Table = $this->getConnection()->createTable($Schema, 'tblToken');
        /**
         * Upgrade
         */
        if (!$this->getConnection()->hasColumn('tblToken', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->getConnection()->hasIndex($Table, array('Identifier'))) {
            $Table->addUniqueIndex(array('Identifier'));
        }
        if (!$this->getConnection()->hasColumn('tblToken', 'Serial')) {
            $Table->addColumn('Serial', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasIndex($Table, array('Serial'))) {
            $Table->addUniqueIndex(array('Serial'));
        }
        if (!$this->getConnection()->hasColumn('tblToken', 'serviceTblConsumer')) {
            $Table->addColumn('serviceTblConsumer', 'bigint', array('notnull' => false));
        }

        return $Table;
    }

    /**
     * @return Table
     * @throws SchemaException
     */
    public function getTableToken()
    {

        return $this->getConnection()->getSchema()->getTable('tblToken');
    }
}
