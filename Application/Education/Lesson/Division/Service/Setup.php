<?php
namespace SPHERE\Application\Education\Lesson\Division\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Education\Lesson\Division\Service
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
        $tblLevel = $this->setTableLevel($Schema);
        $tblGroup = $this->setTableGroup($Schema, $tblLevel);
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
    private function setTableLevel(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblLevel');
        if (!$this->getConnection()->hasColumn('tblLevel', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblLevel', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblLevel
     *
     * @return Table
     */
    private function setTableGroup(Schema &$Schema, Table $tblLevel)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblGroup');
        if (!$this->getConnection()->hasColumn('tblGroup', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblGroup', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblGroup', 'serviceTblYear')) {
            $Table->addColumn('serviceTblYear', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->addForeignKey($Table, $tblLevel);
        return $Table;
    }
}
