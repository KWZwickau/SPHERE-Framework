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
        $tblCalculation = $this->setTableCalculation($Schema);
        $this->setTableItemCalculation($Schema, $tblItem, $tblCalculation);
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

        $Table = $this->createTable($Schema, 'tblItemType');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);

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

        $Table = $this->createTable($Schema, 'tblItem');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_TEXT);
        $this->getConnection()->addForeignKey($Table, $tblItemType);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCalculation(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblCalculation');
        if (!$this->getConnection()->hasColumn('tblCalculation', 'Value')) {
            $Table->addColumn('Value', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        $this->createColumn($Table, 'serviceTblSiblingRank', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblType', self::FIELD_TYPE_BIGINT, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblItem
     * @param Table  $tblCalculation
     */
    private function setTableItemCalculation(Schema &$Schema, Table $tblItem, Table $tblCalculation)
    {

        $Table = $this->createTable($Schema, 'tblItemCalculation');
        $this->getConnection()->addForeignKey($Table, $tblItem);
        $this->getConnection()->addForeignKey($Table, $tblCalculation);
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblItem
     *
     * @return Table
     */
    private function setTableItemAccount(Schema &$Schema, Table $tblItem)
    {

        $Table = $this->createTable($Schema, 'tblItemAccount');
        $this->createColumn($Table, 'serviceTblAccount', self::FIELD_TYPE_BIGINT, true);
        $this->getConnection()->addForeignKey($Table, $tblItem);

        return $Table;
    }
}
