<?php
namespace SPHERE\Application\Billing\Accounting\Debtor\Service;

//use Doctrine\DBAL\Schema\Schema;
//use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Accounting\Debtor\Service
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
        $this->setTableDebtorNumber($Schema);
        $tblBankAccount = $this->setTableBankAccount($Schema);
        $tblBankReference = $this->setTableBankReference($Schema);
        $this->setTableDebtorSelection($Schema, $tblBankAccount, $tblBankReference);

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
     * @return Table $tblTable
     *
     * @return Table
     */
    private function setTableDebtorNumber(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblDebtorNumber');
        $this->createColumn($Table, 'DebtorNumber', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableBankAccount(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblBankAccount');
        $this->createColumn($Table, 'BankName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'IBAN', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BIC', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Owner', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableBankReference(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblBankReference');
        $this->createColumn($Table, 'ReferenceNumber', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'ReferenceDate', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblBankAccount
     * @param Table  $tblBankReference
     *
     * @return Table
     */
    private function setTableDebtorSelection(Schema &$Schema, Table $tblBankAccount, Table $tblBankReference)
    {

        $Table = $this->createTable($Schema, 'tblDebtorSelection');
        $this->createColumn($Table, 'serviceTblPersonCauser', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblItem', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblItemVariant', self::FIELD_TYPE_BIGINT, true);
        if(!$this->getConnection()->hasColumn('tblDebtorSelection', 'Value')) {
            $Table->addColumn('Value', 'decimal', array('precision' => 14, 'scale' => 4));
        }
        $this->createColumn($Table, 'serviceTblPaymentType', self::FIELD_TYPE_BIGINT, true);
        $this->getConnection()->addForeignKey($Table, $tblBankAccount, true);
        $this->getConnection()->addForeignKey($Table, $tblBankReference, true);

        return $Table;
    }
}
