<?php
namespace SPHERE\Application\Billing\Inventory\Commodity\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Inventory\Commodity\Service
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
        $tblComodity = $this->setTableCommodity($Schema);
        $this->setTableCommodityItem($Schema, $tblComodity);

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
    private function setTableCommodity(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblCommodity');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_TEXT);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCommodityItem(Schema &$Schema, Table $tblComodity)
    {

        $Table = $this->createTable($Schema, 'tblCommodityItem');
        if (!$this->getConnection()->hasColumn('tblCommodityItem', 'Quantity')) {
            $Table->addColumn('Quantity', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        if (!$this->getConnection()->hasColumn('tblCommodityItem', 'tblItem')) {
            $Table->addColumn('tblItem', 'bigint');
        }
        $this->getConnection()->addForeignKey($Table, $tblComodity);

        return $Table;
    }
}
