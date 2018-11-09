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

        $tblPaymentType = $this->setTablePaymentType($Schema);
        $tblPayment = $this->setTablePayment($Schema, $tblPaymentType);
        $this->setTableInvoicePayment($Schema, $tblPayment);


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
    private function setTablePaymentType(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblPaymentType');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblPaymentType
     *
     * @return Table
     */
    private function setTablePayment(Schema &$Schema, Table $tblPaymentType)
    {

        $Table = $this->createTable($Schema, 'tblPayment');
        if (!$this->getConnection()->hasColumn('tblPayment', 'Value')) {
            $Table->addColumn('Value', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        $this->createColumn($Table, 'Purpose', self::FIELD_TYPE_STRING);
        $this->getConnection()->addForeignKey($Table, $tblPaymentType);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblPayment
     *
     * @return Table
     */
    private function setTableInvoicePayment(Schema &$Schema, Table $tblPayment)
    {

        $Table = $this->createTable($Schema, 'tblInvoicePayment');
        $this->createColumn($Table, 'serviceTblInvoice', self::FIELD_TYPE_BIGINT, true);
        $this->getConnection()->addForeignKey($Table, $tblPayment);

        return $Table;
    }
}
