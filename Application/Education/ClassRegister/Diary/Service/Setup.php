<?php

namespace SPHERE\Application\Education\ClassRegister\Diary\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

class Setup extends AbstractSetup
{
    /**
     * @param bool $Simulate
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true, $UTF8 = false): string
    {
        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();
        $tblDiary = $this->setTableDiary($Schema);
        $this->setTableDiaryStudent($Schema, $tblDiary);
        $this->setTableDiaryPredecessorDivisionCourse($Schema);

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
    private function setTableDiary(Schema &$Schema): Table
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblClassRegisterDiary');

        $this->createColumn($Table, 'serviceTblDivisionCourse', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'Subject', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'Content', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'Date', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'Location', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblDiary
     */
    private function setTableDiaryStudent(Schema &$Schema, Table $tblDiary)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblClassRegisterDiaryStudent');

        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);

        $this->createForeignKey($Table, $tblDiary);
    }

    /**
     * @param Schema $Schema
     */
    private function setTableDiaryPredecessorDivisionCourse(Schema &$Schema)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblClassRegisterDiaryPredecessorDivisionCourse');

        $this->createColumn($Table, 'serviceTblDivisionCourse', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'serviceTblPredecessorDivisionCourse', self::FIELD_TYPE_BIGINT);
    }
}