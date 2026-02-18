<?php

namespace SPHERE\Application\Education\ClassRegister\ScheduleTime\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

class Setup extends AbstractSetup
{
    /**
     * @param $Simulate
     * @param $IsCollation
     *
     * @return string
     *
     * @noinspection PhpMissingParamTypeInspection
     */
    public function setupDatabaseSchema($Simulate = true, $IsCollation = false): string
    {
        /**
         * Table
         */
        $schema = clone $this->getConnection()->getSchema();
        $tblScheduleTime = $this->setTableScheduleTime($schema);
        $this->setTableScheduleTimeSchoolType($schema, $tblScheduleTime);
        $this->setTableScheduleTimeSlot($schema, $tblScheduleTime);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$IsCollation){
            $this->getConnection()->setMigration($schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $schema
     *
     * @return Table
     */
    private function setTableScheduleTime(Schema $schema): Table
    {
        $table = $this->createTable($schema, 'tblClassRegisterScheduleTime');
        $this->createColumn($table, 'Name');
        $this->createColumn($table, 'SecondaryLevel', self::FIELD_TYPE_SMALLINT);

        return $table;
    }

    /**
     * @param Schema $schema
     * @param Table $tblScheduleTime
     *
     * @return void
     */
    private function setTableScheduleTimeSchoolType(Schema $schema, Table $tblScheduleTime): void
    {
        $table = $this->createTable($schema, 'tblClassRegisterScheduleTimeSchoolType');
        $this->createForeignKey($table, $tblScheduleTime);
        $this->createColumn($table, 'serviceTblSchoolType', self::FIELD_TYPE_BIGINT);
    }

    /**
     * @param Schema $schema
     * @param Table $tblScheduleTime
     *
     * @return void
     */
    private function setTableScheduleTimeSlot(Schema $schema, Table $tblScheduleTime): void
    {
        $table = $this->createTable($schema, 'tblClassRegisterScheduleTimeSlot');
        $this->createForeignKey($table, $tblScheduleTime);
        $this->createColumn($table, 'Lesson', self::FIELD_TYPE_INTEGER);
        $this->createColumn($table, 'StartTime');
        $this->createColumn($table, 'EndTime');
    }
}