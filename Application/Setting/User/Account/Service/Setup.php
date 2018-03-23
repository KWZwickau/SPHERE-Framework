<?php
namespace SPHERE\Application\Setting\User\Account\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Setup
 * @package SPHERE\Application\Setting\User\Account\Service
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
        $this->setTableUserAccount($Schema);
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
     */
    private function setTableUserAccount(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblUserAccount');
        if (!$this->getConnection()->hasColumn('tblUserAccount', 'serviceTblAccount')) {
            $Table->addColumn('serviceTblAccount', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblUserAccount', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint');
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
        }
        if (!$this->getConnection()->hasColumn('tblUserAccount', 'Type')) {
            $Table->addColumn('Type', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblUserAccount', 'UserPassword')) {
            $Table->addColumn('UserPassword', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblUserAccount', 'AccountPassword')) {
            $Table->addColumn('AccountPassword', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblUserAccount', 'ExportDate')) {
            $Table->addColumn('ExportDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblUserAccount', 'LastDownloadAccount')) {
            $Table->addColumn('LastDownloadAccount', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblUserAccount', 'GroupByTime')) {
            $Table->addColumn('GroupByTime', 'datetime');
        }
        $this->getConnection()->getSchema()->getTableNames();

        return $Table;
    }
}
