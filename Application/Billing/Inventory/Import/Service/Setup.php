<?php

namespace SPHERE\Application\Billing\Inventory\Import\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Inventory\Import\Service
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
        $this->setTableImport($Schema);

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
    private function setTableImport(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblImport');
        $this->createColumn($Table, 'Row', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'FirstName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'LastName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Birthday', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'Value', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'PriceVariant', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Item', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Reference', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'ReferenceDescription', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'ReferenceDate', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'PaymentFromDate', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'PaymentTillDate', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'DebtorFirstName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'DebtorLastName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'serviceTblPersonDebtor', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'DebtorNumber', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'IBAN', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'BIC', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Bank', self::FIELD_TYPE_STRING);

        return $Table;
    }
}