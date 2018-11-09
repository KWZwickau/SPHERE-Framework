<?php

namespace SPHERE\Application\Billing\Bookkeeping\Basket\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Bookkeeping\Basket\Service
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
        $tblBasket = $this->setTableBasket($Schema);
        $this->setTableBasketPerson($Schema, $tblBasket);
        $this->setTableBasketItem($Schema, $tblBasket);
        $this->setTblBasketVerification($Schema, $tblBasket);

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
    private function setTableBasket(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblBasket');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_TEXT);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblBasket
     *
     * @return Table
     */
    private function setTableBasketPerson(Schema &$Schema, Table $tblBasket)
    {

        $Table = $this->createTable($Schema, 'tblBasketPerson');
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);
        $this->getConnection()->addForeignKey($Table, $tblBasket);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblBasket
     *
     * @return Table
     */
    private function setTableBasketItem(Schema &$Schema, Table $tblBasket)
    {

        $Table = $this->createTable($Schema, 'tblBasketItem');
        $this->createColumn($Table, 'serviceTblItem', self::FIELD_TYPE_BIGINT, true);
        $this->getConnection()->addForeignKey($Table, $tblBasket);

        return $Table;
    }

    private function setTblBasketVerification(Schema &$Schema, Table $tblBasket)
    {

        $Table = $this->createTable($Schema, 'tblBasketVerification');
        if (!$this->getConnection()->hasColumn('tblBasketVerification', 'Value')) {
            $Table->addColumn('Value', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        $this->createColumn($Table, 'Quantity', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblItem', self::FIELD_TYPE_BIGINT, true);
        $this->getConnection()->addForeignKey($Table, $tblBasket);

        return $Table;
    }
}
