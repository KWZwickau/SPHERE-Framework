<?php

namespace SPHERE\Application\Billing\Inventory\Item\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

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
        $tblItem = $this->setTableItem($Schema);
        $this->setTableItemAccount($Schema, $tblItem);

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
     */
    private function setTableItem(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblItem');
        if (!$this->Connection->hasColumn('tblItem', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->Connection->hasColumn('tblItem', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->Connection->hasColumn('tblItem', 'Price')) {
            $Table->addColumn('Price', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        if (!$this->Connection->hasColumn('tblItem', 'CostUnit')) {
            $Table->addColumn('CostUnit', 'string');
        }
        if (!$this->Connection->hasColumn('tblItem', 'serviceManagement_Student_ChildRank')) {
            $Table->addColumn('serviceManagement_Student_ChildRank', 'bigint', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblItem', 'serviceManagement_Course')) {
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

        $Table = $this->Connection->createTable($Schema, 'tblItemAccount');

        if (!$this->Connection->hasColumn('tblItemAccount', 'serviceBilling_Account')) {
            $Table->addColumn('serviceBilling_Account', 'bigint');
        }

        $this->Connection->addForeignKey($Table, $tblItem);

        return $Table;
    }
}
