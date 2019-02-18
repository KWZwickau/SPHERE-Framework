<?php
namespace SPHERE\Application\Setting\Consumer\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Setting\Consumer\Service
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
        $this->setTableSetting($Schema);
        $this->setTableStudentCustody($Schema);

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
    private function setTableSetting(Schema &$Schema)
    {

        $table = $this->createTable($Schema, 'tblSetting');
        $this->createColumn($table, 'Cluster', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Application', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Module', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'Identifier', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Type', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Value', self::FIELD_TYPE_TEXT);
        $this->createColumn($table, 'Description', self::FIELD_TYPE_TEXT, false, '');
        $this->createColumn($table, 'IsPublic', self::FIELD_TYPE_BOOLEAN, false, false);
        $this->createColumn($table, 'Category', self::FIELD_TYPE_STRING, false, 'Allgemein');

        $this->createIndex($table, array('Cluster', 'Application', 'Module', 'Identifier'));

        return $table;
    }

    /**
     * @param Schema $Schema
     */
    private function setTableStudentCustody(Schema &$Schema)
    {
        $table = $this->createTable($Schema, 'tblStudentCustody');
        $this->createColumn($table, 'serviceTblAccountStudent', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblAccountCustody', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblAccountBlocker', self::FIELD_TYPE_BIGINT);
    }
}
