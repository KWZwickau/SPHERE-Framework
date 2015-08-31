<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service
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
        $this->setTableToken($Schema);
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
     * @throws SchemaException
     */
    private function setTableToken(Schema &$Schema)
    {

        /**
         * Install
         */
        $Table = $this->Connection->createTable($Schema, 'tblToken');
        /**
         * Upgrade
         */
        if (!$this->Connection->hasColumn('tblToken', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->Connection->hasIndex($Table, array('Identifier'))) {
            $Table->addUniqueIndex(array('Identifier'));
        }
        if (!$this->Connection->hasColumn('tblToken', 'Serial')) {
            $Table->addColumn('Serial', 'string', array('notnull' => false));
        }
        if (!$this->Connection->hasIndex($Table, array('Serial'))) {
            $Table->addUniqueIndex(array('Serial'));
        }
        if (!$this->Connection->hasColumn('tblToken', 'serviceTblConsumer')) {
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

        return $this->Connection->getSchema()->getTable('tblToken');
    }
}
