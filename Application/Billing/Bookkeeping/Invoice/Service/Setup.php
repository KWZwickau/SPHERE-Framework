<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service;

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
        $tblInvoice = $this->setTableInvoice($Schema);
        $tblInvoiceItem = $this->setTableInvoiceItem($Schema, $tblInvoice);
        $this->setTableInvoiceAccount($Schema, $tblInvoiceItem);

        $tblTempInvoice = $this->setTableTempInvoice($Schema);
        $this->setTableTempInvoiceCommodity($Schema, $tblTempInvoice);

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
    private function setTableInvoice(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblInvoice');
        if (!$this->Connection->hasColumn('tblInvoice', 'IsPaid')) {
            $Table->addColumn('IsPaid', 'boolean');
        }
        if (!$this->Connection->hasColumn('tblInvoice', 'Number')) {
            $Table->addColumn('Number', 'string');
        }
        if (!$this->Connection->hasColumn('tblInvoice', 'BasketName')) {
            $Table->addColumn('BasketName', 'string');
        }
        if (!$this->Connection->hasColumn('tblInvoice', 'IsVoid')) {
            $Table->addColumn('IsVoid', 'boolean');
        }
        if (!$this->Connection->hasColumn('tblInvoice', 'InvoiceDate')) {
            $Table->addColumn('InvoiceDate', 'date');
        }
        if (!$this->Connection->hasColumn('tblInvoice', 'PaymentDate')) {
            $Table->addColumn('PaymentDate', 'date');
        }
        if (!$this->Connection->hasColumn('tblInvoice', 'Discount')) {
            $Table->addColumn('Discount', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        if (!$this->Connection->hasColumn('tblInvoice', 'DebtorFirstName')) {
            $Table->addColumn('DebtorFirstName', 'string');
        }
        if (!$this->Connection->hasColumn('tblInvoice', 'DebtorLastName')) {
            $Table->addColumn('DebtorLastName', 'string');
        }
        if (!$this->Connection->hasColumn('tblInvoice', 'DebtorSalutation')) {
            $Table->addColumn('DebtorSalutation', 'string');
        }
        if (!$this->Connection->hasColumn('tblInvoice', 'DebtorNumber')) {
            $Table->addColumn('DebtorNumber', 'string');
        }
        if (!$this->Connection->hasColumn('tblInvoice', 'serviceManagement_Address')) {
            $Table->addColumn('serviceManagement_Address', 'bigint', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblInvoice', 'serviceManagement_Person')) {
            $Table->addColumn('serviceManagement_Person', 'bigint', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblInvoice', 'serviceBilling_Banking_Payment_Type')) {
            $Table->addColumn('serviceBilling_Banking_Payment_Type', 'bigint');
        }
        if (!$this->Connection->hasColumn('tblInvoice', 'IsPaymentDateModified')) {
            $Table->addColumn('IsPaymentDateModified', 'boolean');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblInvoice
     *
     * @return Table
     */
    private function setTableInvoiceItem(Schema &$Schema, Table $tblInvoice)
    {

        $Table = $this->Connection->createTable($Schema, 'tblInvoiceItem');

        if (!$this->Connection->hasColumn('tblInvoiceItem', 'CommodityDescription')) {
            $Table->addColumn('CommodityDescription', 'string');
        }
        if (!$this->Connection->hasColumn('tblInvoiceItem', 'CommodityName')) {
            $Table->addColumn('CommodityName', 'string');
        }
        if (!$this->Connection->hasColumn('tblInvoiceItem', 'ItemDescription')) {
            $Table->addColumn('ItemDescription', 'string');
        }
        if (!$this->Connection->hasColumn('tblInvoiceItem', 'ItemName')) {
            $Table->addColumn('ItemName', 'string');
        }
        if (!$this->Connection->hasColumn('tblInvoiceItem', 'ItemPrice')) {
            $Table->addColumn('ItemPrice', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        if (!$this->Connection->hasColumn('tblInvoiceItem', 'ItemQuantity')) {
            $Table->addColumn('ItemQuantity', 'decimal', array('precision' => 14, 'scale' => 4));
        }

        $this->Connection->addForeignKey($Table, $tblInvoice);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblInvoiceItem
     *
     * @return Table
     */
    private function setTableInvoiceAccount(Schema &$Schema, Table $tblInvoiceItem)
    {

        $Table = $this->Connection->createTable($Schema, 'tblInvoiceAccount');

        if (!$this->Connection->hasColumn('tblInvoiceAccount', 'serviceBilling_Account')) {
            $Table->addColumn('serviceBilling_Account', 'bigint');
        }

        $this->Connection->addForeignKey($Table, $tblInvoiceItem);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableTempInvoice(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblTempInvoice');

        if (!$this->Connection->hasColumn('tblTempInvoice', 'serviceBilling_Basket')) {
            $Table->addColumn('serviceBilling_Basket', 'bigint');
        }
        if (!$this->Connection->hasColumn('tblTempInvoice', 'serviceManagement_Person')) {
            $Table->addColumn('serviceManagement_Person', 'bigint');
        }
        if (!$this->Connection->hasColumn('tblTempInvoice', 'serviceBilling_Debtor')) {
            $Table->addColumn('serviceBilling_Debtor', 'bigint');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblTempInvoice
     *
     * @return Table
     */
    private function setTableTempInvoiceCommodity(Schema &$Schema, Table $tblTempInvoice)
    {

        $Table = $this->Connection->createTable($Schema, 'tblTempInvoiceCommodity');

        if (!$this->Connection->hasColumn('tblTempInvoiceCommodity', 'serviceBilling_Commodity')) {
            $Table->addColumn('serviceBilling_Commodity', 'bigint');
        }

        $this->Connection->addForeignKey($Table, $tblTempInvoice);

        return $Table;
    }
}
