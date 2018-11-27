<?php
namespace SPHERE\Application\Billing\Accounting\Creditor\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 * @package SPHERE\Application\Billing\Accounting\Creditor\Service
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
        $this->setTableCreditor($Schema);

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
     * @return Table $tblCreditor
     *
     * @return Table
     */
    private function setTableCreditor(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblCreditor');
        $this->createColumn($Table, 'Owner', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Street', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Number', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Code', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'City', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'District', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'CreditorId', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BankName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'IBAN', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BIC', self::FIELD_TYPE_STRING);
//        $this->createColumn($Table, 'serviceTblCompany', self::FIELD_TYPE_BIGINT, true);
//        $this->createColumn($Table, 'serviceTblType', self::FIELD_TYPE_BIGINT, true);

        return $Table;
    }
}
