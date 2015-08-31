<?php

namespace SPHERE\Application\Billing\Accounting\Banking\Service;

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

        $tblPaymentType = $this->setTablePaymentType($Schema);
        $tblDebtor = $this->setTableDebtor($Schema, $tblPaymentType);
        $this->setTableDebtorCommodity($Schema, $tblDebtor);
        $this->setTableReference($Schema, $tblDebtor);

        /**
         * Migration & Protocol
         */
        $this->Connection->addProtocol(__CLASS__);
        $this->Connection->setMigration($Schema, $Simulate);
        return $this->Connection->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblPaymentType
     *
     * @return Table tblDebtorCommodity
     */
    private function setTableDebtor(Schema &$Schema, Table $tblPaymentType)
    {

        $Table = $this->Connection->createTable($Schema, 'tblDebtor');
        if (!$this->Connection->hasColumn('tblDebtor', 'DebtorNumber')) {
            $Table->addColumn('DebtorNumber', 'string');
        }
        if (!$this->Connection->hasColumn('tblDebtor', 'LeadTimeFirst')) {
            $Table->addColumn('LeadTimeFirst', 'integer');
        }
        if (!$this->Connection->hasColumn('tblDebtor', 'LeadTimeFollow')) {
            $Table->addColumn('LeadTimeFollow', 'integer');
        }
        if (!$this->Connection->hasColumn('tblDebtor', 'BankName')) {
            $Table->addColumn('BankName', 'string');
        }
        if (!$this->Connection->hasColumn('tblDebtor', 'IBAN')) {
            $Table->addColumn('IBAN', 'string');
        }
        if (!$this->Connection->hasColumn('tblDebtor', 'BIC')) {
            $Table->addColumn('BIC', 'string');
        }
        if (!$this->Connection->hasColumn('tblDebtor', 'Owner')) {
            $Table->addColumn('Owner', 'string');
        }
        if (!$this->Connection->hasColumn('tblDebtor', 'CashSign')) {
            $Table->addColumn('CashSign', 'string');
        }
        if (!$this->Connection->hasColumn('tblDebtor', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->Connection->hasColumn('tblDebtor', 'ServiceManagementPerson')) {
            $Table->addColumn('ServiceManagementPerson', 'bigint', array('notnull' => false));
        }

        $this->Connection->addForeignKey($Table, $tblPaymentType);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblDebtor
     *
     * @return Table
     */
    private function setTableDebtorCommodity(Schema &$Schema, Table $tblDebtor)
    {

        $Table = $this->Connection->createTable($Schema, 'tblDebtorCommodity');

        if (!$this->Connection->hasColumn('tblDebtorCommodity', 'serviceBilling_Commodity')) {
            $Table->addColumn('serviceBilling_Commodity', 'bigint');
        }

        $this->Connection->addForeignKey($Table, $tblDebtor);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblDebtor
     *
     * @return Table
     */
    private function setTableReference(Schema &$Schema, Table $tblDebtor)
    {

        $Table = $this->Connection->createTable($Schema, 'tblReference');

        if (!$this->Connection->hasColumn('tblReference', 'Reference')) {
            $Table->addColumn('Reference', 'string');
        }
        if (!$this->Connection->hasColumn('tblReference', 'isVoid')) {
            $Table->addColumn('isVoid', 'boolean');
        }
        if (!$this->Connection->hasColumn('tblReference', 'ReferenceDate')) {
            $Table->addColumn('ReferenceDate', 'date', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblReference', 'serviceBilling_Commodity')) {
            $Table->addColumn('serviceBilling_Commodity', 'bigint');
        }

        $this->Connection->addForeignKey($Table, $tblDebtor);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTablePaymentType(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblPaymentType');

        if (!$this->Connection->hasColumn('tblPaymentType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }

        return $Table;
    }
}
