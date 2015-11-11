<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service
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
        $tblAccount = $this->setTableAccount($Schema);
        $tblIdentification = $this->setTableIdentification($Schema);
        $this->setTableSession($Schema, $tblAccount);
        $this->setTableAuthorization($Schema, $tblAccount);
        $this->setTableAuthentication($Schema, $tblAccount, $tblIdentification);
        $this->setTableUser($Schema, $tblAccount);
        $this->setTableSetting($Schema, $tblAccount);
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
    private function setTableAccount(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblAccount');
        if (!$this->getConnection()->hasColumn('tblAccount', 'Username')) {
            $Table->addColumn('Username', 'string');
        }
        if (!$this->getConnection()->hasIndex($Table, array('Username'))) {
            $Table->addUniqueIndex(array('Username'));
        }
        if (!$this->getConnection()->hasColumn('tblAccount', 'Password')) {
            $Table->addColumn('Password', 'string');
        }
        if (!$this->getConnection()->hasIndex($Table, array('Username', 'Password'))) {
            $Table->addIndex(array('Username', 'Password'));
        }
        if (!$this->getConnection()->hasColumn('tblAccount', 'serviceTblToken')) {
            $Table->addColumn('serviceTblToken', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblAccount', 'serviceTblConsumer')) {
            $Table->addColumn('serviceTblConsumer', 'bigint', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableIdentification(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblIdentification');
        if (!$this->getConnection()->hasColumn('tblIdentification', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasIndex($Table, array('Name'))) {
            $Table->addUniqueIndex(array('Name'));
        }
        if (!$this->getConnection()->hasColumn('tblIdentification', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->getConnection()->hasIndex($Table, array('Description'))) {
            $Table->addIndex(array('Description'));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblAccount
     *
     * @return Table
     */
    private function setTableSession(Schema &$Schema, Table $tblAccount)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblSession');
        if (!$this->getConnection()->hasColumn('tblSession', 'Session')) {
            $Table->addColumn('Session', 'string');
        }
        if (!$this->getConnection()->hasIndex($Table, array('Session'))) {
            $Table->addIndex(array('Session'));
        }
        if (!$this->getConnection()->hasColumn('tblSession', 'Timeout')) {
            $Table->addColumn('Timeout', 'integer');
        }
        $this->getConnection()->addForeignKey($Table, $tblAccount);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @param Table  $tblAccount
     *
     * @return Table
     */
    private function setTableAuthorization(Schema &$Schema, Table $tblAccount)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblAuthorization');
        if (!$this->getConnection()->hasColumn('tblAuthorization', 'serviceTblRole')) {
            $Table->addColumn('serviceTblRole', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->addForeignKey($Table, $tblAccount);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @param Table  $tblAccount
     * @param Table  $tblIdentification
     *
     * @return Table
     */
    private function setTableAuthentication(Schema &$Schema, Table $tblAccount, Table $tblIdentification)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblAuthentication');
        $this->getConnection()->addForeignKey($Table, $tblAccount);
        $this->getConnection()->addForeignKey($Table, $tblIdentification);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblAccount
     *
     * @return Table
     */
    private function setTableUser(Schema &$Schema, Table $tblAccount)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblUser');
        if (!$this->getConnection()->hasColumn('tblUser', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->addForeignKey($Table, $tblAccount);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblAccount
     *
     * @return Table
     */
    private function setTableSetting(Schema &$Schema, Table $tblAccount)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblSetting');
        if (!$this->getConnection()->hasColumn('tblSetting', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblSetting', 'Value')) {
            $Table->addColumn('Value', 'string');
        }
        $this->getConnection()->addForeignKey($Table, $tblAccount);
        return $Table;
    }
}
