<?php
namespace SPHERE\Application\Setting\User\Account\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

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
        if (!$this->getConnection()->hasColumn('tblUserAccount', 'serviceTblToPersonAddress')) {
            $Table->addColumn('serviceTblToPersonAddress', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblUserAccount', 'serviceTblToPersonMail')) {
            $Table->addColumn('serviceTblToPersonMail', 'bigint', array('notnull' => false));
        }
//        if (!$this->getConnection()->hasColumn('tblUserAccount', 'UserName')) {
//            $Table->addColumn('UserName', 'string');
//        }
        if (!$this->getConnection()->hasColumn('tblUserAccount', 'UserPassword')) {
            $Table->addColumn('UserPassword', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblUserAccount', 'IsSend')) {
            $Table->addColumn('IsSend', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblUserAccount', 'IsExport')) {
            $Table->addColumn('IsExport', 'boolean');
        }

        return $Table;
    }
}
