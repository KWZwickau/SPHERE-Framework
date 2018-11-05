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
        $tblPersonBilling = $this->setTablePersonBilling($Schema);
        $tblDebtor = $this->setTableDebtor($Schema, $tblPersonBilling);
        $this->setTableDebtorNumber($Schema, $tblDebtor);
        $tblBankAccount = $this->setTableBankAccount($Schema, $tblDebtor);
        $tblBankReference = $this->setTableBankReference($Schema, $tblDebtor, $tblBankAccount);
        $this->setTableDebtorSelection($Schema, $tblDebtor, $tblBankReference);

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
    private function setTablePersonBilling(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblPersonBilling');
        $this->createColumn($Table, 'Salutation', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Title', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'FirstName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'LastName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Street', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'StreetNumber', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Code', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'City', self::FIELD_TYPE_STRING);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblPersonBilling
     *
     * @return Table
     */
    private function setTableDebtor(Schema &$Schema, Table $tblPersonBilling)
    {

        $Table = $this->createTable($Schema, 'tblDebtor');
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);
        $this->getConnection()->addForeignKey($Table, $tblPersonBilling, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblDebtor
     *
     * @return Table
     */
    private function setTableDebtorNumber(Schema &$Schema, Table $tblDebtor)
    {

        $Table = $this->createTable($Schema, 'tblDebtorNumber');
        $this->createColumn($Table, '$DebtorNumber', self::FIELD_TYPE_STRING);
        $this->getConnection()->addForeignKey($Table, $tblDebtor, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblDebtor
     *
     * @return Table
     */
    private function setTableBankAccount(Schema &$Schema, Table $tblDebtor)
    {

        $Table = $this->createTable($Schema, 'tblBankAccount');
        $this->createColumn($Table, 'BankName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'IBAN', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BIC', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Owner', self::FIELD_TYPE_STRING);
        $this->getConnection()->addForeignKey($Table, $tblDebtor, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblDebtor
     * @param Table $tblBankAccount
     *
     * @return Table
     */
    private function setTableBankReference(Schema &$Schema, Table $tblDebtor, Table $tblBankAccount)
    {

        $Table = $this->createTable($Schema, 'tblBankReference');
        $this->createColumn($Table, 'ReferenceNumber', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'ReferenceDate', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'IsStandard', self::FIELD_TYPE_BOOLEAN);
        $this->getConnection()->addForeignKey($Table, $tblDebtor, true);
        $this->getConnection()->addForeignKey($Table, $tblBankAccount, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblDebtor
     * @param Table $tblBankReference
     *
     * @return Table
     */
    private function setTableDebtorSelection(Schema &$Schema, Table $tblDebtor, Table $tblBankReference)
    {

        $Table = $this->createTable($Schema, 'tblDebtorSelection');
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblItem', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblPaymentType', self::FIELD_TYPE_BIGINT, true);
        $this->getConnection()->addForeignKey($Table, $tblDebtor, true);
        $this->getConnection()->addForeignKey($Table, $tblBankReference, true);

        return $Table;
    }
}
