<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Bookkeeping\Balance\Service
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

        $tblBalance = $this->setTableBalance($Schema);
        $this->setTablePayment($Schema, $tblBalance);
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
    private function setTableBalance(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblBalance');

        if (!$this->getConnection()->hasColumn('tblBalance', 'serviceBilling_Banking')) {
            $Table->addColumn('serviceBilling_Banking', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblBalance', 'serviceBilling_Invoice')) {
            $Table->addColumn('serviceBilling_Invoice', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblBalance', 'ExportDate')) {
            $Table->addColumn('ExportDate', 'date', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblBalance', 'BankName')) {
            $Table->addColumn('BankName', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblBalance', 'IBAN')) {
            $Table->addColumn('IBAN', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblBalance', 'BIC')) {
            $Table->addColumn('BIC', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblBalance', 'Owner')) {
            $Table->addColumn('Owner', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblBalance', 'CashSign')) {
            $Table->addColumn('CashSign', 'string', array('notnull' => false));
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblBalance
     *
     * @return Table
     */
    private function setTablePayment(Schema &$Schema, Table $tblBalance)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblPayment');

        if (!$this->getConnection()->hasColumn('tblPayment', 'Value')) {
            $Table->addColumn('Value', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        if (!$this->getConnection()->hasColumn('tblPayment', 'Date')) {
            $Table->addColumn('Date', 'date');
        }

        $this->getConnection()->addForeignKey($Table, $tblBalance);

        return $Table;
    }
}
