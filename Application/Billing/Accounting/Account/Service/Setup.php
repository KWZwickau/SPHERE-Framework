<?php
namespace SPHERE\Application\Billing\Accounting\Account\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Accounting\Account\Service
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
        $tblAccountKeyType = $this->setTableAccountKeyType($Schema);
        $tblAccountType = $this->setTableAccountType($Schema);
        $tblAccountKey = $this->setTableAccountKey($Schema, $tblAccountKeyType);
        $this->setTableAccount($Schema, $tblAccountType, $tblAccountKey);

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
     * @return Table $tblAccountKeyType
     *
     * @return Table
     */
    private function setTableAccountKeyType(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblAccountKeyType');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_TEXT);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableAccountType(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblAccountType');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_TEXT);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblAccountKeyType
     *
     * @return Table
     */
    private function setTableAccountKey(Schema &$Schema, Table $tblAccountKeyType)
    {

        $Table = $this->createTable($Schema, 'tblAccountKey');
        $this->createColumn($Table, 'Value', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Code', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'ValidFrom', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'ValidTo', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_TEXT);
        $this->getConnection()->addForeignKey($Table, $tblAccountKeyType);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblAccountType
     * @param Table  $tblAccountKey
     *
     * @return Table
     */
    private function setTableAccount(Schema &$Schema, Table $tblAccountType, Table $tblAccountKey)
    {

        $Table = $this->createTable($Schema, 'tblAccount');
        $this->createColumn($Table, 'Number', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'IsActive', self::FIELD_TYPE_BOOLEAN);
        $this->getConnection()->addForeignKey($Table, $tblAccountType);
        $this->getConnection()->addForeignKey($Table, $tblAccountKey);

        return $Table;
    }
}
