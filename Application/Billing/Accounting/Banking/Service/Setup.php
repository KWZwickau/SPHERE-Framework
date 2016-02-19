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

//        $this->setTablePaymentType($Schema);
        $this->setTableDebtor($Schema);
        $this->setTableBankAccount($Schema);
        $this->setTableBankReference($Schema);
//        $this->setTableDebtorCommodity($Schema, $tblDebtor);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);
        return $this->getConnection()->getProtocol($Simulate);
    }

//    /**
//     * @param Schema $Schema
//     *
//     * @return Table
//     */
//    private function setTablePaymentType(Schema &$Schema)
//    {
//
//        $Table = $this->getConnection()->createTable($Schema, 'tblPaymentType');
//
//        if (!$this->getConnection()->hasColumn('tblPaymentType', 'Name')) {
//            $Table->addColumn('Name', 'string');
//        }
//
//        return $Table;
//    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableDebtor(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblDebtor');
        if (!$this->getConnection()->hasColumn('tblDebtor', 'DebtorNumber')) {
            $Table->addColumn('DebtorNumber', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblDebtor', 'ServicePeople_Person')) {
            $Table->addColumn('ServicePeople_Person', 'bigint', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableBankAccount(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblBankAccount');
//        if (!$this->getConnection()->hasColumn('tblAccount', 'LeadTimeFirst')) {
//            $Table->addColumn('LeadTimeFirst', 'integer');
//        }
//        if (!$this->getConnection()->hasColumn('tblAccount', 'LeadTimeFollow')) {
//            $Table->addColumn('LeadTimeFollow', 'integer');
//        }
        if (!$this->getConnection()->hasColumn('tblBankAccount', 'BankName')) {
            $Table->addColumn('BankName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblBankAccount', 'IBAN')) {
            $Table->addColumn('IBAN', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblBankAccount', 'BIC')) {
            $Table->addColumn('BIC', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblBankAccount', 'Owner')) {
            $Table->addColumn('Owner', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblBankAccount', 'CashSign')) {
            $Table->addColumn('CashSign', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblBankAccount', 'ServicePeople_Person')) {
            $Table->addColumn('ServicePeople_Person', 'bigint', array('notnull' => false));
        }


        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableBankReference(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblBankReference');

        if (!$this->getConnection()->hasColumn('tblBankReference', 'Reference')) {
            $Table->addColumn('Reference', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblBankReference', 'ReferenceDate')) {
            $Table->addColumn('ReferenceDate', 'date', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblBankReference', 'isVoid')) {
            $Table->addColumn('isVoid', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblBankReference', 'ServicePeople_Person')) {
            $Table->addColumn('ServicePeople_Person', 'bigint', array('notnull' => false));
        }
        return $Table;
    }

//    /**
//     * @param Schema $Schema
//     * @param Table  $tblDebtor
//     *
//     * @return Table
//     */
//    private function setTableDebtorCommodity(Schema &$Schema, Table $tblDebtor)
//    {
//
//        $Table = $this->getConnection()->createTable($Schema, 'tblDebtorCommodity');
//
//        if (!$this->getConnection()->hasColumn('tblDebtorCommodity', 'serviceBilling_Commodity')) {
//            $Table->addColumn('serviceBilling_Commodity', 'bigint', array('notnull' => false));
//        }
//        $this->getConnection()->addForeignKey($Table, $tblDebtor);
//        return $Table;
//    }
}
