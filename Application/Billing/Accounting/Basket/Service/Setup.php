<?php

namespace SPHERE\Application\Billing\Accounting\Basket\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Accounting\Basket\Service
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

        $tblBasketCommodity = $this->setTableBasketCommodity($Schema, $tblBasket);
        $this->setTableBasketCommodityDebtor($Schema, $tblBasketCommodity);
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

        $Table = $this->getConnection()->createTable($Schema, 'tblBasket');

        if (!$this->getConnection()->hasColumn('tblBasket', 'CreateDate')) {
            $Table->addColumn('CreateDate', 'datetime');
        }
        if (!$this->getConnection()->hasColumn('tblBasket', 'Name')) {
            $Table->addColumn('Name', 'string');
        }

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

        $Table = $this->getConnection()->createTable($Schema, 'tblBasketPerson');

        if (!$this->getConnection()->hasColumn('tblBasketPerson', 'serviceManagement_Person')) {
            $Table->addColumn('serviceManagement_Person', 'bigint');
        }

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

        $Table = $this->getConnection()->createTable($Schema, 'tblBasketItem');

        if (!$this->getConnection()->hasColumn('tblBasketItem', 'serviceBilling_CommodityItem')) {
            $Table->addColumn('serviceBilling_CommodityItem', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblBasketItem', 'Price')) {
            $Table->addColumn('Price', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        if (!$this->getConnection()->hasColumn('tblBasketItem', 'Quantity')) {
            $Table->addColumn('Quantity', 'decimal', array('precision' => 14, 'scale' => 4));
        }

        $this->getConnection()->addForeignKey($Table, $tblBasket);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblBasket
     *
     * @return Table
     */
    private function setTableBasketCommodity(Schema &$Schema, Table $tblBasket)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblBasketCommodity');

        if (!$this->getConnection()->hasColumn('tblBasketCommodity', 'serviceManagement_Person')) {
            $Table->addColumn('serviceManagement_Person', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblBasketCommodity', 'serviceBilling_Commodity')) {
            $Table->addColumn('serviceBilling_Commodity', 'bigint');
        }

        $this->getConnection()->addForeignKey($Table, $tblBasket);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblBasketCommodity
     *
     * @return Table
     */
    private function setTableBasketCommodityDebtor(Schema &$Schema, Table $tblBasketCommodity)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblBasketCommodityDebtor');

        if (!$this->getConnection()->hasColumn('tblBasketCommodityDebtor', 'serviceBilling_Debtor')) {
            $Table->addColumn('serviceBilling_Debtor', 'bigint');
        }

        $this->getConnection()->addForeignKey($Table, $tblBasketCommodity);

        return $Table;
    }
}
