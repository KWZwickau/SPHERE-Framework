<?php
namespace SPHERE\Application\Billing\Inventory\Item\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Inventory\Item\Service
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
        $tblItemType = $this->setTableItemType($Schema);
        $tblItem = $this->setTableItem($Schema, $tblItemType);
        $this->setTableItemCondition($Schema, $tblItem);
        $this->setTableItemAccount($Schema, $tblItem);

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
    private function setTableItemType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblItemType');
        if (!$this->getConnection()->hasColumn('tblItemType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblItemType
     *
     * @return Table
     */
    private function setTableItem(Schema &$Schema, Table $tblItemType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblItem');
        if (!$this->getConnection()->hasColumn('tblItem', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblItem', 'Description')) {
            $Table->addColumn('Description', 'string');
        }

        $this->getConnection()->addForeignKey($Table, $tblItemType);

        return $Table;
    }

    private function setTableItemCondition(Schema &$Schema, Table $tblItem)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblItemCondition');
        if (!$this->getConnection()->hasColumn('tblItemCondition', 'Value')) {
            $Table->addColumn('Value', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        if (!$this->getConnection()->hasColumn('tblItemCondition', 'serviceStudentSiblingRank')) {
            $Table->addColumn('serviceStudentSiblingRank', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblItemCondition', 'serviceSchoolTblType')) {
            $Table->addColumn('serviceSchoolTblType', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->addForeignKey($Table, $tblItem);
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblItem
     *
     * @return Table
     */
    private function setTableItemAccount(Schema &$Schema, Table $tblItem)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblItemAccount');

        if (!$this->getConnection()->hasColumn('tblItemAccount', 'serviceBilling_Account')) {
            $Table->addColumn('serviceBilling_Account', 'bigint');
        }

        $this->getConnection()->addForeignKey($Table, $tblItem);

        return $Table;
    }
}
