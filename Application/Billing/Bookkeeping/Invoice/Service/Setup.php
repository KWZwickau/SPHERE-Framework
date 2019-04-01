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
        $tblInvoiceCreditor = $this->setTableInvoiceCreditor($Schema);
        $tblInvoice = $this->setTableInvoice($Schema, $tblInvoiceCreditor);
        $this->setTableInvoiceItemDebtor($Schema, $tblInvoice);

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
    private function setTableInvoiceCreditor(Schema $Schema)
    {

        $Table = $this->createTable($Schema, 'tblInvoiceCreditor');
        $this->createColumn($Table, 'CreditorId', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'SchoolName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Owner', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BankName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'IBAN', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BIC', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'serviceTblCreditor', self::FIELD_TYPE_BIGINT, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblInvoiceCreditor
     *
     * @return Table
     */
    private function setTableInvoice(Schema &$Schema, Table $tblInvoiceCreditor)
    {

        $Table = $this->createTable($Schema, 'tblInvoice');
        $this->createColumn($Table, 'InvoiceNumber', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'IntegerNumber', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'Year', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Month', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'TargetTime', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'FirstName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'LastName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BasketName', self::FIELD_TYPE_STRING);
        $this->getConnection()->addForeignKey($Table, $tblInvoiceCreditor);
        $this->createColumn($Table, 'serviceTblPersonCauser', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblBasket', self::FIELD_TYPE_BIGINT, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblInvoice
     *
     * @return Table
     */
    private function setTableInvoiceItemDebtor(Schema $Schema, Table $tblInvoice)
    {

        $Table = $this->createTable($Schema, 'tblInvoiceItemDebtor');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_TEXT);
        if(!$this->getConnection()->hasColumn('tblInvoiceItemDebtor', 'Value')){
            $Table->addColumn('Value', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        $this->createColumn($Table, 'Quantity', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'DebtorNumber', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'DebtorPerson', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BankReference', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Owner', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BankName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'IBAN', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BIC', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'IsPaid', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($Table, 'serviceTblItem', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblPersonDebtor', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblBankReference', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblBankAccount', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblPaymentType', self::FIELD_TYPE_BIGINT, true);
        $this->getConnection()->addForeignKey($Table, $tblInvoice);

        return $Table;
    }
}
