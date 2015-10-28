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
        if (!$this->getConnection()->hasColumn('tblInvoice', 'serviceManagement_Address')) {
            $Table->addColumn('serviceManagement_Address', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'serviceManagement_Person')) {
            $Table->addColumn('serviceManagement_Person', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'serviceBilling_Banking_Payment_Type')) {
            $Table->addColumn('serviceBilling_Banking_Payment_Type', 'bigint');
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

    /**
     * @param Schema $Schema
     * @param Table  $tblInvoiceItem
     *
     * @return Table
     */
    private function setTableInvoiceAccount(Schema &$Schema, Table $tblInvoiceItem)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblInvoiceAccount');

        if (!$this->getConnection()->hasColumn('tblInvoiceAccount', 'serviceBilling_Account')) {
            $Table->addColumn('serviceBilling_Account', 'bigint');
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
