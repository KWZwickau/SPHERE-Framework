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
        $tblDebtor = $this->setTableDebtor($Schema);
        $tblItemValue = $this->setTableItemValue($Schema);
        $tblInvoice = $this->setTableInvoice($Schema);
        $this->setTableInvoiceItem($Schema, $tblInvoice, $tblItemValue, $tblDebtor);

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
    private function setTableDebtor(Schema $Schema)
    {

        $Table = $this->createTable($Schema, 'tblDebtor');
        $this->createColumn($Table, 'DebtorNumber', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'DebtorPerson', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BankReference', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Owner', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BankName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'IBAN', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BIC', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'serviceTblDebtor', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblBankReference', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblPaymentType', self::FIELD_TYPE_BIGINT, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableItemValue(Schema $Schema)
    {

        $Table = $this->createTable($Schema, 'tblItemValue');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_TEXT);
        if (!$this->getConnection()->hasColumn('tblItemValue', 'Value')) {
            $Table->addColumn('Value', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        $this->createColumn($Table, 'Quantity', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'serviceTblItem', self::FIELD_TYPE_BIGINT, true);

        return $Table;
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
        $this->createColumn($Table, 'TargetTime', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'SchoolName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'SchoolOwner', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'SchoolBankName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'SchoolIBAN', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'SchoolBIC', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'IsPaid', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($Table, 'IsReversal', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($Table, 'serviceTblAddress', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblMail', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblPhone', self::FIELD_TYPE_BIGINT, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblInvoice
     * @param Table  $tblItem
     * @param Table  $tblDebtor
     *
     * @return Table
     */
    private function setTableInvoiceItem(Schema $Schema, Table $tblInvoice, Table $tblItem, Table $tblDebtor)
    {

        $Table = $this->createTable($Schema, 'tblInvoiceItem');
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);
        $this->getConnection()->addForeignKey($Table, $tblInvoice);
        $this->getConnection()->addForeignKey($Table, $tblItem);
        $this->getConnection()->addForeignKey($Table, $tblDebtor);

        return $Table;
    }
}
