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
        $this->setTableInvoiceDebtor($Schema, $tblInvoice);
        $this->setTableInvoiceCreditor($Schema, $tblInvoice);
        $this->setTableInvoiceCauser($Schema, $tblInvoice);
        $this->setTableInvoiceItemValue($Schema, $tblInvoice);

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

        $Table = $this->createTable($Schema, 'tblInvoice');
        $this->createColumn($Table, 'InvoiceNumber', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'IntegerNumber', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'Year', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Month', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'TargetTime', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'IsPaid', self::FIELD_TYPE_BOOLEAN);
//        $this->createColumn($Table, 'IsReversal', self::FIELD_TYPE_BOOLEAN);
//        $this->createColumn($Table, 'serviceTblAddress', self::FIELD_TYPE_BIGINT, true);
//        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);
//        $this->createColumn($Table, 'serviceTblMail', self::FIELD_TYPE_BIGINT, true);
//        $this->createColumn($Table, 'serviceTblPhone', self::FIELD_TYPE_BIGINT, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblInvoice
     *
     * @return Table
     */
    private function setTableInvoiceDebtor(Schema $Schema, Table $tblInvoice)
    {

        $Table = $this->createTable($Schema, 'tblInvoiceDebtor');
        $this->createColumn($Table, 'DebtorNumber', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'DebtorPerson', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BankReference', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Owner', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BankName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'IBAN', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BIC', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'serviceTblPersonDebtor', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblBankReference', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblPaymentType', self::FIELD_TYPE_BIGINT, true);
        $this->getConnection()->addForeignKey($Table, $tblInvoice);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblInvoice
     *
     * @return Table
     */
    private function setTableInvoiceCreditor(Schema $Schema, Table $tblInvoice)
    {

        $Table = $this->createTable($Schema, 'tblInvoiceCreditor');
        $this->createColumn($Table, 'SchoolName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Owner', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BankName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'IBAN', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BIC', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'serviceTblCreditor', self::FIELD_TYPE_BIGINT, true);
        $this->getConnection()->addForeignKey($Table, $tblInvoice);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblInvoice
     *
     * @return Table
     */
    private function setTableInvoiceCauser(Schema $Schema, Table $tblInvoice)
    {

        $Table = $this->createTable($Schema, 'tblInvoiceItem');
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);
        $this->getConnection()->addForeignKey($Table, $tblInvoice);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblInvoice
     *
     * @return Table
     */
    private function setTableInvoiceItemValue(Schema $Schema, Table $tblInvoice)
    {

        $Table = $this->createTable($Schema, 'tblInvoiceItemValue');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_TEXT);
        if (!$this->getConnection()->hasColumn('tblInvoiceItemValue', 'Value')) {
            $Table->addColumn('Value', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        $this->createColumn($Table, 'Quantity', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'serviceTblItem', self::FIELD_TYPE_BIGINT, true);
        $this->getConnection()->addForeignKey($Table, $tblInvoice);

        return $Table;
    }
}
