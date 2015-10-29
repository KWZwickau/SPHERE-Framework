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
        $tblItem = $this->setTableItem($Schema);
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
    private function setTableItem(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblItem');
        if (!$this->getConnection()->hasColumn('tblItem', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblItem', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblItem', 'Price')) {
            $Table->addColumn('Price', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        if (!$this->getConnection()->hasColumn('tblItem', 'CostUnit')) {
            $Table->addColumn('CostUnit', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblItem', 'serviceManagement_Student_ChildRank')) {
            $Table->addColumn('serviceManagement_Student_ChildRank', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblItem', 'serviceManagement_Course')) {
            $Table->addColumn('serviceManagement_Course', 'bigint', array('notnull' => false));
        }

        return $Table;
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
