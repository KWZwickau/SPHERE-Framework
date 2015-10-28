<?php
namespace SPHERE\Application\Billing\Accounting\Banking\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Accounting\Banking\Service
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
        $tblDebtor = $this->setTableDebtor($Schema, $tblPaymentType);
        $this->setTableAccount($Schema, $tblDebtor);
        $this->setTableDebtorCommodity($Schema, $tblDebtor);
        $this->setTableReference($Schema, $tblDebtor);

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

        $Table = $this->getConnection()->createTable($Schema, 'tblPaymentType');

        if (!$this->getConnection()->hasColumn('tblPaymentType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblPaymentType
     *
     * @return Table
     */
    private function setTableDebtor(Schema &$Schema, Table $tblPaymentType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblDebtor');
        if (!$this->getConnection()->hasColumn('tblDebtor', 'DebtorNumber')) {
            $Table->addColumn('DebtorNumber', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblDebtor', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblDebtor', 'ServiceManagementPerson')) {
            $Table->addColumn('ServiceManagementPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->addForeignKey($Table, $tblPaymentType);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblDebtor
     *
     * @return Table
     */
    private function setTableAccount(Schema &$Schema, Table $tblDebtor)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblAccount');
//        if (!$this->getConnection()->hasColumn('tblAccount', 'LeadTimeFirst')) {
//            $Table->addColumn('LeadTimeFirst', 'integer');
//        }
//        if (!$this->getConnection()->hasColumn('tblAccount', 'LeadTimeFollow')) {
//            $Table->addColumn('LeadTimeFollow', 'integer');
//        }
        if (!$this->getConnection()->hasColumn('tblAccount', 'BankName')) {
            $Table->addColumn('BankName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblAccount', 'IBAN')) {
            $Table->addColumn('IBAN', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblAccount', 'BIC')) {
            $Table->addColumn('BIC', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblAccount', 'Owner')) {
            $Table->addColumn('Owner', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblAccount', 'CashSign')) {
            $Table->addColumn('CashSign', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblAccount', 'Active')) {
            $Table->addColumn('Active', 'boolean');
        }

        $this->getConnection()->addForeignKey($Table, $tblDebtor);

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

        $Table = $this->getConnection()->createTable($Schema, 'tblDebtorCommodity');

        if (!$this->getConnection()->hasColumn('tblDebtorCommodity', 'serviceBilling_Commodity')) {
            $Table->addColumn('serviceBilling_Commodity', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->addForeignKey($Table, $tblDebtor);
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

        $Table = $this->getConnection()->createTable($Schema, 'tblReference');

        if (!$this->getConnection()->hasColumn('tblReference', 'Reference')) {
            $Table->addColumn('Reference', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblReference', 'isVoid')) {
            $Table->addColumn('isVoid', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblReference', 'ReferenceDate')) {
            $Table->addColumn('ReferenceDate', 'date', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblReference', 'serviceBilling_Commodity')) {
            $Table->addColumn('serviceBilling_Commodity', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblReference', 'tblAccount')) {
            $Table->addColumn('tblAccount', 'bigint', array('notnull' => false));
        }

        $this->getConnection()->addForeignKey($Table, $tblDebtor);
        return $Table;
    }
}
