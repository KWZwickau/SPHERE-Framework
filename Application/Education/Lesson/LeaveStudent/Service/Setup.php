<?php

namespace SPHERE\Application\Education\Lesson\LeaveStudent\Service;

use Doctrine\DBAL\Schema\Schema;
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
        $this->setTableLeaveStudent($Schema);

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
     */
    private function setTableLeaveStudent(Schema &$Schema): void
    {
        $table = $this->getConnection()->createTable($Schema, 'tblLessonLeaveStudent');

        $this->createColumn($table, 'serviceTblSchoolType', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblYear', self::FIELD_TYPE_INTEGER);
        $this->createColumn($table, 'Data', self::FIELD_TYPE_TEXT);
        $this->createColumn($table, 'IsPrintView', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($table, 'DocumentDate', self::FIELD_TYPE_DATETIME, true);
    }
}