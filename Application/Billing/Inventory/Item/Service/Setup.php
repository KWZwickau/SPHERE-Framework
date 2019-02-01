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
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true, $UTF8 = false)
    {

        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();
        $tblItemType = $this->setTableItemType($Schema);
        $tblItem = $this->setTableItem($Schema, $tblItemType);
        $this->setTableItemGroup($Schema, $tblItem);
        $tblVariant = $this->setTableItemVariant($Schema, $tblItem);
        $this->setTableItemCalculation($Schema, $tblVariant);
        $this->setTableItemAccount($Schema, $tblItem);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($Schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }
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
        $this->createColumn($Table, 'Amount', self::FIELD_TYPE_INTEGER);
        $this->getConnection()->addForeignKey($Table, $tblItemType);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblItem
     *
     * @return Table
     */
    private function setTableItemGroup(Schema &$Schema, Table $tblItem)
    {

        $Table = $this->createTable($Schema, 'tblItemGroup');
        $this->getConnection()->addForeignKey($Table, $tblItem);
        $this->createColumn($Table, 'serviceTblGroup', self::FIELD_TYPE_BIGINT);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblItem
     *
     * @return Table
     */
    private function setTableItemVariant(Schema &$Schema, Table $tblItem)
    {

        $Table = $this->createTable($Schema, 'tblItemVariant');

        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_TEXT);
        $this->createForeignKey($Table, $tblItem, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblVariant
     *
     * @return Table
     */
    private function setTableItemCalculation(Schema &$Schema, Table $tblVariant)
    {

        $Table = $this->createTable($Schema, 'tblItemCalculation');
        if(!$this->getConnection()->hasColumn('tblItemCalculation', 'Value')){
            $Table->addColumn('Value', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        $this->createColumn($Table, 'DateFrom', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'DateTo', self::FIELD_TYPE_DATETIME, true);
//        $this->createColumn($Table, 'serviceTblType', self::FIELD_TYPE_BIGINT, true);
        $this->createForeignKey($Table, $tblVariant, true);

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

        $Table = $this->createTable($Schema, 'tblItemAccount');
        $this->createColumn($Table, 'serviceTblAccount', self::FIELD_TYPE_BIGINT, true);
        $this->getConnection()->addForeignKey($Table, $tblItem);

        return $Table;
    }
}
