<?php
namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Bookkeeping\Invoice\Service
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
        $tblInvoice = $this->setTableInvoice($Schema);
        $tblInvoiceItem = $this->setTableInvoiceItem($Schema, $tblInvoice);
//        $tblOrder = $this->setTableOrder($Schema);
//        $this->setTableOrderItem($Schema, $tblOrder);
        $this->setTableInvoiceAccount($Schema, $tblInvoiceItem);

        $tblTempInvoice = $this->setTableTempInvoice($Schema);
        $this->setTableTempInvoiceCommodity($Schema, $tblTempInvoice);

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
    private function setTableInvoice(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblInvoice');
        if (!$this->getConnection()->hasColumn('tblInvoice', 'IsPaid')) {
            $Table->addColumn('IsPaid', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'Number')) {
            $Table->addColumn('Number', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'BasketName')) {
            $Table->addColumn('BasketName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'IsVoid')) {
            $Table->addColumn('IsVoid', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'InvoiceDate')) {
            $Table->addColumn('InvoiceDate', 'date');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'PaymentDate')) {
            $Table->addColumn('PaymentDate', 'date');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'Discount')) {
            $Table->addColumn('Discount', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'DebtorFirstName')) {
            $Table->addColumn('DebtorFirstName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'DebtorLastName')) {
            $Table->addColumn('DebtorLastName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'DebtorSalutation')) {
            $Table->addColumn('DebtorSalutation', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'DebtorNumber')) {
            $Table->addColumn('DebtorNumber', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'serviceTblAddress')) {
            $Table->addColumn('serviceTblAddress', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'serviceTblPaymentType')) {
            $Table->addColumn('serviceTblPaymentType', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'IsPaymentDateModified')) {
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

        $Table = $this->getConnection()->createTable($Schema, 'tblInvoiceItem');

        if (!$this->getConnection()->hasColumn('tblInvoiceItem', 'CommodityDescription')) {
            $Table->addColumn('CommodityDescription', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblInvoiceItem', 'CommodityName')) {
            $Table->addColumn('CommodityName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblInvoiceItem', 'ItemDescription')) {
            $Table->addColumn('ItemDescription', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblInvoiceItem', 'ItemName')) {
            $Table->addColumn('ItemName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblInvoiceItem', 'ItemPrice')) {
            $Table->addColumn('ItemPrice', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        if (!$this->getConnection()->hasColumn('tblInvoiceItem', 'ItemQuantity')) {
            $Table->addColumn('ItemQuantity', 'decimal', array('precision' => 14, 'scale' => 4));
        }

        $this->getConnection()->addForeignKey($Table, $tblInvoice);

        return $Table;
    }

//    /**
//     * @param Schema $Schema
//     *
//     * @return Table
//     */
//    private function setTableOrder(Schema &$Schema)
//    {
//
//        $Table = $this->getConnection()->createTable($Schema, 'tblOrder');
//        if (!$this->getConnection()->hasColumn('tblOrder', 'BasketName')) {
//            $Table->addColumn('BasketName', 'string');
//        }
//        if (!$this->getConnection()->hasColumn('tblInvoice', 'InvoiceDate')) {
//            $Table->addColumn('InvoiceDate', 'date');
//        }
//        if (!$this->getConnection()->hasColumn('tblInvoice', 'PaymentDate')) {
//            $Table->addColumn('PaymentDate', 'date');
//        }
//        if (!$this->getConnection()->hasColumn('tblOrder', 'Discount')) {
//            $Table->addColumn('Discount', 'decimal', array('precision' => 14, 'scale' => 4));
//        }
//        if (!$this->getConnection()->hasColumn('tblOrder', 'DebtorFirstName')) {
//            $Table->addColumn('DebtorFirstName', 'string');
//        }
//        if (!$this->getConnection()->hasColumn('tblOrder', 'DebtorLastName')) {
//            $Table->addColumn('DebtorLastName', 'string');
//        }
//        if (!$this->getConnection()->hasColumn('tblOrder', 'DebtorSalutation')) {
//            $Table->addColumn('DebtorSalutation', 'string');
//        }
//        if (!$this->getConnection()->hasColumn('tblOrder', 'DebtorNumber')) {
//            $Table->addColumn('DebtorNumber', 'string');
//        }
//        if (!$this->getConnection()->hasColumn('tblOrder', 'serviceManagement_Address')) {
//            $Table->addColumn('serviceManagement_Address', 'bigint', array('notnull' => false));
//        }
//        if (!$this->getConnection()->hasColumn('tblOrder', 'serviceManagement_Person')) {
//            $Table->addColumn('serviceManagement_Person', 'bigint', array('notnull' => false));
//        }
//        if (!$this->getConnection()->hasColumn('tblOrder', 'serviceBilling_Banking_Payment_Type')) {
//            $Table->addColumn('serviceBilling_Banking_Payment_Type', 'bigint');
//        }
//        if (!$this->getConnection()->hasColumn('tblOrder', 'IsPaymentDateModified')) {
//            $Table->addColumn('IsPaymentDateModified', 'boolean');
//        }
//
//        return $Table;
//    }

//    /**
//     * @param Schema $Schema
//     * @param Table  $tblOrder
//     *
//     * @return Table
//     */
//    private function setTableOrderItem(Schema &$Schema, Table $tblOrder)
//    {
//
//        $Table = $this->getConnection()->createTable($Schema, 'tblOrderItem');
//
//        if (!$this->getConnection()->hasColumn('tblOrderItem', 'CommodityDescription')) {
//            $Table->addColumn('CommodityDescription', 'string');
//        }
//        if (!$this->getConnection()->hasColumn('tblOrderItem', 'CommodityName')) {
//            $Table->addColumn('CommodityName', 'string');
//        }
//        if (!$this->getConnection()->hasColumn('tblOrderItem', 'ItemDescription')) {
//            $Table->addColumn('ItemDescription', 'string');
//        }
//        if (!$this->getConnection()->hasColumn('tblOrderItem', 'ItemName')) {
//            $Table->addColumn('ItemName', 'string');
//        }
//        if (!$this->getConnection()->hasColumn('tblOrderItem', 'ItemPrice')) {
//            $Table->addColumn('ItemPrice', 'decimal', array('precision' => 14, 'scale' => 4));
//        }
//        if (!$this->getConnection()->hasColumn('tblOrderItem', 'ItemQuantity')) {
//            $Table->addColumn('ItemQuantity', 'decimal', array('precision' => 14, 'scale' => 4));
//        }
//
//        $this->getConnection()->addForeignKey($Table, $tblOrder);
//
//        return $Table;
//    }

    /**
     * @param Schema $Schema
     * @param Table  $tblInvoiceItem
     *
     * @return Table
     */
    private function setTableInvoiceAccount(Schema &$Schema, Table $tblInvoiceItem)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblInvoiceAccount');

        if (!$this->getConnection()->hasColumn('tblInvoiceAccount', 'serviceTblAccount')) {
            $Table->addColumn('serviceTblAccount', 'bigint');
        }

        $this->getConnection()->addForeignKey($Table, $tblInvoiceItem);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableTempInvoice(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblTempInvoice');

        if (!$this->getConnection()->hasColumn('tblTempInvoice', 'serviceBilling_Basket')) {
            $Table->addColumn('serviceBilling_Basket', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblTempInvoice', 'serviceManagement_Person')) {
            $Table->addColumn('serviceManagement_Person', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblTempInvoice', 'serviceBilling_Debtor')) {
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

        $Table = $this->getConnection()->createTable($Schema, 'tblTempInvoiceCommodity');

        if (!$this->getConnection()->hasColumn('tblTempInvoiceCommodity', 'serviceBilling_Commodity')) {
            $Table->addColumn('serviceBilling_Commodity', 'bigint');
        }

        $this->getConnection()->addForeignKey($Table, $tblTempInvoice);

        return $Table;
    }
}
