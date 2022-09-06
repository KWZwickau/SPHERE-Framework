<?php

namespace SPHERE\Application\Platform\System\BasicData\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Platform\System\BasicData\Service
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

        $Schema = clone $this->getConnection()->getSchema();
        $tblHolidayType = $this->setTableHolidayType($Schema);
        $tblState = $this->setTableState($Schema);
        $this->setTableHoliday($Schema, $tblHolidayType, $tblState);

        /**
         * Migration & Archive
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
    private function setTableHolidayType(Schema &$Schema)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblHolidayType');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Identifier', self::FIELD_TYPE_STRING);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableState(Schema &$Schema)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblState');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblHolidayType
     * @param Table $tblState
     *
     * @return Table
     */
    private function setTableHoliday(Schema &$Schema, Table $tblHolidayType, Table $tblState)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblHoliday');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'FromDate', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'ToDate', self::FIELD_TYPE_DATETIME, true);

        $this->getConnection()->addForeignKey($Table, $tblHolidayType, false);
        $this->getConnection()->addForeignKey($Table, $tblState, true);

        return $Table;
    }
}