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
        $tblDebtor = $this->setTableDebtor($Schema);
        $tblInvoice = $this->setTableInvoice($Schema, $tblDebtor);
        $tblItem = $this->setTableItem($Schema);
        $this->setTableInvoiceItem($Schema, $tblInvoice, $tblItem);

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
    public function setTableDebtor(Schema $Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblDebtor');
        if (!$this->getConnection()->hasColumn('tblDebtor', 'DebtorNumber')) {
            $Table->addColumn('DebtorNumber', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblDebtor', 'DebtorPerson')) {
            $Table->addColumn('DebtorPerson', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblDebtor', 'Reference')) {
            $Table->addColumn('Reference', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    public function setTableItem(Schema $Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblItem');
        if (!$this->getConnection()->hasColumn('tblItem', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblItem', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblItem', 'Value')) {
            $Table->addColumn('Value', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        if (!$this->getConnection()->hasColumn('tblItem', 'Quantity')) {
            $Table->addColumn('Quantity', 'int');
        }
        if (!$this->getConnection()->hasColumn('tblItem', 'serviceTblItem')) {
            $Table->addColumn('serviceTblItem', 'bigint', array('notnull' => false));
        }

        return $Table;
    }


    /**
     * @param Schema $Schema
     * @param Table  $tblDebtor
     *
     * @return Table
     */
    private function setTableInvoice(Schema &$Schema, Table $tblDebtor)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblInvoice');
        if (!$this->getConnection()->hasColumn('tblInvoice', 'InvoiceNumber')) {
            $Table->addColumn('InvoiceNumber', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblInvoice', 'BasketName')) {
            $Table->addColumn('BasketName', 'string');
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
        $this->getConnection()->addForeignKey($Table, $tblDebtor);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblInvoice
     * @param Table  $tblItem
     *
     * @return Table
     */
    public function setTableInvoiceItem(Schema $Schema, Table $tblInvoice, Table $tblItem)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblInvoiceItem');
        $this->getConnection()->addForeignKey($Table, $tblInvoice);
        $this->getConnection()->addForeignKey($Table, $tblItem);

        return $Table;
    }
}
