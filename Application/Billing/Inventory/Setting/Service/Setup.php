<?php
namespace SPHERE\Application\Billing\Inventory\Setting\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 * @package SPHERE\Application\Billing\Inventory\Setting\Service
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
        $this->setTableSetting($Schema);
        $this->setTableSettingGroupPerson($Schema);

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
    private function setTableSetting(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblSetting');
        $this->createColumn($Table, 'Identifier', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Value', self::FIELD_TYPE_STRING);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableSettingGroupPerson(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblSettingGroupPerson');
        $this->createColumn($Table, 'serviceTblGroupPerson', self::FIELD_TYPE_BIGINT);

        return $Table;
    }
}